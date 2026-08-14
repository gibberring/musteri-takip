<?php

namespace App\Http\Controllers;

use App\Models\Islemloglari;
use App\Models\Servis;
use App\Models\ServisDurumCevap;
use App\Models\ServisDurumCevap0;
use App\Services\TeknisyenYonlendirmeBildirimService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

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
            $payload = $request->only(['personel_id','servis_durum_id','aciklama','tarih','saat']);
            if (empty($payload['personel_id'])) {
                $payload['personel_id'] = $log->islemi_yapan_personel_id; // değişiklik yoksa mevcut kalsın
            }
            // Model kolon adı farklı ise eşle
            if (isset($payload['personel_id'])) {
                $payload['islemi_yapan_personel_id'] = $payload['personel_id'];
                unset($payload['personel_id']);
            }
            $log->update($payload);
            if ($log->servis_id) {
                $this->syncServisPersonelFromLatestCevap((int) $log->servis_id);
            }
            return response()->json(['success' => true, 'message' => 'İşlem logu başarıyla güncellendi.']);
        } catch (\Exception $e) {
            Log::error('İşlem logu güncellenirken hata: ' . $e->getMessage(), ['log_id' => $islemlog]);
            return response()->json(['success' => false, 'message' => 'İşlem logu güncellenirken bir hata oluştu.'], 500);
        }
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