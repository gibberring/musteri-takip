<?php

namespace App\Http\Controllers;

use App\Models\Servis;
use App\Models\Kasa;
use App\Models\Islemloglari;
use App\Models\SettingsAuditLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DeletedRecordsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            $user = Auth::user();
            if (!$user || (int) $user->poz_id !== 1071) {
                abort(403, 'Bu sayfaya erişim yetkiniz yok.');
            }
            return $next($request);
        });
    }

    private function authorizePatron()
    {
        $user = Auth::user();
        if (!$user || (int) $user->poz_id !== 1071) {
            abort(403, 'Bu sayfaya erişim yetkiniz yok.');
        }
    }

    // Tüm silinen kayıtları limitsiz çekmek (özellikle işlem logları) 128MB PHP
    // belleğini aşıp 500 veriyordu; her sekmede aynı üst sınır kullanılıyor.
    private const LIST_LIMIT = 500;

    /**
     * Tarih alanları boş bırakılırsa (ilk açılış veya datepicker temizlenirse) her zaman
     * bugüne düşer; kullanıcı datepicker ile değiştirdiğinde o tarih kullanılır.
     */
    private function dateRangeFromRequest(Request $request): array
    {
        $bugun = Carbon::today()->format('Y-m-d');
        $tarih1 = trim((string) $request->input('tarih1', ''));
        $tarih2 = trim((string) $request->input('tarih2', ''));

        return [
            $tarih1 !== '' ? $tarih1 : $bugun,
            $tarih2 !== '' ? $tarih2 : $bugun,
        ];
    }

    public function servisPage(Request $request)
    {
        $this->authorizePatron();

        [$tarih1, $tarih2] = $this->dateRangeFromRequest($request);
        $silinenServisler = $this->deletedServisQuery(null, self::LIST_LIMIT, $tarih1, $tarih2)->get();

        return view('settings.deleted-records-servis', compact('silinenServisler', 'tarih1', 'tarih2'));
    }

    public function kasaPage(Request $request)
    {
        $this->authorizePatron();

        [$tarih1, $tarih2] = $this->dateRangeFromRequest($request);
        $silinenKasa = $this->deletedKasaQuery(null, self::LIST_LIMIT, $tarih1, $tarih2)->get();

        return view('settings.deleted-records-kasa', compact('silinenKasa', 'tarih1', 'tarih2'));
    }

    public function digerPage(Request $request)
    {
        $this->authorizePatron();

        [$tarih1, $tarih2] = $this->dateRangeFromRequest($request);
        $silinenIslemLoglari = $this->deletedIslemLogQuery(null, self::LIST_LIMIT, $tarih1, $tarih2)->get();
        $musteriIletisimGuncellemeleri = $this->musteriGuncellemeQuery(self::LIST_LIMIT, $tarih1, $tarih2)->get();

        return view('settings.deleted-records-diger', compact(
            'silinenIslemLoglari',
            'musteriIletisimGuncellemeleri',
            'tarih1',
            'tarih2'
        ));
    }

    public function searchIslemLog(Request $request)
    {
        $this->authorizePatron();

        $q = trim((string) $request->input('q', ''));
        [$tarih1, $tarih2] = $this->dateRangeFromRequest($request);
        $logs = $this->deletedIslemLogQuery($q, self::LIST_LIMIT, $tarih1, $tarih2)->get();
        $html = view('settings.partials.deleted-islemlog-rows', [
            'silinenIslemLoglari' => $logs,
            'emptyMessage' => $q === ''
                ? 'Silinen işlem logu bulunamadı.'
                : 'Eşleşen silinen işlem logu bulunamadı.',
        ])->render();

        return response()->json([
            'html' => $html,
            'count' => $logs->count(),
        ]);
    }

    public function searchKasa(Request $request)
    {
        $this->authorizePatron();

        $q = trim((string) $request->input('q', ''));
        [$tarih1, $tarih2] = $this->dateRangeFromRequest($request);
        $rows = $this->deletedKasaQuery($q, self::LIST_LIMIT, $tarih1, $tarih2)->get();
        $html = view('settings.partials.deleted-kasa-rows', [
            'silinenKasa' => $rows,
            'emptyMessage' => $q === ''
                ? 'Silinen kasa kaydı bulunamadı.'
                : 'Eşleşen silinen kasa kaydı bulunamadı.',
        ])->render();

        return response()->json([
            'html' => $html,
            'count' => $rows->count(),
        ]);
    }

    public function searchServis(Request $request)
    {
        $this->authorizePatron();

        $q = trim((string) $request->input('q', ''));
        [$tarih1, $tarih2] = $this->dateRangeFromRequest($request);
        $rows = $this->deletedServisQuery($q, self::LIST_LIMIT, $tarih1, $tarih2)->get();
        $html = view('settings.partials.deleted-servis-rows', [
            'silinenServisler' => $rows,
            'emptyMessage' => $q === ''
                ? 'Silinen servis kaydı bulunamadı.'
                : 'Eşleşen silinen servis kaydı bulunamadı.',
        ])->render();

        return response()->json([
            'html' => $html,
            'count' => $rows->count(),
        ]);
    }

    public function searchMusteriGuncelleme(Request $request)
    {
        $this->authorizePatron();

        [$tarih1, $tarih2] = $this->dateRangeFromRequest($request);
        $rows = $this->musteriGuncellemeQuery(self::LIST_LIMIT, $tarih1, $tarih2)->get();
        $html = view('settings.partials.deleted-musteri-guncelleme-rows', [
            'musteriIletisimGuncellemeleri' => $rows,
        ])->render();

        return response()->json([
            'html' => $html,
            'count' => $rows->count(),
        ]);
    }

    private function musteriGuncellemeQuery(int $limit, ?string $tarih1 = null, ?string $tarih2 = null)
    {
        $query = SettingsAuditLog::query()
            ->where('action', SettingsAuditLog::ACTION_MUSTERI_CONTACT_UPDATED)
            ->with(['personel:id,ad'])
            ->orderByDesc('id');

        if ($tarih1) {
            $query->whereDate('created_at', '>=', $tarih1);
        }
        if ($tarih2) {
            $query->whereDate('created_at', '<=', $tarih2);
        }

        return $query->limit($limit);
    }

    private function deletedIslemLogQuery(?string $search, int $limit = 500, ?string $tarih1 = null, ?string $tarih2 = null)
    {
        $query = Islemloglari::onlyDeleted()
            ->with(['personel', 'servis', 'servisDurum', 'silenKisi'])
            ->orderByDesc('silinme_tarihi')
            ->orderByDesc('id');

        if ($tarih1) {
            $query->whereDate('silinme_tarihi', '>=', $tarih1);
        }
        if ($tarih2) {
            $query->whereDate('silinme_tarihi', '<=', $tarih2);
        }

        $term = trim((string) $search);
        if ($term === '') {
            return $query->limit($limit);
        }

        $term = mb_substr($term, 0, 100);
        $normalized = ltrim($term, '#');
        $like = '%' . addcslashes($term, '%_\\') . '%';
        $likeNorm = '%' . addcslashes($normalized, '%_\\') . '%';

        $query->where(function ($q) use ($like, $likeNorm, $normalized) {
            $q->where('aciklama', 'like', $like)
                ->orWhere('tarih', 'like', $like)
                ->orWhere('saat', 'like', $like)
                ->orWhere('silinme_tarihi', 'like', $like)
                ->orWhere('id', 'like', $likeNorm)
                ->orWhere('servis_id', 'like', $likeNorm)
                ->orWhereHas('personel', function ($pq) use ($like) {
                    $pq->where('ad', 'like', $like)
                        ->orWhere('nick', 'like', $like);
                })
                ->orWhereHas('silenKisi', function ($pq) use ($like) {
                    $pq->where('ad', 'like', $like)
                        ->orWhere('nick', 'like', $like);
                })
                ->orWhereHas('servisDurum', function ($pq) use ($like) {
                    $pq->where('ad', 'like', $like);
                });

            if ($normalized !== '' && ctype_digit($normalized)) {
                $q->orWhere('id', $normalized)
                    ->orWhere('servis_id', $normalized);
            }
        });

        return $query->limit($limit);
    }

    private function deletedServisQuery(?string $search, int $limit = 500, ?string $tarih1 = null, ?string $tarih2 = null)
    {
        $query = Servis::with(['musteri', 'personel', 'servisDurum', 'silenKisi', 'marka', 'cihazTuru'])
            ->where('silindi', 1)
            ->orderByDesc('silinme_tarihi')
            ->orderByDesc('id');

        if ($tarih1) {
            $query->whereDate('silinme_tarihi', '>=', $tarih1);
        }
        if ($tarih2) {
            $query->whereDate('silinme_tarihi', '<=', $tarih2);
        }

        $term = trim((string) $search);
        if ($term === '') {
            return $query->limit($limit);
        }

        $term = mb_substr($term, 0, 100);
        $normalized = ltrim($term, '#');
        $like = '%' . addcslashes($term, '%_\\') . '%';
        $likeNorm = '%' . addcslashes($normalized, '%_\\') . '%';
        $phoneDigits = preg_replace('/\D+/', '', $normalized);

        $query->where(function ($q) use ($like, $likeNorm, $normalized, $phoneDigits) {
            $q->where('id', 'like', $likeNorm)
                ->orWhere('tarih', 'like', $like)
                ->orWhere('saat', 'like', $like)
                ->orWhere('silinme_tarihi', 'like', $like)
                ->orWhere('cihaz_model', 'like', $like)
                ->orWhereHas('musteri', function ($pq) use ($like, $phoneDigits) {
                    $pq->where('ad', 'like', $like)
                        ->orWhere('tel1', 'like', $like)
                        ->orWhere('tel2', 'like', $like);
                    if ($phoneDigits !== '' && mb_strlen($phoneDigits) >= 3) {
                        $phoneLike = '%' . addcslashes($phoneDigits, '%_\\') . '%';
                        $pq->orWhere('tel1', 'like', $phoneLike)
                            ->orWhere('tel2', 'like', $phoneLike);
                    }
                })
                ->orWhereHas('personel', function ($pq) use ($like) {
                    $pq->where('ad', 'like', $like)
                        ->orWhere('nick', 'like', $like);
                })
                ->orWhereHas('silenKisi', function ($pq) use ($like) {
                    $pq->where('ad', 'like', $like)
                        ->orWhere('nick', 'like', $like);
                })
                ->orWhereHas('servisDurum', function ($pq) use ($like) {
                    $pq->where('ad', 'like', $like);
                })
                ->orWhereHas('marka', function ($pq) use ($like) {
                    $pq->where('ad', 'like', $like);
                })
                ->orWhereHas('cihazTuru', function ($pq) use ($like) {
                    $pq->where('ad', 'like', $like);
                });

            if ($normalized !== '' && ctype_digit($normalized)) {
                $q->orWhere('id', $normalized);
            }
        });

        return $query->limit($limit);
    }

    private function deletedKasaQuery(?string $search, int $limit = 500, ?string $tarih1 = null, ?string $tarih2 = null)
    {
        $query = Kasa::onlyDeleted()
            ->with(['personel', 'ilgiliPersonel', 'odemeTuru', 'odemeSekli', 'servis', 'silenKisi'])
            ->orderByDesc('silinme_tarihi')
            ->orderByDesc('id');

        if ($tarih1) {
            $query->whereDate('silinme_tarihi', '>=', $tarih1);
        }
        if ($tarih2) {
            $query->whereDate('silinme_tarihi', '<=', $tarih2);
        }

        $term = trim((string) $search);
        if ($term === '') {
            return $query->limit($limit);
        }

        $term = mb_substr($term, 0, 100);
        $normalized = ltrim($term, '#');
        $like = '%' . addcslashes($term, '%_\\') . '%';
        $likeNorm = '%' . addcslashes($normalized, '%_\\') . '%';

        $amountCandidate = str_replace([' ', "\u{00A0}"], '', $normalized);
        $amountCandidate = str_replace('.', '', $amountCandidate);
        $amountCandidate = str_replace(',', '.', $amountCandidate);
        $hasAmount = $amountCandidate !== '' && is_numeric($amountCandidate);

        $query->where(function ($q) use ($like, $likeNorm, $normalized, $hasAmount, $amountCandidate) {
            $q->where('aciklama', 'like', $like)
                ->orWhere('tarih', 'like', $like)
                ->orWhere('saat', 'like', $like)
                ->orWhere('islem_tarihi', 'like', $like)
                ->orWhere('islem_saati', 'like', $like)
                ->orWhere('silinme_tarihi', 'like', $like)
                ->orWhere('tutar', 'like', $likeNorm)
                ->orWhere('id', 'like', $likeNorm)
                ->orWhere('servis_id', 'like', $likeNorm)
                ->orWhereHas('ilgiliPersonel', function ($pq) use ($like) {
                    $pq->where('ad', 'like', $like)
                        ->orWhere('nick', 'like', $like);
                })
                ->orWhereHas('personel', function ($pq) use ($like) {
                    $pq->where('ad', 'like', $like)
                        ->orWhere('nick', 'like', $like);
                })
                ->orWhereHas('odemeSekli', function ($pq) use ($like) {
                    $pq->where('ad', 'like', $like);
                })
                ->orWhereHas('odemeTuru', function ($pq) use ($like) {
                    $pq->where('ad', 'like', $like);
                })
                ->orWhereHas('silenKisi', function ($pq) use ($like) {
                    $pq->where('ad', 'like', $like)
                        ->orWhere('nick', 'like', $like);
                });

            if ($normalized !== '' && ctype_digit($normalized)) {
                $q->orWhere('id', $normalized)
                    ->orWhere('servis_id', $normalized);
            }

            if ($hasAmount) {
                $q->orWhere('tutar', $amountCandidate)
                    ->orWhere('tutar', 'like', '%' . addcslashes($amountCandidate, '%_\\') . '%');
            }
        });

        return $query->limit($limit);
    }

    public function restoreServis($servis)
    {
        $this->authorizePatron();
        Log::info('Silinen servis geri alma isteği', ['servis_id' => $servis, 'user_id' => Auth::id()]);
        $updated = Servis::where('id', $servis)->update([
            'silindi' => 0,
            'silinme_tarihi' => null,
            'silen_kisi_id' => null,
        ]);
        if ($updated < 1) {
            if (request()->ajax() || request()->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Kayıt bulunamadı.'], 404);
            }
            return redirect()->back()->with('error', 'Kayıt bulunamadı.');
        }

        if (request()->ajax() || request()->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Servis kaydı geri alındı.']);
        }
        return redirect()->back()->with('success', 'Servis kaydı geri alındı.');
    }

    public function restoreKasa($kasa)
    {
        $this->authorizePatron();
        Log::info('Silinen kasa geri alma isteği', ['kasa_id' => $kasa, 'user_id' => Auth::id()]);
        $updated = Kasa::where('id', $kasa)
            ->where('silindi', 1)
            ->update([
            'silindi' => 0,
            'silinme_tarihi' => null,
            'silen_kisi_id' => null,
        ]);
        if ($updated < 1) {
            if (request()->ajax() || request()->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Kayıt bulunamadı veya zaten aktif.'], 404);
            }
            return redirect()->back()->with('error', 'Kayıt bulunamadı veya zaten aktif.');
        }
        if (request()->ajax() || request()->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Kasa kaydı geri alındı.']);
        }
        return redirect()->back()->with('success', 'Kasa kaydı geri alındı.');
    }

    public function restoreIslemLog($log)
    {
        $this->authorizePatron();
        Log::info('Silinen işlem logu geri alma isteği', ['log_id' => $log, 'user_id' => Auth::id()]);
        $islemLog = Islemloglari::onlyDeleted()->findOrFail($log);
        $islemLog->update([
            'silindi' => 0,
            'silinme_tarihi' => null,
            'silen_kisi_id' => null,
        ]);
        if (request()->ajax() || request()->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'İşlem logu geri alındı.']);
        }
        return redirect()->back()->with('success', 'İşlem logu geri alındı.');
    }
}
