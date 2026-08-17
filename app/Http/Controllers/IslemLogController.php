<?php

namespace App\Http\Controllers;

use App\Models\Islemloglari;
use App\Models\Servis;
use App\Models\ServisDurumCevap;
use App\Models\ServisDurumCevap0;
use App\Models\ServisDurumSoru;
use App\Services\TeknisyenYonlendirmeBildirimService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class IslemLogController extends Controller
{
    private function authorizeLogAction()
    {
        $user = Auth::user();
        $allowedPozisyonlar = [1071, 1080, 1073]; // Patron, Muhasebe, Operatör
        if (!$user || !in_array((int) $user->poz_id, $allowedPozisyonlar, true)) {
            abort(403, 'Bu işlem için yetkiniz bulunmamaktadır.');
        }
    }

    private function syncServisPersonelFromLatestCevap(int $servisId): void
    {
        $servis = Servis::find($servisId);
        if (!$servis) {
            return;
        }
        $servisDurumId = (int) ($servis->servis_durum_id ?? 0);
        if ($servisDurumId !== 9098) {
            // Servis "Teknisyen Yönlendirildi" değilken personel_id'yi 9098 cevaplarından
            // güncellemek yanlış atamaya ve yeniden yönlendirmede kaydın arafta kalmasına yol açıyordu.
            return;
        }

        $oldPersonelId = (int) ($servis->personel_id ?? 0);

        $latestCevap = ServisDurumCevap::where('soru_id', 13234)
            ->whereHas('durumCevap0', function ($q) use ($servisId) {
                $q->where('servis_id', $servisId)
                  ->where('servis_durum_id', 9098);
            })
            ->orderByDesc('id')
            ->first();

        $newPersonelId = ($latestCevap && $latestCevap->cevap) ? (int) $latestCevap->cevap : 0;
        $servis->personel_id = $newPersonelId > 0 ? $newPersonelId : null;
        $servis->save();

        if ($newPersonelId > 0 && $newPersonelId !== $oldPersonelId) {
            try {
                $wa = app(TeknisyenYonlendirmeBildirimService::class)
                    ->handleYonlendirme($servis->fresh() ?: $servis, $newPersonelId);
                if (empty($wa['sent'])) {
                    Log::warning('İşlem logu teknisyen değişimi WhatsApp gönderilemedi', [
                        'servis_id' => $servisId,
                        'teknisyen_id' => $newPersonelId,
                        'error' => $wa['error'] ?? null,
                    ]);
                }
            } catch (\Throwable $e) {
                Log::error('İşlem logu teknisyen değişimi WhatsApp hatası: ' . $e->getMessage(), [
                    'servis_id' => $servisId,
                    'teknisyen_id' => $newPersonelId,
                ]);
            }
        }
    }

    private function clearTeknisyenYonlendirmeAnswers(int $servisId): void
    {
        $cevap0Ids = ServisDurumCevap0::where('servis_id', $servisId)
            ->where('servis_durum_id', 9098)
            ->pluck('id')
            ->all();

        if (!empty($cevap0Ids)) {
            ServisDurumCevap::whereIn('durumCevap0_id', $cevap0Ids)->delete();
            ServisDurumCevap0::whereIn('id', $cevap0Ids)->delete();
        }
    }

    public function show($islemlog)
    {
        $this->authorizeLogAction();
        try {
            // ID ile manuel bul (implicit binding tutarsızlığını önle)
            $islemLog = Islemloglari::with(['personel', 'servisDurum'])
                ->notDeleted()
                ->findOrFail($islemlog);
            // Frontend net alanlar bekliyor: id, tarih, saat, aciklama, servis_durum_id
            return response()->json([
                'id' => $islemLog->id,
                'tarih' => $islemLog->tarih,
                'saat' => $islemLog->saat ? substr($islemLog->saat, 0, 5) : null,
                'aciklama' => $islemLog->aciklama,
                'servis_durum_id' => $islemLog->servis_durum_id,
                'personel' => $islemLog->personel, // opsiyonel
                'servis_durum' => $islemLog->servisDurum, // opsiyonel
            ]);
        } catch (\Exception $e) {
            Log::error('İşlem logu detayları çekilirken hata: ' . $e->getMessage(), ['log_id' => $islemlog]);
            return response()->json(['success' => false, 'message' => 'İşlem logu detayları alınamadı.'], 500);
        }
    }

    public function update(Request $request, $islemlog)
    {
        $this->authorizeLogAction();
        $request->validate([
            'personel_id' => 'nullable|exists:personel,id',
            'servis_durum_id' => 'required|exists:servis_durum,id',
            'aciklama' => 'nullable|string',
            'tarih' => 'required|date',
            'saat' => 'required|date_format:H:i',
        ]);

        try {
            $log = Islemloglari::notDeleted()->findOrFail($islemlog);
            $oldDurumId = (int) ($log->servis_durum_id ?? 0);
            $payload = $request->only(['personel_id','servis_durum_id','aciklama','tarih','saat']);
            if (empty($payload['personel_id'])) {
                $payload['personel_id'] = $log->islemi_yapan_personel_id; // değişiklik yoksa mevcut kalsın
            }
            // Model kolon adı farklı ise eşle
            if (isset($payload['personel_id'])) {
                $payload['islemi_yapan_personel_id'] = $payload['personel_id'];
                unset($payload['personel_id']);
            }
            DB::transaction(function () use ($log, $payload, $oldDurumId) {
                $log->update($payload);
                if ($log->servis_id) {
                    $this->syncServisDurumFromUpdatedLog($log, $oldDurumId);
                    $this->syncServisPersonelFromLatestCevap((int) $log->servis_id);
                }
            });
            return response()->json(['success' => true, 'message' => 'İşlem logu başarıyla güncellendi.']);
        } catch (\Exception $e) {
            Log::error('İşlem logu güncellenirken hata: ' . $e->getMessage(), ['log_id' => $islemlog]);
            return response()->json(['success' => false, 'message' => 'İşlem logu güncellenirken bir hata oluştu.'], 500);
        }
    }

    /**
     * Düzenlenen log o servisin en son (silinmemiş) durum loguysa servis durumunu
     * ve ilgili cevap/soru kayıtlarını yeni duruma çeker. Ara logda dokunulmaz.
     */
    private function syncServisDurumFromUpdatedLog(Islemloglari $log, int $oldDurumId): void
    {
        $servisId = (int) $log->servis_id;
        $newDurumId = (int) ($log->servis_durum_id ?? 0);
        if ($servisId <= 0 || $newDurumId <= 0) {
            return;
        }

        $latestId = Islemloglari::where('servis_id', $servisId)
            ->notDeleted()
            ->orderBy('id', 'desc')
            ->value('id');

        if ((int) $latestId !== (int) $log->id) {
            return;
        }

        Servis::where('id', $servisId)->update(['servis_durum_id' => $newDurumId]);

        if ($oldDurumId > 0 && $oldDurumId !== $newDurumId) {
            $this->alignCevapRecordsToDurum($servisId, $oldDurumId, $newDurumId);
        }
    }

    /**
     * Son durum loguna karşılık gelen cevap0 + cevap.soru_id kayıtlarını yeni duruma hizalar.
     * Eşleşme: cevap_format (gerekirse sira); tek soruluk durumlarda format farklı olsa da bağlanır.
     */
    private function alignCevapRecordsToDurum(int $servisId, int $oldDurumId, int $newDurumId): void
    {
        $cevap0 = ServisDurumCevap0::where('servis_id', $servisId)
            ->where('servis_durum_id', $oldDurumId)
            ->orderByDesc('id')
            ->first();

        if (!$cevap0) {
            return;
        }

        $cevap0->servis_durum_id = $newDurumId;
        $cevap0->save();

        $newSorular = ServisDurumSoru::where('servis_durum_id', $newDurumId)
            ->orderBy('sira')
            ->orderBy('id')
            ->get();

        if ($newSorular->isEmpty()) {
            return;
        }

        $usedNewSoruIds = [];
        $cevaplar = ServisDurumCevap::where('durumCevap0_id', $cevap0->id)->get();
        $oldSorularById = ServisDurumSoru::whereIn('id', $cevaplar->pluck('soru_id')->filter()->unique()->all())
            ->get()
            ->keyBy('id');

        foreach ($cevaplar as $cevap) {
            $oldSoru = $oldSorularById->get((int) $cevap->soru_id);
            $newSoru = $this->findMatchingSoru($oldSoru, $newSorular, $usedNewSoruIds);
            if (!$newSoru) {
                continue;
            }
            $cevap->soru_id = $newSoru->id;
            $cevap->save();
            $usedNewSoruIds[] = (int) $newSoru->id;
        }
    }

    private function findMatchingSoru($oldSoru, $newSorular, array $usedNewSoruIds)
    {
        $candidates = $newSorular->filter(function ($soru) use ($usedNewSoruIds) {
            return !in_array((int) $soru->id, $usedNewSoruIds, true);
        })->values();

        if ($candidates->isEmpty()) {
            return null;
        }

        if ($oldSoru) {
            $byFormat = $candidates->filter(function ($soru) use ($oldSoru) {
                return (string) $soru->cevap_format === (string) $oldSoru->cevap_format;
            })->values();

            if ($byFormat->count() === 1) {
                return $byFormat->first();
            }
            if ($byFormat->isNotEmpty()) {
                $bySira = $byFormat->firstWhere('sira', $oldSoru->sira);
                return $bySira ?: $byFormat->first();
            }

            // Her iki durumda da tek soru varsa format farklı olsa da bağla (placeholder "?" soruları).
            // 9098 gibi çok sorulu durumlarda teknisyen cevabını (13234) başka formata taşıma.
            if ($candidates->count() === 1) {
                $oldCount = ServisDurumSoru::where('servis_durum_id', $oldSoru->servis_durum_id)->count();
                if ($oldCount === 1) {
                    return $candidates->first();
                }
            }
        }

        return null;
    }

    public function destroy($islemlog)
    {
        $this->authorizeLogAction();
        try {
            $log = Islemloglari::notDeleted()->findOrFail($islemlog);
            $servisId = $log->servis_id;
            $log->update([
                'silindi' => 1,
                'silen_kisi_id' => Auth::id(),
                'silinme_tarihi' => now()->format('Y-m-d H:i:s'),
            ]);

            // Servisin mevcut durumunu, kalan en son logun durumuna geri al
            if ($servisId) {
                $sonLog = Islemloglari::where('servis_id', $servisId)
                    ->notDeleted()
                    ->orderBy('id', 'desc')
                    ->first();
                if ($sonLog && $sonLog->servis_durum_id) {
                    Servis::where('id', $servisId)->update(['servis_durum_id' => $sonLog->servis_durum_id]);
                }
                if ((int) $log->servis_durum_id === 9098) {
                    $this->clearTeknisyenYonlendirmeAnswers((int) $servisId);
                }
                $this->syncServisPersonelFromLatestCevap((int) $servisId);
            }

            return response()->json(['success' => true, 'message' => 'İşlem logu başarıyla silindi.']);
        } catch (\Exception $e) {
            Log::error('İşlem logu silinirken hata: ' . $e->getMessage(), ['log_id' => $islemlog]);
            return response()->json(['success' => false, 'message' => 'İşlem logu silinirken bir hata oluştu.'], 500);
        }
    }
}