<?php

namespace App\Http\Controllers;

use App\Models\Islemloglari;
use App\Models\Personel;
use App\Models\Servis;
use App\Models\ServisDurumCevap;
use App\Models\ServisDurumCevap0;
use App\Models\ServisDurumSoru;
use App\Services\TeknisyenYonlendirmeBildirimService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class IslemLogController extends Controller
{
    /**
     * Servis kaydının gidiş tarihini sınırlar: Kimse (Patron dahil) servis kaydının
     * oluşturulma tarihinden geriye tarih seçemez. Patron dışındaki roller ayrıca
     * oluşturulma tarihinden en fazla 10 gün ileri tarih seçebilir.
     * Uygunsa null, değilse hata mesajı döner.
     */
    private function gidisTarihiSinirHatasi(string $value, Servis $servis, ?Personel $user): ?string
    {
        try {
            $yeniTarih = Carbon::createFromFormat('Y-m-d', $value)->startOfDay();
        } catch (\Exception $e) {
            return null; // Format hatası zaten date_format kuralı tarafından yakalanır.
        }
        $kayitTarihi = $servis->created_at ? $servis->created_at->copy()->startOfDay() : Carbon::today();
        if ($yeniTarih->lt($kayitTarihi)) {
            return 'Gidiş tarihi, servis kaydının oluşturulma tarihinden geriye alınamaz.';
        }

        $patronPozisyonId = 1071;
        if ($user && (int) $user->poz_id === $patronPozisyonId) {
            return null;
        }

        if ($yeniTarih->gt($kayitTarihi->copy()->addDays(10))) {
            return 'Gidiş tarihi, servis kaydının oluşturulma tarihinden en fazla 10 gün ileri alınabilir.';
        }
        return null;
    }

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

    /**
     * Silinen 9098 loguna ait cevap0 + 13234/13235'i siler.
     * Diğer 9098 yönlendirmelerinin cevaplarına dokunmaz (tüm 13234 wipe yok).
     */
    private function clearTeknisyenYonlendirmeAnswers(int $servisId, Islemloglari $deletedLog): void
    {
        $cevap0 = $this->findCevap0ForDeletedYonlendirmeLog($servisId, $deletedLog);
        if (!$cevap0) {
            return;
        }

        ServisDurumCevap::where('durumCevap0_id', $cevap0->id)->delete();
        $cevap0->delete();
    }

    /**
     * Log ile cevap0 arasında FK yok. Aktif 9098 logları (silinen dahil)
     * cevap0 ile sondan hizalanır: yeni atamanın cevabı eski log silinince uçmaz.
     * Eşleşme yoksa hiçbir 13234 silinmez.
     */
    private function findCevap0ForDeletedYonlendirmeLog(int $servisId, Islemloglari $deletedLog): ?ServisDurumCevap0
    {
        $cevap0s = ServisDurumCevap0::where('servis_id', $servisId)
            ->where('servis_durum_id', 9098)
            ->orderBy('id')
            ->get()
            ->values();

        if ($cevap0s->isEmpty()) {
            return null;
        }

        $logIds = Islemloglari::where('servis_id', $servisId)
            ->where('servis_durum_id', 9098)
            ->where(function ($q) use ($deletedLog) {
                $q->where(function ($active) {
                    $active->where('silindi', '!=', 1)
                        ->orWhereNull('silindi');
                })->orWhere('id', $deletedLog->id);
            })
            ->orderBy('id')
            ->pluck('id')
            ->values();

        $logIndex = $logIds->search($deletedLog->id);
        if ($logIndex === false) {
            return null;
        }

        $offsetFromEnd = $logIds->count() - 1 - (int) $logIndex;
        $cevapIndex = $cevap0s->count() - 1 - $offsetFromEnd;
        if ($cevapIndex < 0 || $cevapIndex >= $cevap0s->count()) {
            return null;
        }

        return $cevap0s[$cevapIndex];
    }

    /**
     * Aktif 9098 logunu cevap0 ile sondan hizalar (silme wipe'ına dokunmaz).
     */
    private function findCevap0ForYonlendirmeLog(int $servisId, Islemloglari $log): ?ServisDurumCevap0
    {
        $cevap0s = ServisDurumCevap0::where('servis_id', $servisId)
            ->where('servis_durum_id', 9098)
            ->orderBy('id')
            ->get()
            ->values();

        if ($cevap0s->isEmpty()) {
            return null;
        }

        $logIds = Islemloglari::where('servis_id', $servisId)
            ->where('servis_durum_id', 9098)
            ->notDeleted()
            ->orderBy('id')
            ->pluck('id')
            ->values();

        $logIndex = $logIds->search($log->id);
        if ($logIndex === false) {
            return null;
        }

        $offsetFromEnd = $logIds->count() - 1 - (int) $logIndex;
        $cevapIndex = $cevap0s->count() - 1 - $offsetFromEnd;
        if ($cevapIndex < 0 || $cevapIndex >= $cevap0s->count()) {
            return null;
        }

        return $cevap0s[$cevapIndex];
    }

    /**
     * @return array{0: int|null, 1: string|null}
     */
    private function resolveYonlendirmeFields(Islemloglari $log): array
    {
        $teknisyenId = null;
        $gidisTarihi = null;
        $servis = $log->servis_id ? Servis::find($log->servis_id) : null;

        if ((int) $log->servis_durum_id === 9098 && $log->servis_id) {
            $cevap0 = $this->findCevap0ForYonlendirmeLog((int) $log->servis_id, $log);
            if ($cevap0) {
                $teknisyenCevap = ServisDurumCevap::where('durumCevap0_id', $cevap0->id)
                    ->where('soru_id', 13234)
                    ->first();
                $gidisCevap = ServisDurumCevap::where('durumCevap0_id', $cevap0->id)
                    ->where('soru_id', 13235)
                    ->first();
                if ($teknisyenCevap && $teknisyenCevap->cevap) {
                    $teknisyenId = (int) $teknisyenCevap->cevap;
                }
                if ($gidisCevap && $gidisCevap->cevap) {
                    $gidisTarihi = (string) $gidisCevap->cevap;
                }
            }
        }

        if (!$teknisyenId && $servis && $servis->personel_id) {
            $teknisyenId = (int) $servis->personel_id;
        }
        if (!$gidisTarihi && $servis && !empty($servis->tarih)) {
            $gidisTarihi = (string) $servis->tarih;
        }
        if (!$gidisTarihi && $log->aciklama && preg_match('/Gidi[sş] Tarihi:\s*(\d{4}-\d{2}-\d{2})/u', (string) $log->aciklama, $m)) {
            $gidisTarihi = $m[1];
        }

        return [$teknisyenId ?: null, $gidisTarihi ?: null];
    }

    private function aktifTeknisyenListesi(?int $includeId = null)
    {
        return Personel::query()
            ->where('poz_id', 1077)
            ->where(function ($q) use ($includeId) {
                $q->where('aktif', 1);
                if ($includeId) {
                    $q->orWhere('id', $includeId);
                }
            })
            ->orderBy('ad')
            ->get(['id', 'ad']);
    }

    private function buildYonlendirmeLogAciklama(int $teknisyenId, string $gidisTarihi): string
    {
        $teknisyenSoru = ServisDurumSoru::find(13234);
        $gidisSoru = ServisDurumSoru::find(13235);
        $personel = Personel::find($teknisyenId);

        return '- ' . ($teknisyenSoru->soru ?? 'Teknisyen') . ': ' . ($personel ? $personel->ad : 'Bilinmiyor')
            . '<br>- ' . ($gidisSoru->soru ?? 'Gidiş Tarihi') . ': ' . $gidisTarihi;
    }

    private function upsertYonlendirmeCevaplari(int $servisId, Islemloglari $log, int $teknisyenId, string $gidisTarihi): void
    {
        $cevap0 = $this->findCevap0ForYonlendirmeLog($servisId, $log);
        if (!$cevap0) {
            $servis = Servis::find($servisId);
            $cevap0 = new ServisDurumCevap0();
            $cevap0->servis_id = $servisId;
            $cevap0->servis_durum_id = 9098;
            $cevap0->tarih = $log->tarih;
            $cevap0->saat = $log->saat;
            if ($servis && isset($servis->uye_firma_id)) {
                $cevap0->uye_firma_id = $servis->uye_firma_id;
            }
            $cevap0->save();
        }

        $this->upsertCevap($cevap0, 13234, (string) $teknisyenId);
        $this->upsertCevap($cevap0, 13235, $gidisTarihi);
    }

    private function upsertCevap(ServisDurumCevap0 $cevap0, int $soruId, string $value): void
    {
        $cevap = ServisDurumCevap::where('durumCevap0_id', $cevap0->id)
            ->where('soru_id', $soruId)
            ->first();
        if (!$cevap) {
            $cevap = new ServisDurumCevap();
            $cevap->durumCevap0_id = $cevap0->id;
            $cevap->soru_id = $soruId;
            if (isset($cevap0->uye_firma_id)) {
                $cevap->uye_firma_id = $cevap0->uye_firma_id;
            }
        }
        $cevap->cevap = $value;
        $cevap->save();
    }

    private function isLatestServisLog(Islemloglari $log): bool
    {
        $latestId = Islemloglari::where('servis_id', $log->servis_id)
            ->notDeleted()
            ->orderBy('id', 'desc')
            ->value('id');

        return (int) $latestId === (int) $log->id;
    }

    private function plainTextFromBr(string $text): string
    {
        $text = preg_replace('/<br\s*\/?>/i', "\n", $text) ?? $text;
        return html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    private function brFromPlainText(string $text): string
    {
        return preg_replace("/\r\n|\r|\n/", '<br>', $text) ?? $text;
    }

    public function show($islemlog)
    {
        $this->authorizeLogAction();
        try {
            // ID ile manuel bul (implicit binding tutarsızlığını önle)
            $islemLog = Islemloglari::with(['personel', 'servisDurum'])
                ->notDeleted()
                ->findOrFail($islemlog);
            [$teknisyenId, $gidisTarihi] = $this->resolveYonlendirmeFields($islemLog);
            // Frontend net alanlar bekliyor: id, tarih, saat, aciklama, servis_durum_id
            return response()->json([
                'id' => $islemLog->id,
                'tarih' => $islemLog->tarih,
                'saat' => $islemLog->saat ? substr($islemLog->saat, 0, 5) : null,
                'aciklama' => $islemLog->aciklama,
                'servis_durum_id' => $islemLog->servis_durum_id,
                'personel' => $islemLog->personel, // opsiyonel
                'servis_durum' => $islemLog->servisDurum, // opsiyonel
                'teknisyen_id' => $teknisyenId,
                'gidis_tarihi' => $gidisTarihi,
                'teknisyenler' => $this->aktifTeknisyenListesi($teknisyenId),
            ]);
        } catch (\Exception $e) {
            Log::error('İşlem logu detayları çekilirken hata: ' . $e->getMessage(), ['log_id' => $islemlog]);
            return response()->json(['success' => false, 'message' => 'İşlem logu detayları alınamadı.'], 500);
        }
    }

    public function update(Request $request, $islemlog)
    {
        $this->authorizeLogAction();
        $newDurumId = (int) $request->input('servis_durum_id');
        $yonlendirmeKurallari = $newDurumId === 9098
            ? [
                'teknisyen_id' => 'required|exists:personel,id',
                'gidis_tarihi' => 'required|date_format:Y-m-d',
            ]
            : [];
        $request->validate(array_merge([
            'personel_id' => 'nullable|exists:personel,id',
            'servis_durum_id' => 'required|exists:servis_durum,id',
            'aciklama' => 'nullable|string',
            'tarih' => 'required|date',
            'saat' => 'required|date_format:H:i',
        ], $yonlendirmeKurallari));

        try {
            $log = Islemloglari::notDeleted()->findOrFail($islemlog);

            if ($newDurumId === 9098 && $log->servis_id) {
                $gidisTarihiKontrol = trim((string) $request->input('gidis_tarihi'));
                $servisKontrol = Servis::find($log->servis_id);
                if ($gidisTarihiKontrol !== '' && $servisKontrol) {
                    if ($hata = $this->gidisTarihiSinirHatasi($gidisTarihiKontrol, $servisKontrol, Auth::user())) {
                        return response()->json(['success' => false, 'message' => $hata], 422);
                    }
                }
            }

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
            $teknisyenId = (int) $request->input('teknisyen_id');
            $gidisTarihi = trim((string) $request->input('gidis_tarihi'));
            if ($newDurumId === 9098) {
                $payload['aciklama'] = $this->buildYonlendirmeLogAciklama($teknisyenId, $gidisTarihi);
            } else {
                $payload['aciklama'] = $this->plainTextFromBr((string) ($payload['aciklama'] ?? ''));
                $payload['aciklama'] = $this->brFromPlainText($payload['aciklama']);
            }
            DB::transaction(function () use ($log, $payload, $oldDurumId, $newDurumId, $teknisyenId, $gidisTarihi) {
                $log->update($payload);
                if ($log->servis_id) {
                    $this->syncServisDurumFromUpdatedLog($log, $oldDurumId);
                    if ($newDurumId === 9098 && $teknisyenId > 0 && $gidisTarihi !== '') {
                        $this->upsertYonlendirmeCevaplari((int) $log->servis_id, $log, $teknisyenId, $gidisTarihi);
                        if ($this->isLatestServisLog($log)) {
                            $servis = Servis::find($log->servis_id);
                            if ($servis) {
                                $servis->tarih = $gidisTarihi;
                                $servis->save();
                            }
                        }
                    }
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
                    $this->clearTeknisyenYonlendirmeAnswers((int) $servisId, $log);
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