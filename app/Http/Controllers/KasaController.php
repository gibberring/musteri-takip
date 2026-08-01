<?php

namespace App\Http\Controllers;

use App\Models\Kasa;
use App\Models\KasaOdemeTuru;
use App\Models\KasaOdemeSekli;
use App\Models\Personel;
use App\Models\Servis;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

class KasaController extends Controller
{
    private function sanitizeLogData(array $data): array
    {
        $sensitiveKeys = ['aciklama', 'tutar', 'tarih', 'saat', 'islem_tarihi', 'islem_saati'];
        foreach ($sensitiveKeys as $key) {
            if (array_key_exists($key, $data) && $data[$key] !== null) {
                $data[$key] = '[REDACTED]';
            }
        }
        return $data;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        Log::info('KasaController@index çağrıldı.', ['query_params' => $this->sanitizeLogData($request->query())]);
        
        // Erişim kontrolünü try dışına al: 403 istisnası yakalanmasın
        $loggedInUser = Auth::user();
        $teknisyenPozisyonId = 1077; // 'T.Ş.R.N Teknisyen' pozisyon ID'si
        $operatorPozisyonId = 1073; // Operatör pozisyon ID'si
        $idariIslerPozisyonId = 1076; // İdari İşler pozisyon ID'si

        // Operatör, İdari İşler ve Teknisyen için kasa sayfasına erişimi engelle
        if ($loggedInUser && in_array($loggedInUser->poz_id, [$operatorPozisyonId, $idariIslerPozisyonId, $teknisyenPozisyonId])) {
            Log::warning('Yetkisiz kasa sayfası erişim denemesi.', ['user_id' => $loggedInUser->id, 'poz_id' => $loggedInUser->poz_id]);
            abort(403, 'Kasa kayıtlarını görüntüleme yetkiniz bulunmamaktadır.');
        }

        try {

            $query = Kasa::with(['odemeTuru', 'odemeSekli', 'personel', 'ilgiliPersonel', 'servis']);

            // Soft delete filtresi
            $query->where(function($q) {
                $q->where('silindi', '!=', 1)
                                              ->orWhereNull('silindi');
            });

            // Eğer giriş yapan kullanıcı bir teknisyen ise, kayıtları filtrele
            if ($loggedInUser && $loggedInUser->poz_id === $teknisyenPozisyonId) {
                $query->where(function($q) use ($loggedInUser) {
                    // Teknisyene doğrudan atanmış kasa kayıtları
                    $q->where('personel_id', $loggedInUser->id)
                      ->orWhere('ilgili_personel_id', $loggedInUser->id);

                    // Teknisyene atanmış servislere bağlı kasa kayıtları
                    $q->orWhereHas('servis', function($subQ) use ($loggedInUser) {
                        $subQ->where('personel_id', $loggedInUser->id);
                    });
                });
            }

            // Arama filtreleri
            $odemeTuru = $request->input('odeme_turu');
            $odemeYonu = $request->input('odeme_yonu');
            $odemeDurumu = $request->input('odeme_durumu');
            $odemeSekli = $request->input('odeme_sekli');
            $ilgiliPersonel = $request->input('ilgili_personel');
            $tedarikci = $request->input('tedarikci'); // Tedarikçi ID'si
            $tarih1 = $request->input('tarih1');
            $tarih2 = $request->input('tarih2');
            $odenentutar = $request->input('odenentutar'); // aratxt

            Log::debug('KasaController@index - Tarih parametreleri (RAW):', ['tarih1_raw' => $tarih1, 'tarih2_raw' => $tarih2]);

            // Eğer hiçbir arama kriteri belirtilmediyse veya tarih aralığı belirtilmediyse, bugünün kayıtlarını getir
            // Bu filtreleme kaldırıldı, çünkü tüm kayıtların gelmesi bekleniyor.
            // if (!$tarih1 && !$tarih2 && !$odemeTuru && !$odemeYonu && !$odemeDurumu && !$odemeSekli && !$ilgiliPersonel && !$tedarikci && !$odenentutar) {
            //     $query->whereDate('islem_tarihi', Carbon::today());
            // }

            // Bu noktada $query, teknisyen filtresi ve diğer genel filtrelerle (odeme_turu, odeme_durumu, odeme_sekli, ilgili_personel, odenentutar) filtrelenmiş durumda.
            // $totalsQueryForSums, bu filtrelenmiş halden klonlanacak ve böylece tüm bu filtreleri içerecek.
            // Ancak, odeme_yonu filtresi ve varsayılan 'bugünün kayıtları' filtresi $totalsQueryForSums'a uygulanmayacak.
            $totalsQueryForSums = clone $query;


            // Tarih filtrelerini asıl sorguya uygula
            $dateFrom = null;
            $dateTo = null;
            if ($tarih1) {
                $formattedTarih1 = null;
                try {
                    $formattedTarih1 = Carbon::createFromFormat('d.m.Y', $tarih1)->format('Y-m-d');
                } catch (\Exception $e) {
                    try {
                        $formattedTarih1 = Carbon::createFromFormat('Y-m-d', $tarih1)->format('Y-m-d');
                    } catch (\Exception $e2) {
                        Log::error('KasaController@index - Tarih1 ayrıştırma hatası:', ['tarih1' => $tarih1, 'error' => $e2->getMessage()]);
                    }
                }
                if ($formattedTarih1) {
                    $dateFrom = $formattedTarih1;
                    $query->whereDate('islem_tarihi', '>=', $formattedTarih1);
                    $totalsQueryForSums->whereDate('islem_tarihi', '>=', $formattedTarih1); // totalsQueryForSums için de uygula
                    Log::debug('KasaController@index - Tarih1 işlendi:', ['tarih1' => $tarih1, 'formatted' => $formattedTarih1]);
                }
            }
            if ($tarih2) {
                $formattedTarih2 = null;
                try {
                    $formattedTarih2 = Carbon::createFromFormat('d.m.Y', $tarih2)->format('Y-m-d');
                } catch (\Exception $e) {
                    try {
                        $formattedTarih2 = Carbon::createFromFormat('Y-m-d', $tarih2)->format('Y-m-d');
                    } catch (\Exception $e2) {
                        Log::error('KasaController@index - Tarih2 ayrıştırma hatası:', ['tarih2' => $tarih2, 'error' => $e2->getMessage()]);
                    }
                }
                if ($formattedTarih2) {
                    $dateTo = $formattedTarih2;
                    $query->whereDate('islem_tarihi', '<=', $formattedTarih2);
                    $totalsQueryForSums->whereDate('islem_tarihi', '<=', $formattedTarih2); // totalsQueryForSums için de uygula
                    Log::debug('KasaController@index - Tarih2 işlendi:', ['tarih2' => $tarih2, 'formatted' => $formattedTarih2]);
                }
            }

            if ($odenentutar !== null && $odenentutar !== '') {
                $odenentutarTrimmed = trim((string) $odenentutar);
                if ($odenentutarTrimmed !== '' && is_numeric($odenentutarTrimmed)) {
                    $query->where('tutar', $odenentutarTrimmed);
                    $totalsQueryForSums->where('tutar', $odenentutarTrimmed);
                } else {
                    $query->whereHas('ilgiliPersonel', function ($q) use ($odenentutarTrimmed) {
                        $q->where('ad', 'LIKE', '%' . $odenentutarTrimmed . '%');
                    });
                    $totalsQueryForSums->whereHas('ilgiliPersonel', function ($q) use ($odenentutarTrimmed) {
                        $q->where('ad', 'LIKE', '%' . $odenentutarTrimmed . '%');
                    });
                }
            }
            if ($odemeTuru && $odemeTuru !== '0') {
                $query->where('odeme_turu_id', $odemeTuru);
                $totalsQueryForSums->where('odeme_turu_id', $odemeTuru);
            }
            if ($odemeDurumu !== null && $odemeDurumu !== '' && $odemeDurumu !== '-1') {
                $query->where('gerceklesme', $odemeDurumu);
                $totalsQueryForSums->where('gerceklesme', $odemeDurumu);
            }
            if ($odemeSekli && $odemeSekli !== '0') {
                $query->where('odeme_sekli_id', $odemeSekli);
                $totalsQueryForSums->where('odeme_sekli_id', $odemeSekli);
            }
            if ($ilgiliPersonel && $ilgiliPersonel !== '0') {
                $query->where('ilgili_personel_id', $ilgiliPersonel);
                $totalsQueryForSums->where('ilgili_personel_id', $ilgiliPersonel);
            }
            if ($tedarikci && $tedarikci !== '0') {
                $query->where('tedarikci_id', $tedarikci);
                $totalsQueryForSums->where('tedarikci_id', $tedarikci);
            }
            
            // Eğer hiçbir arama kriteri belirtilmediyse (tarihler ve diğer filtreler), bugünün kayıtlarını getir
            // Gerçekleşme tarihi için kasa tablosundaki 'islem_tarihi' sütunu baz alınır.
            // Bu filtre sadece asıl $query için geçerli olacak, $totalsQueryForSums için değil.
            if (!$tarih1 && !$tarih2 && !$odemeTuru && !$odemeYonu && !$odemeDurumu && !$odemeSekli && !$ilgiliPersonel && !$tedarikci && !$odenentutar) {
                $query->whereDate('islem_tarihi', Carbon::today());
                $totalsQueryForSums->whereDate('islem_tarihi', Carbon::today());
            }
            if (!$dateFrom && !$dateTo) {
                $dateFrom = Carbon::today()->format('Y-m-d');
                $dateTo = $dateFrom;
            } elseif ($dateFrom && !$dateTo) {
                $dateTo = $dateFrom;
            } elseif (!$dateFrom && $dateTo) {
                $dateFrom = $dateTo;
            }

            // Ödeme yönü: hem tablo hem toplamlar aynı filtreye göre olsun (tabloda ne görünüyorsa toplam da ona göre)
            if ($odemeYonu && ($odemeYonu === '1' || $odemeYonu === '-1')) {
                $query->where('odeme_yonu', $odemeYonu);
                $totalsQueryForSums->where('odeme_yonu', $odemeYonu);
            }

            Log::debug('KasaController@index - Totals Query For Sums SQL (final before sum):', ['sql' => $totalsQueryForSums->toSql(), 'bindings' => $totalsQueryForSums->getBindings(), 'count' => $totalsQueryForSums->count()]);

            $gelirSorgusu = (clone $totalsQueryForSums)->where('odeme_yonu', 1);
            Log::debug('KasaController@index - Gelir Toplam Sorgusu SQL:', ['sql' => $gelirSorgusu->toSql(), 'bindings' => $gelirSorgusu->getBindings()]);
            $gelirRecordsForDebug = $gelirSorgusu->get();
            Log::debug('KasaController@index - Gelir Records (for debug):', ['records' => $gelirRecordsForDebug->map(function($record) { return ['id' => $record->id, 'tutar' => $record->tutar, 'odeme_yonu' => $record->odeme_yonu]; })->toArray()]);
            $totalGelir = $gelirRecordsForDebug->sum('tutar');

            $giderSorgusu = (clone $totalsQueryForSums)->where('odeme_yonu', -1);
            Log::debug('KasaController@index - Gider Toplam Sorgusu SQL:', ['sql' => $giderSorgusu->toSql(), 'bindings' => $giderSorgusu->getBindings()]);
            $giderRecordsForDebug = $giderSorgusu->get();
            Log::debug('KasaController@index - Gider Records (for debug):', ['records' => $giderRecordsForDebug->map(function($record) { return ['id' => $record->id, 'tutar' => $record->tutar, 'odeme_yonu' => $record->odeme_yonu]; })->toArray()]);
            $totalGiderRaw = $giderRecordsForDebug->sum('tutar');

            // Teknisyen filtresi varsa sadece o teknisyen(ler) için pay toplamı hesapla
            $teknisyenFiltreIds = null;
            if ($ilgiliPersonel && $ilgiliPersonel !== '0') {
                $teknisyenFiltreIds = [(int) $ilgiliPersonel];
            } elseif ($odenentutar !== null && $odenentutar !== '') {
                $odenentutarTrimmed = trim((string) $odenentutar);
                if ($odenentutarTrimmed !== '' && !is_numeric($odenentutarTrimmed)) {
                    $teknisyenFiltreIds = Personel::where('ad', 'LIKE', '%' . $odenentutarTrimmed . '%')
                        ->where('poz_id', 1077)
                        ->pluck('id')
                        ->toArray();
                }
            }
            $teknisyenPaylariToplam = $this->calculateTeknisyenPaylariToplam($dateFrom, $dateTo, $teknisyenFiltreIds);
            // Ofis Gideri, Reklam Ödemesi, Uğur Bey Harcama, Maaş Ödemesi seçiliyse teknisyen payı gösterme ve hesaba katma
            $teknisyenPayiHaricTurler = ['Ofis Gideri', 'Reklam Ödemesi', 'Uğur Bey Harcama', 'Maaş Ödemesi'];
            $teknisyenPaylariGoster = true;
            if ($odemeTuru && $odemeTuru !== '0') {
                $seciliOdemeTuruAdi = KasaOdemeTuru::where('id', $odemeTuru)->value('ad');
                if ($seciliOdemeTuruAdi && in_array($seciliOdemeTuruAdi, $teknisyenPayiHaricTurler, true)) {
                    $teknisyenPaylariToplam = 0;
                    $teknisyenPaylariGoster = false;
                }
            }
            $bekleyenOdemeToplam = (clone $totalsQueryForSums)
                ->where('gerceklesme', 0)
                ->where('odeme_yonu', -1)
                ->sum('tutar');
            // Toplam Gider: kasa giderleri + teknisyen payları + bekleyen gider (hepsi filtrelenmiş setten)
            $totalGider = $totalGiderRaw + $teknisyenPaylariToplam + $bekleyenOdemeToplam;
            $netToplam = $totalGelir - $totalGider;

            // Ödeme şekillerine göre gelir detaylarını hesapla
            $gelirDetaylariByOdemeSekli = [];
            $tumOdemeSekilleri = KasaOdemeSekli::orderBy('sira')->get(['id', 'ad']);
            foreach ($tumOdemeSekilleri as $sekil) {
                $sekilGelir = (clone $totalsQueryForSums)
                                ->where('odeme_yonu', 1)
                                ->where('odeme_sekli_id', $sekil->id)
                                ->sum('tutar');
                if ($sekilGelir > 0) {
                    $gelirDetaylariByOdemeSekli[] = [
                        'ad' => $sekil->ad,
                        'tutar' => $sekilGelir,
                    ];
                }
            }

            $giderKalemleri = [
                'Servis İşlemleri',
                'Ofis Gideri',
                'Reklam Ödemesi',
                'Uğur Bey Harcama',
                'Maaş Ödemesi',
            ];
            $giderDetaylariByOdemeTuru = [];
            foreach ($giderKalemleri as $kalemAd) {
                $kalemTutar = (clone $totalsQueryForSums)
                    ->where('odeme_yonu', -1)
                    ->whereHas('odemeTuru', function ($q) use ($kalemAd) {
                        $q->where('ad', $kalemAd);
                    })
                    ->sum('tutar');
                $giderDetaylariByOdemeTuru[] = [
                    'ad' => $kalemAd,
                    'tutar' => $kalemTutar,
                ];
            }

            if ($teknisyenPaylariGoster) {
                $giderDetaylariByOdemeTuru[] = [
                    'ad' => 'Teknisyen Payları',
                    'tutar' => $teknisyenPaylariToplam,
                ];
            }
            $giderDetaylariByOdemeTuru[] = [
                'ad' => 'Bekleyen Ödeme',
                'tutar' => $bekleyenOdemeToplam,
            ];

            $kasaHareketleri = $query
                ->orderByDesc('islem_tarihi')
                ->orderByDesc('tarih')
                ->orderByDesc('saat')
                ->get();

            Log::info('Kasa hareketleri çekildi.', ['count' => $kasaHareketleri->count(), 'totalGelir' => $totalGelir, 'totalGider' => $totalGider, 'netToplam' => $netToplam, 'gelirDetaylari' => $gelirDetaylariByOdemeSekli]);

            $tumPersoneller = Personel::orderBy('ad')->get(['id', 'ad']);
            $teknisyenler = Personel::where('poz_id', 1077)->orderBy('ad')->get(['id', 'ad']);
            $tumOdemeTurleri = KasaOdemeTuru::orderBy('sira')->get(['id', 'ad', 'yon', 'muhattap']);
            // $tumOdemeSekilleri = KasaOdemeSekli::orderBy('sira')->get(['id', 'ad']); // Zaten yukarıda çekildi, tekrar çekmeye gerek yok

            $loggedInUser = Auth::user();
            $patronPozisyonId = 1071; // Patron pozisyon ID'si
            $isPatron = ($loggedInUser && $loggedInUser->poz_id === $patronPozisyonId);
            $canExport = ($loggedInUser && in_array((int) $loggedInUser->poz_id, [$patronPozisyonId, 1080], true));
            // Yeni kasa hareketi ekleme: sadece Patron (1071) ve Muhasebe (1080) – store() ile aynı kural
            $canAddKasa = ($loggedInUser && in_array((int) $loggedInUser->poz_id, [$patronPozisyonId, 1080], true));

            if ($request->ajax()) {
                // AJAX isteği ise sadece tablo satırlarını ve toplamları JSON olarak döndür
                return response()->json([
                    'html' => view('crm.kasa._kasa_table_rows', compact('kasaHareketleri', 'isPatron'))->render(),
                    'totalGelir' => $totalGelir,
                    'totalGider' => $totalGider,
                    'netToplam' => $netToplam,
                    'gelirDetaylariByOdemeSekli' => $gelirDetaylariByOdemeSekli,
                    'giderDetaylariByOdemeTuru' => $giderDetaylariByOdemeTuru,
                ]);
            } else {
                // Normal istek ise tüm sayfayı döndür
                return view('crm.kasa.index', compact('kasaHareketleri', 'tumPersoneller', 'teknisyenler', 'tumOdemeTurleri', 'tumOdemeSekilleri', 'isPatron', 'canExport', 'canAddKasa', 'totalGelir', 'totalGider', 'netToplam', 'gelirDetaylariByOdemeSekli', 'giderDetaylariByOdemeTuru'));
            }
        } catch (\Exception $e) {
             Log::error('Kasa hareketleri listesi çekilirken hata: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
             return view('crm.kasa.index', [
                'kasaHareketleri' => collect(),
                'tumPersoneller' => collect(),
                'teknisyenler' => collect(),
                'tumOdemeTurleri' => collect(),
                'tumOdemeSekilleri' => collect(),
                'isPatron' => false,
                'canExport' => false,
                'canAddKasa' => false,
                'totalGelir' => 0,
                'totalGider' => 0,
                'netToplam' => 0,
                'gelirDetaylariByOdemeSekli' => [],
                'giderDetaylariByOdemeTuru' => [],
            ])->withErrors('Kasa verileri yüklenirken bir sorun oluştu.');
        }
    }

    /**
     * @param  array<int>|null  $personelIdsFilter  Sadece bu ID'ler için hesapla; null ise tüm teknisyenler.
     */
    private function calculateTeknisyenPaylariToplam(string $dateFrom, string $dateTo, ?array $personelIdsFilter = null): float
    {
        $teknisyenPozisyonId = 1077; // TŞRN Teknisyen pozisyon ID'si
        $adetliDisiDurumlar = [9102, 9477, 9104, 9106, 9110]; // Müşteriye Ulaşılamadı, Yarın Gidilecek, Müşteri İptal Etti, Fiyatta Anlaşılamadı, Haber Verecek
        $teknisyenlerQuery = Personel::where('poz_id', $teknisyenPozisyonId);
        if ($personelIdsFilter !== null) {
            if (count($personelIdsFilter) === 0) {
                return 0.0;
            }
            $teknisyenlerQuery->whereIn('id', $personelIdsFilter);
        }
        $teknisyenler = $teknisyenlerQuery->get(['id', 'calisma_sekli_type', 'calisma_sekli_adet_tutar']);

        $startDate = Carbon::parse($dateFrom)->startOfDay();
        $endDate = Carbon::parse($dateTo)->startOfDay();
        if ($endDate->lt($startDate)) {
            [$startDate, $endDate] = [$endDate, $startDate];
        }

        $toplamTeknisyenPayi = 0.0;
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $gun = $date->format('Y-m-d');
            foreach ($teknisyenler as $teknisyen) {
                $kasaQuery = Kasa::query()
                    ->where(function ($q) use ($teknisyen) {
                        $q->where('personel_id', $teknisyen->id)
                            ->orWhere('ilgili_personel_id', $teknisyen->id)
                            ->orWhereHas('servis', function ($subQ) use ($teknisyen) {
                                $subQ->where('personel_id', $teknisyen->id);
                            });
                    })
                    ->whereDate('islem_tarihi', $gun)
                    ->where('gerceklesme', 1)
                    ->where(function ($q) {
                        $q->where('silindi', '!=', 1)
                          ->orWhereNull('silindi');
                    });

                $gelir = (clone $kasaQuery)->where('odeme_yonu', 1)->sum('tutar');
                $gider = (clone $kasaQuery)->where('odeme_yonu', -1)->sum('tutar');
                $netTutar = $gelir - $gider;

                $calismaSekliType = $teknisyen->calisma_sekli_type ?? null;
                $isAdetli = (Str::lower(trim((string) $calismaSekliType)) === 'adetli');
                $teknisyenPayi = 0;

                $adet = 0;
                if ($isAdetli) {
                    $adet = Servis::query()
                        ->where('personel_id', $teknisyen->id)
                        ->whereDate('tarih', $gun)
                        ->whereNotIn('servis_durum_id', $adetliDisiDurumlar)
                        ->where(function ($q) {
                            $q->where('silindi', '!=', 1)
                              ->orWhereNull('silindi');
                        })
                        ->count();
                } elseif ($calismaSekliType) {
                    preg_match_all('/\d{1,3}/', (string) $calismaSekliType, $matches);
                    $sayilar = $matches[0] ?? [];
                    if (count($sayilar) >= 1) {
                        $teknisyenYuzde = (int) $sayilar[0];
                        $teknisyenPayi = ($netTutar * $teknisyenYuzde) / 100;
                    }
                }

                $hasKasaHareketi = ($gelir > 0 || $gider > 0);
                $includeRow = $hasKasaHareketi || ($isAdetli && $adet > 0);
                if ($includeRow) {
                    $toplamTeknisyenPayi += $teknisyenPayi;
                }
            }
        }

        return $toplamTeknisyenPayi;
    }


    /**
     * Teknisyen bazlı günlük kasa özetini gösterir.
     */
    public function technicianDailySummary(Request $request)
    {
        Log::info('KasaController@technicianDailySummary çağrıldı.', ['query_params' => $request->query()]);

        $loggedInUser = Auth::user();
        $teknisyenPozisyonId = 1077; // TŞRN Teknisyen pozisyon ID'si
        $operatorPozisyonId = 1073; // Operatör pozisyon ID'si
        $idariIslerPozisyonId = 1076; // İdari İşler pozisyon ID'si

        if ($loggedInUser && in_array($loggedInUser->poz_id, [$operatorPozisyonId, $idariIslerPozisyonId])) {
            abort(403, 'Kasa kayıtlarını görüntüleme yetkiniz bulunmamaktadır.');
        }

        $tarih = $request->input('tarih');
        $tarih1 = $request->input('tarih1');
        $tarih2 = $request->input('tarih2');

        $dateFrom = $tarih1 && preg_match('/^\d{4}-\d{2}-\d{2}$/', $tarih1) ? $tarih1 : null;
        $dateTo = $tarih2 && preg_match('/^\d{4}-\d{2}-\d{2}$/', $tarih2) ? $tarih2 : null;
        if (!$dateFrom && !$dateTo && $tarih && preg_match('/^\d{4}-\d{2}-\d{2}$/', $tarih)) {
            $dateFrom = $tarih;
            $dateTo = $tarih;
        }
        if ($dateFrom && !$dateTo) {
            $dateTo = $dateFrom;
        }
        if ($dateTo && !$dateFrom) {
            $dateFrom = $dateTo;
        }
        if (!$dateFrom && !$dateTo) {
            $dateFrom = Carbon::today()->format('Y-m-d');
            $dateTo = $dateFrom;
        }
        $gun = $dateFrom;

        $selectedTeknisyenId = null;
        $canAdvancedSearch = $loggedInUser && in_array((int) $loggedInUser->poz_id, [1071, 1080], true);
        if ($canAdvancedSearch && $request->filled('teknisyen_id')) {
            $candidateId = (int) $request->input('teknisyen_id');
            $exists = Personel::where('id', $candidateId)
                ->where('poz_id', $teknisyenPozisyonId)
                ->exists();
            if ($exists) {
                $selectedTeknisyenId = $candidateId;
            }
        }

        $teknisyenlerQuery = Personel::where('poz_id', $teknisyenPozisyonId)->orderBy('ad');
        if ($loggedInUser && $loggedInUser->poz_id === $teknisyenPozisyonId) {
            $teknisyenlerQuery->where('id', $loggedInUser->id);
        } elseif ($selectedTeknisyenId) {
            $teknisyenlerQuery->where('id', $selectedTeknisyenId);
        }
        $odenentutar = $request->input('odenentutar');
        if ($odenentutar !== null && $odenentutar !== '') {
            $odenentutarTrimmed = trim((string) $odenentutar);
            if ($odenentutarTrimmed !== '' && !is_numeric($odenentutarTrimmed)) {
                $teknisyenlerQuery->where('ad', 'LIKE', '%' . $odenentutarTrimmed . '%');
            }
        }
        $teknisyenler = $teknisyenlerQuery->get(['id', 'ad', 'mesai_basladimi', 'calisma_sekli_type', 'calisma_sekli_adet_tutar']);

        $adetliDisiDurumlar = [9102, 9477, 9104, 9106, 9110]; // Müşteriye Ulaşılamadı, Yarın Gidilecek, Müşteri İptal Etti, Fiyatta Anlaşılamadı, Haber Verecek
        $adetliDisiDurumlar = [9102, 9477, 9104, 9106, 9110]; // Müşteriye Ulaşılamadı, Yarın Gidilecek, Müşteri İptal Etti, Fiyatta Anlaşılamadı, Haber Verecek
        $adetliDisiDurumlar = [9102, 9477, 9104, 9106, 9110]; // Müşteriye Ulaşılamadı, Yarın Gidilecek, Müşteri İptal Etti, Fiyatta Anlaşılamadı, Haber Verecek
        $adetliDisiDurumlar = [9102, 9477, 9104, 9106, 9110]; // Müşteriye Ulaşılamadı, Yarın Gidilecek, Müşteri İptal Etti, Fiyatta Anlaşılamadı, Haber Verecek
        $adetliDisiDurumlar = [9102, 9477, 9104, 9106, 9110]; // Müşteriye Ulaşılamadı, Yarın Gidilecek, Müşteri İptal Etti, Fiyatta Anlaşılamadı, Haber Verecek
        $adetliDisiDurumlar = [9102, 9477, 9104, 9106, 9110]; // Müşteriye Ulaşılamadı, Yarın Gidilecek, Müşteri İptal Etti, Fiyatta Anlaşılamadı, Haber Verecek
        $adetliDisiDurumlar = [9102, 9477, 9104, 9106, 9110]; // Müşteriye Ulaşılamadı, Yarın Gidilecek, Müşteri İptal Etti, Fiyatta Anlaşılamadı, Haber Verecek
        $gunlukOzetler = [];
        foreach ($teknisyenler as $teknisyen) {
            $kasaQuery = Kasa::query()
                ->where(function ($q) use ($teknisyen) {
                    $q->where('personel_id', $teknisyen->id)
                        ->orWhere('ilgili_personel_id', $teknisyen->id)
                        ->orWhereHas('servis', function ($subQ) use ($teknisyen) {
                            $subQ->where('personel_id', $teknisyen->id);
                        });
                })
                ->whereBetween('islem_tarihi', [$dateFrom, $dateTo])
                ->where('gerceklesme', 1)
                ->where(function ($q) {
                    $q->where('silindi', '!=', 1)
                      ->orWhereNull('silindi');
                });
            if ($odenentutar !== null && $odenentutar !== '') {
                $odenentutarTrimmed = trim((string) $odenentutar);
                if ($odenentutarTrimmed !== '' && is_numeric($odenentutarTrimmed)) {
                    $kasaQuery->where('tutar', $odenentutarTrimmed);
                }
            }

            $gelir = (clone $kasaQuery)->where('odeme_yonu', 1)->sum('tutar');
            $gider = (clone $kasaQuery)->where('odeme_yonu', -1)->sum('tutar');
            $netTutar = $gelir - $gider;
            $netTutar = $gelir - $gider;
            $netTutar = $gelir - $gider;
            $netTutar = $gelir - $gider;
            $netTutar = $gelir - $gider;
            $netTutar = $gelir - $gider;
            $netTutar = $gelir - $gider;
            $netTutar = $gelir - $gider;
            $netTutar = $gelir - $gider;
            $sonSaat = (clone $kasaQuery)
                ->whereNotNull('saat')
                ->orderByDesc('saat')
                ->value('saat');
            $netTutar = $gelir - $gider;

            $calismaSekliType = $teknisyen->calisma_sekli_type ?? null;
            $calismaAdetTutar = $teknisyen->calisma_sekli_adet_tutar ?? null;
            $isAdetli = (Str::lower(trim((string) $calismaSekliType)) === 'adetli');
            $teknisyenPayi = 0;
            $firmaPayi = 0;

            $adet = 0;
            if ($isAdetli) {
                $adet = Servis::query()
                    ->where('personel_id', $teknisyen->id)
                    ->whereBetween('tarih', [$dateFrom, $dateTo])
                    ->whereNotIn('servis_durum_id', $adetliDisiDurumlar)
                    ->where(function ($q) {
                        $q->where('silindi', '!=', 1)
                          ->orWhereNull('silindi');
                    })
                    ->count();
                if (!$sonSaat) {
                    $sonSaat = Servis::query()
                        ->where('personel_id', $teknisyen->id)
                        ->whereBetween('tarih', [$dateFrom, $dateTo])
                        ->whereNotIn('servis_durum_id', $adetliDisiDurumlar)
                        ->whereNotNull('saat')
                        ->orderByDesc('saat')
                        ->value('saat');
                }
                $firmaPayi = ($adet * (float) $calismaAdetTutar) - $gider;
            } elseif ($calismaSekliType) {
                preg_match_all('/\d{1,3}/', (string) $calismaSekliType, $matches);
                $sayilar = $matches[0] ?? [];
                if (count($sayilar) >= 2) {
                    $teknisyenYuzde = (int) $sayilar[0];
                    $firmaYuzde = (int) $sayilar[1];
                    $toplamYuzde = $teknisyenYuzde + $firmaYuzde;

                    if ($toplamYuzde > 0 && $toplamYuzde <= 100) {
                        $teknisyenPayi = (($gelir - $gider) * $teknisyenYuzde) / 100;
                        $firmaPayi = (($gelir - $gider) * $firmaYuzde) / 100;
                    }
                }
            } else {
                $teknisyenPayi = 0;
                $firmaPayi = ($gelir - $gider);
            }

            // Bekleyen ödeme (tarih filtresi yok) varsa satır listelensin
            $bekleyenTutar = (float) Kasa::query()
                ->where(function ($q) use ($teknisyen) {
                    $q->where('personel_id', $teknisyen->id)
                        ->orWhere('ilgili_personel_id', $teknisyen->id)
                        ->orWhereHas('servis', function ($subQ) use ($teknisyen) {
                            $subQ->where('personel_id', $teknisyen->id);
                        });
                })
                ->where('gerceklesme', 0)
                ->where(function ($q) {
                    $q->where('silindi', '!=', 1)->orWhereNull('silindi');
                })
                ->sum('tutar');
            $hasBekleyenOdeme = $bekleyenTutar > 0;

            $hasKasaHareketi = ($gelir > 0 || $gider > 0);
            $includeRow = $hasKasaHareketi || ($isAdetli && $adet > 0) || $hasBekleyenOdeme;
            if ($includeRow) {
                $gunlukOzetler[] = [
                    'teknisyen' => $teknisyen,
                    'gelir' => $gelir,
                    'gider' => $gider,
                    'adetli' => $isAdetli,
                    'teknisyen_payi' => $teknisyenPayi,
                    'firma_payi' => $firmaPayi,
                    'son_saat' => $sonSaat,
                    'bekleyen_tutar' => $bekleyenTutar,
                ];
            }
        }

        $toplamGelir = collect($gunlukOzetler)->sum('gelir')
            + collect($gunlukOzetler)->where('adetli', true)->sum('firma_payi');
        $toplamGider = collect($gunlukOzetler)->sum('gider');
        $toplamTeknisyenPayi = collect($gunlukOzetler)->sum('teknisyen_payi');
        $toplamFirmaPayi = collect($gunlukOzetler)->sum('firma_payi');
        $netKalan = $toplamFirmaPayi;

        // Bekleyen ödemeler (gerceklesme=0) – tarih ve yön filtresi yok; /kasa/bekleyen-odemeler ile aynı kapsam
        $teknisyenIds = $teknisyenler->pluck('id')->toArray();
        if (!empty($teknisyenIds)) {
            $bekleyenOdemeToplam = (float) Kasa::query()
                ->where(function ($q) use ($teknisyenIds) {
                    $q->whereIn('personel_id', $teknisyenIds)
                        ->orWhereIn('ilgili_personel_id', $teknisyenIds)
                        ->orWhereHas('servis', function ($subQ) use ($teknisyenIds) {
                            $subQ->whereIn('personel_id', $teknisyenIds);
                        });
                })
                ->where('gerceklesme', 0)
                ->where(function ($q) {
                    $q->where('silindi', '!=', 1)->orWhereNull('silindi');
                })
                ->sum('tutar');
        } else {
            $bekleyenOdemeToplam = 0.0;
        }

        $kasaKartlarAdetli = false;
        $netKalanTeknisyen = $netKalan;
        $patronPozisyonId = 1071;
        $muhasebePozisyonId = 1080;
        if ($loggedInUser && in_array((int) $loggedInUser->poz_id, [$patronPozisyonId, $muhasebePozisyonId], true)) {
            $netKalanTeknisyen = $netKalan;
        } elseif ($loggedInUser && $loggedInUser->poz_id === $teknisyenPozisyonId) {
            $calismaSekliType = $loggedInUser->calisma_sekli_type ?? null;
            $isAdetli = (Str::lower(trim((string) $calismaSekliType)) === 'adetli');
            if ($isAdetli) {
                $kasaKartlarAdetli = true;
            } else {
                $netKalanTeknisyen = collect($gunlukOzetler)->sum('teknisyen_payi');
            }
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'gun' => $gun,
                'html' => view('crm.kasa._pending_technician_rows', [
                    'gun' => $gun,
                    'gunlukOzetler' => $gunlukOzetler,
                ])->render(),
                'toplamGelir' => $toplamGelir,
                'toplamGider' => $toplamGider,
                'toplamTeknisyenPayi' => $toplamTeknisyenPayi,
                'toplamFirmaPayi' => $toplamFirmaPayi,
                'netKalan' => $netKalan,
                'netKalanTeknisyen' => $netKalanTeknisyen,
                'kasaKartlarAdetli' => $kasaKartlarAdetli,
                'bekleyenOdemeToplam' => $bekleyenOdemeToplam,
            ]);
        }

        $canExport = ($loggedInUser && in_array((int) $loggedInUser->poz_id, [1071, 1080], true));
        return view('crm.kasa.technician_daily', [
            'gun' => $gun,
            'gunlukOzetler' => $gunlukOzetler,
            'toplamGelir' => $toplamGelir,
            'toplamGider' => $toplamGider,
            'toplamTeknisyenPayi' => $toplamTeknisyenPayi,
            'toplamFirmaPayi' => $toplamFirmaPayi,
            'netKalan' => $netKalan,
            'netKalanTeknisyen' => $netKalanTeknisyen,
            'kasaKartlarAdetli' => $kasaKartlarAdetli,
            'bekleyenOdemeToplam' => $bekleyenOdemeToplam,
            'canExport' => $canExport,
            'selectedTeknisyenId' => $selectedTeknisyenId,
            'canAdvancedSearch' => $canAdvancedSearch,
            'teknisyenler' => $teknisyenler,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'odenentutar' => $odenentutar,
        ]);
    }

    public function exportGenelKasa(Request $request)
    {
        $loggedInUser = Auth::user();
        $patronPozisyonId = 1071;
        $muhasebePozisyonId = 1080;
        if (!$loggedInUser || !in_array((int) $loggedInUser->poz_id, [$patronPozisyonId, $muhasebePozisyonId], true)) {
            abort(403, 'Bu işlem için yetkiniz bulunmamaktadır.');
        }

        $query = Kasa::with(['odemeTuru', 'odemeSekli', 'personel', 'ilgiliPersonel', 'servis']);
        $query->where(function($q) {
            $q->where('silindi', '!=', 1)
              ->orWhereNull('silindi');
        });

        $odemeYonu = $request->input('odeme_yonu');
        $tarih1 = $request->input('tarih1');
        $tarih2 = $request->input('tarih2');
        $odenentutar = $request->input('odenentutar');
        $odemeTuru = $request->input('odeme_turu');
        $odemeDurumu = $request->input('odeme_durumu');
        $odemeSekli = $request->input('odeme_sekli');
        $ilgiliPersonel = $request->input('ilgili_personel');
        $tedarikci = $request->input('tedarikci');

        if ($tarih1) {
            $formattedTarih1 = null;
            try {
                $formattedTarih1 = Carbon::createFromFormat('d.m.Y', $tarih1)->format('Y-m-d');
            } catch (\Exception $e) {
                try {
                    $formattedTarih1 = Carbon::createFromFormat('Y-m-d', $tarih1)->format('Y-m-d');
                } catch (\Exception $e2) {
                    // no-op
                }
            }
            if ($formattedTarih1) {
                $query->whereDate('islem_tarihi', '>=', $formattedTarih1);
            }
        }
        if ($tarih2) {
            $formattedTarih2 = null;
            try {
                $formattedTarih2 = Carbon::createFromFormat('d.m.Y', $tarih2)->format('Y-m-d');
            } catch (\Exception $e) {
                try {
                    $formattedTarih2 = Carbon::createFromFormat('Y-m-d', $tarih2)->format('Y-m-d');
                } catch (\Exception $e2) {
                    // no-op
                }
            }
            if ($formattedTarih2) {
                $query->whereDate('islem_tarihi', '<=', $formattedTarih2);
            }
        }
        if (!$tarih1 && !$tarih2 && !$odemeTuru && !$odemeYonu && !$odemeDurumu && !$odemeSekli && !$ilgiliPersonel && !$tedarikci && !$odenentutar) {
            $query->whereDate('islem_tarihi', Carbon::today());
        }
        if ($odenentutar !== null && $odenentutar !== '') {
            $odenentutarTrimmed = trim((string) $odenentutar);
            if ($odenentutarTrimmed !== '' && is_numeric($odenentutarTrimmed)) {
                $query->where('tutar', $odenentutarTrimmed);
            } else {
                $query->whereHas('ilgiliPersonel', function ($q) use ($odenentutarTrimmed) {
                    $q->where('ad', 'LIKE', '%' . $odenentutarTrimmed . '%');
                });
            }
        }
        if ($odemeYonu && ($odemeYonu === '1' || $odemeYonu === '-1')) {
            $query->where('odeme_yonu', $odemeYonu);
        }
        if ($odemeTuru && $odemeTuru !== '0') {
            $query->where('odeme_turu_id', $odemeTuru);
        }
        if ($odemeDurumu !== null && $odemeDurumu !== '' && $odemeDurumu !== '-1') {
            $query->where('gerceklesme', $odemeDurumu);
        }
        if ($odemeSekli && $odemeSekli !== '0') {
            $query->where('odeme_sekli_id', $odemeSekli);
        }
        if ($ilgiliPersonel && $ilgiliPersonel !== '0') {
            $query->where('ilgili_personel_id', $ilgiliPersonel);
        }
        if ($tedarikci && $tedarikci !== '0') {
            $query->where('tedarikci_id', $tedarikci);
        }

        $totalsQueryForSums = clone $query;
        $dateFrom = null;
        $dateTo = null;
        if ($tarih1) {
            $ft1 = null;
            try { $ft1 = Carbon::createFromFormat('d.m.Y', $tarih1)->format('Y-m-d'); } catch (\Exception $e) {
                try { $ft1 = Carbon::createFromFormat('Y-m-d', $tarih1)->format('Y-m-d'); } catch (\Exception $e2) {}
            }
            if ($ft1) { $dateFrom = $ft1; }
        }
        if ($tarih2) {
            $ft2 = null;
            try { $ft2 = Carbon::createFromFormat('d.m.Y', $tarih2)->format('Y-m-d'); } catch (\Exception $e) {
                try { $ft2 = Carbon::createFromFormat('Y-m-d', $tarih2)->format('Y-m-d'); } catch (\Exception $e2) {}
            }
            if ($ft2) { $dateTo = $ft2; }
        }
        if (!$dateFrom && !$dateTo) {
            $dateFrom = Carbon::today()->format('Y-m-d');
            $dateTo = $dateFrom;
        } elseif ($dateFrom && !$dateTo) {
            $dateTo = $dateFrom;
        } elseif (!$dateFrom && $dateTo) {
            $dateFrom = $dateTo;
        }

        $totalGelir = (clone $totalsQueryForSums)->where('odeme_yonu', 1)->sum('tutar');
        $totalGiderRaw = (clone $totalsQueryForSums)->where('odeme_yonu', -1)->sum('tutar');
        $teknisyenFiltreIds = null;
        if ($ilgiliPersonel && $ilgiliPersonel !== '0') {
            $teknisyenFiltreIds = [(int) $ilgiliPersonel];
        } elseif ($odenentutar !== null && $odenentutar !== '') {
            $trimmed = trim((string) $odenentutar);
            if ($trimmed !== '' && !is_numeric($trimmed)) {
                $teknisyenFiltreIds = Personel::where('ad', 'LIKE', '%' . $trimmed . '%')->where('poz_id', 1077)->pluck('id')->toArray();
            }
        }
        $teknisyenPaylariToplam = $this->calculateTeknisyenPaylariToplam($dateFrom, $dateTo, $teknisyenFiltreIds);
        $teknisyenPayiHaricTurlerExport = ['Ofis Gideri', 'Reklam Ödemesi', 'Uğur Bey Harcama', 'Maaş Ödemesi'];
        if ($odemeTuru && $odemeTuru !== '0') {
            $seciliOdemeTuruAdiExport = KasaOdemeTuru::where('id', $odemeTuru)->value('ad');
            if ($seciliOdemeTuruAdiExport && in_array($seciliOdemeTuruAdiExport, $teknisyenPayiHaricTurlerExport, true)) {
                $teknisyenPaylariToplam = 0;
            }
        }
        $bekleyenOdemeToplam = (clone $totalsQueryForSums)->where('gerceklesme', 0)->where('odeme_yonu', -1)->sum('tutar');
        $totalGider = $totalGiderRaw + $teknisyenPaylariToplam + $bekleyenOdemeToplam;
        $netToplam = $totalGelir - $totalGider;

        $rows = $query->orderByDesc('islem_tarihi')
            ->orderByDesc('tarih')
            ->orderByDesc('saat')
            ->get();

        $filename = 'genelkasa_' . Carbon::now()->format('Ymd_His') . '.csv';
        $headers = ['K.NO', 'TARİH', 'ÖDEME TÜRÜ', 'AÇIKLAMA', 'ÖDEME ŞEKLİ', 'TEKNİSYEN', 'DURUM', 'GERÇEKLEŞME TARİHİ', 'TUTAR'];
        $totalGelirFormatted = number_format((float) $totalGelir, 2, ',', '');
        $totalGiderFormatted = number_format((float) $totalGider, 2, ',', '');
        $netToplamFormatted = number_format((float) $netToplam, 2, ',', '');

        return response()->streamDownload(function() use ($rows, $headers, $totalGelirFormatted, $totalGiderFormatted, $netToplamFormatted) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, $headers, ';');
            foreach ($rows as $kasa) {
                $tarih = $kasa->tarih ? ($kasa->tarih . ($kasa->saat ? ' ' . $kasa->saat : '')) : '';
                $durum = ((int) $kasa->gerceklesme === 1) ? 'Tamamlandı' : 'Beklemede';
                $teknisyenAd = $kasa->ilgiliPersonel->ad ?? '';
                fputcsv($handle, [
                    $kasa->id,
                    $tarih,
                    $kasa->odemeTuru->ad ?? '',
                    $kasa->aciklama ?? '',
                    $kasa->odemeSekli->ad ?? '',
                    $teknisyenAd,
                    $durum,
                    $kasa->islem_tarihi ?? '',
                    number_format((float) ($kasa->tutar ?? 0), 2, ',', ''),
                ], ';');
            }
            fputcsv($handle, [], ';');
            fputcsv($handle, ['TOPLAM GELİR', '', '', '', '', '', '', '', $totalGelirFormatted], ';');
            fputcsv($handle, ['TOPLAM GİDER', '', '', '', '', '', '', '', $totalGiderFormatted], ';');
            fputcsv($handle, ['GENEL TOPLAM', '', '', '', '', '', '', '', $netToplamFormatted], ';');
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function exportTechnicianDailySummary(Request $request)
    {
        $loggedInUser = Auth::user();
        $patronPozisyonId = 1071;
        $muhasebePozisyonId = 1080;
        if (!$loggedInUser || !in_array((int) $loggedInUser->poz_id, [$patronPozisyonId, $muhasebePozisyonId], true)) {
            abort(403, 'Bu işlem için yetkiniz bulunmamaktadır.');
        }

        $tarih = $request->input('tarih');
        $tarih1 = $request->input('tarih1');
        $tarih2 = $request->input('tarih2');

        $dateFrom = $tarih1 && preg_match('/^\d{4}-\d{2}-\d{2}$/', $tarih1) ? $tarih1 : null;
        $dateTo = $tarih2 && preg_match('/^\d{4}-\d{2}-\d{2}$/', $tarih2) ? $tarih2 : null;
        if (!$dateFrom && !$dateTo && $tarih && preg_match('/^\d{4}-\d{2}-\d{2}$/', $tarih)) {
            $dateFrom = $tarih;
            $dateTo = $tarih;
        }
        if ($dateFrom && !$dateTo) {
            $dateTo = $dateFrom;
        }
        if ($dateTo && !$dateFrom) {
            $dateFrom = $dateTo;
        }
        if (!$dateFrom && !$dateTo) {
            $dateFrom = Carbon::today()->format('Y-m-d');
            $dateTo = $dateFrom;
        }
        $gun = $dateFrom;

        $selectedTeknisyenId = null;
        if ($request->filled('teknisyen_id')) {
            $candidateId = (int) $request->input('teknisyen_id');
            $exists = Personel::where('id', $candidateId)
                ->where('poz_id', 1077)
                ->exists();
            if ($exists) {
                $selectedTeknisyenId = $candidateId;
            }
        }

        $teknisyenlerQuery = Personel::where('poz_id', 1077)->orderBy('ad');
        if ($selectedTeknisyenId) {
            $teknisyenlerQuery->where('id', $selectedTeknisyenId);
        }
        $teknisyenler = $teknisyenlerQuery->get(['id', 'ad', 'mesai_basladimi', 'calisma_sekli_type', 'calisma_sekli_adet_tutar']);

        $adetliDisiDurumlar = [9102, 9477, 9104, 9106, 9110]; // Müşteriye Ulaşılamadı, Yarın Gidilecek, Müşteri İptal Etti, Fiyatta Anlaşılamadı, Haber Verecek
        $gunlukOzetler = [];
        foreach ($teknisyenler as $teknisyen) {
            $kasaQuery = Kasa::query()
                ->where(function ($q) use ($teknisyen) {
                    $q->where('personel_id', $teknisyen->id)
                        ->orWhere('ilgili_personel_id', $teknisyen->id)
                        ->orWhereHas('servis', function ($subQ) use ($teknisyen) {
                            $subQ->where('personel_id', $teknisyen->id);
                        });
                })
                ->whereBetween('islem_tarihi', [$dateFrom, $dateTo])
                ->where('gerceklesme', 1)
                ->where(function ($q) {
                    $q->where('silindi', '!=', 1)
                      ->orWhereNull('silindi');
                });
            if ($request->filled('odenentutar')) {
                $kasaQuery->where('tutar', $request->input('odenentutar'));
            }

            $gelir = (clone $kasaQuery)->where('odeme_yonu', 1)->sum('tutar');
            $gider = (clone $kasaQuery)->where('odeme_yonu', -1)->sum('tutar');
            $netTutar = $gelir - $gider;

            $calismaSekliType = $teknisyen->calisma_sekli_type ?? null;
            $calismaAdetTutar = $teknisyen->calisma_sekli_adet_tutar ?? null;
            $isAdetli = (Str::lower(trim((string) $calismaSekliType)) === 'adetli');
            $teknisyenPayi = 0;
            $firmaPayi = 0;

            $adet = 0;
            if ($isAdetli) {
                $adet = Servis::query()
                    ->where('personel_id', $teknisyen->id)
                    ->whereBetween('tarih', [$dateFrom, $dateTo])
                    ->whereNotIn('servis_durum_id', $adetliDisiDurumlar)
                    ->where(function ($q) {
                        $q->where('silindi', '!=', 1)
                          ->orWhereNull('silindi');
                    })
                    ->count();
                $firmaPayi = ($adet * (float) $calismaAdetTutar) - $gider;
            } elseif ($calismaSekliType) {
                preg_match_all('/\d{1,3}/', (string) $calismaSekliType, $matches);
                $sayilar = $matches[0] ?? [];
                if (count($sayilar) >= 2) {
                    $teknisyenYuzde = (int) $sayilar[0];
                    $firmaYuzde = (int) $sayilar[1];
                    $toplamYuzde = $teknisyenYuzde + $firmaYuzde;

                    if ($toplamYuzde > 0 && $toplamYuzde <= 100) {
                        $teknisyenPayi = ($netTutar * $teknisyenYuzde) / 100;
                        $firmaPayi = ($netTutar * $firmaYuzde) / 100;
                    }
                }
            } else {
                $teknisyenPayi = 0;
                $firmaPayi = $netTutar;
            }

            $hasKasaHareketi = ($gelir > 0 || $gider > 0);
            $includeRow = $hasKasaHareketi || ($isAdetli && $adet > 0);
            if ($includeRow) {
                $gunlukOzetler[] = [
                    'teknisyen' => $teknisyen,
                    'gelir' => $gelir,
                    'gider' => $gider,
                    'teknisyen_payi' => $teknisyenPayi,
                    'firma_payi' => $firmaPayi,
                ];
            }
        }

        $filename = 'kasa_' . $gun . '.csv';
        $headers = ['TARİH', 'TEKNİSYEN', 'MESAİ', 'GELİR', 'GİDER', 'TEKNİSYEN PAYI', 'FİRMA PAYI'];

        $dateLabel = $dateFrom === $dateTo ? $dateFrom : ($dateFrom . ' - ' . $dateTo);

        return response()->streamDownload(function() use ($dateLabel, $gunlukOzetler, $headers) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, $headers, ';');
            foreach ($gunlukOzetler as $ozet) {
                $teknisyen = $ozet['teknisyen'];
                $mesai = ((int) ($teknisyen->mesai_basladimi ?? 0) === 1) ? 'Çalışıyor' : 'Çalışmıyor';
                fputcsv($handle, [
                    $dateLabel ? $dateLabel : '-',
                    $teknisyen->ad ?? '',
                    $mesai,
                    $ozet['gelir'],
                    $ozet['gider'],
                    $ozet['teknisyen_payi'],
                    $ozet['firma_payi'],
                ], ';');
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * Teknisyen bazlı günlük detayları getirir.
     * tarih1 + tarih2 gönderilirse tarih aralığı, yoksa tek tarih (tarih) kullanılır.
     */
    public function technicianDailyDetail(Request $request)
    {
        $teknisyenId = (int) $request->input('teknisyen_id');
        $tarih = $request->input('tarih');
        $tarih1 = $request->input('tarih1');
        $tarih2 = $request->input('tarih2');

        $dateFrom = null;
        $dateTo = null;
        if ($tarih1 && preg_match('/^\d{4}-\d{2}-\d{2}$/', $tarih1) && $tarih2 && preg_match('/^\d{4}-\d{2}-\d{2}$/', $tarih2)) {
            $dateFrom = $tarih1;
            $dateTo = $tarih2;
            if ($dateFrom > $dateTo) {
                [$dateFrom, $dateTo] = [$dateTo, $dateFrom];
            }
        }
        if (!$dateFrom || !$dateTo) {
            $gun = $tarih && preg_match('/^\d{4}-\d{2}-\d{2}$/', $tarih)
                ? $tarih
                : Carbon::today()->format('Y-m-d');
            $dateFrom = $gun;
            $dateTo = $gun;
        }
        $gun = $dateFrom;

        $loggedInUser = Auth::user();
        $teknisyenPozisyonId = 1077;
        $canOpenServisDetay = !($loggedInUser && $loggedInUser->poz_id === $teknisyenPozisyonId);
        if ($loggedInUser && $loggedInUser->poz_id === $teknisyenPozisyonId && $loggedInUser->id !== $teknisyenId) {
            return response()->json(['success' => false, 'message' => 'Yetkisiz işlem.'], 403);
        }

        $teknisyen = Personel::where('poz_id', $teknisyenPozisyonId)
            ->where('id', $teknisyenId)
            ->first(['id', 'ad', 'calisma_sekli_type', 'calisma_sekli_adet_tutar']);

        if (!$teknisyen) {
            return response()->json(['success' => false, 'message' => 'Teknisyen bulunamadı.'], 404);
        }

        // Tarih aralığındaki tamamlanan hareketler
        $kasaTamamlanan = Kasa::with(['odemeTuru', 'odemeSekli', 'servis.musteri', 'servis.servisDurum', 'servis.marka', 'servis.cihazTuru'])
            ->where(function ($q) use ($teknisyen) {
                $q->where('personel_id', $teknisyen->id)
                  ->orWhere('ilgili_personel_id', $teknisyen->id)
                  ->orWhereHas('servis', function ($subQ) use ($teknisyen) {
                      $subQ->where('personel_id', $teknisyen->id);
                  });
            })
            ->whereBetween('islem_tarihi', [$dateFrom, $dateTo])
            ->where('gerceklesme', 1)
            ->where(function ($q) {
                $q->where('silindi', '!=', 1)->orWhereNull('silindi');
            })
            ->orderBy('islem_tarihi')
            ->orderBy('saat')
            ->get();

        // Bekleyen ödemeler (tarih filtresi yok) – bekleyen-odemeler sayfası gibi, kırmızı gösterilecek
        $kasaBekleyen = Kasa::with(['odemeTuru', 'odemeSekli', 'servis.musteri', 'servis.servisDurum', 'servis.marka', 'servis.cihazTuru'])
            ->where(function ($q) use ($teknisyen) {
                $q->where('personel_id', $teknisyen->id)
                  ->orWhere('ilgili_personel_id', $teknisyen->id)
                  ->orWhereHas('servis', function ($subQ) use ($teknisyen) {
                      $subQ->where('personel_id', $teknisyen->id);
                  });
            })
            ->where('gerceklesme', 0)
            ->where(function ($q) {
                $q->where('silindi', '!=', 1)->orWhereNull('silindi');
            })
            ->orderBy('islem_tarihi')
            ->orderBy('saat')
            ->get();

        $baseKasaQuery = function () use ($kasaBekleyen, $kasaTamamlanan) {
            return $kasaBekleyen->concat($kasaTamamlanan);
        };

        $isAdetli = (Str::lower(trim((string) $teknisyen->calisma_sekli_type)) === 'adetli');
        if ($isAdetli) {
            $adetliDisiDurumlar = [9102, 9477, 9104, 9106, 9110]; // Müşteriye Ulaşılamadı, Yarın Gidilecek, Müşteri İptal Etti, Fiyatta Anlaşılamadı, Haber Verecek
            $servisler = Servis::with(['musteri', 'servisDurum', 'marka', 'cihazTuru'])
                ->where('personel_id', $teknisyen->id)
                ->whereBetween('tarih', [$dateFrom, $dateTo])
                ->whereNotIn('servis_durum_id', $adetliDisiDurumlar)
                ->where(function ($q) {
                    $q->where('silindi', '!=', 1)
                      ->orWhereNull('silindi');
                })
                ->orderBy('tarih')
                ->orderBy('saat')
                ->get(['id', 'tarih', 'saat', 'cihaz_model', 'cihaz_arizasi', 'operator_not', 'musteri_id', 'servis_durum_id', 'marka_id', 'cihaz_tur_id']);

            $kasaHareketleri = $baseKasaQuery();

            $html = view('crm.kasa._technician_daily_detail', [
                'gun' => $gun,
                'dateFrom' => $dateFrom,
                'dateTo' => $dateTo,
                'teknisyen' => $teknisyen,
                'adetli' => true,
                'servisler' => $servisler,
                'kasaHareketleri' => $kasaHareketleri,
                'canOpenServisDetay' => $canOpenServisDetay,
            ])->render();

            return response()->json(['success' => true, 'html' => $html]);
        }

        $kasaHareketleri = $baseKasaQuery();

        $html = view('crm.kasa._technician_daily_detail', [
            'gun' => $gun,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'teknisyen' => $teknisyen,
            'adetli' => false,
            'servisler' => collect(),
            'kasaHareketleri' => $kasaHareketleri,
            'canOpenServisDetay' => $canOpenServisDetay,
        ])->render();

        return response()->json(['success' => true, 'html' => $html]);
    }

    /**
     * Seçili teknisyenlerin girişini bloke et (mesai_basladimi = 0).
     */
    public function blockTechnicians(Request $request)
    {
        $loggedInUser = Auth::user();
        $patronPozisyonId = 1071;
        $muhasebePozisyonId = 1080;

        if (!$loggedInUser || !in_array((int) $loggedInUser->poz_id, [$patronPozisyonId, $muhasebePozisyonId])) {
            return response()->json(['success' => false, 'message' => 'Bu işlem için yetkiniz bulunmamaktadır.'], 403);
        }

        $ids = $request->input('teknisyen_ids', []);
        if (!is_array($ids) || empty($ids)) {
            return response()->json(['success' => false, 'message' => 'Seçili teknisyen bulunamadı.'], 422);
        }

        $filteredIds = collect($ids)
            ->map(function ($id) { return (int) $id; })
            ->filter(function ($id) { return $id > 0; })
            ->values()
            ->all();

        if (empty($filteredIds)) {
            return response()->json(['success' => false, 'message' => 'Geçerli teknisyen bulunamadı.'], 422);
        }

        $updatedCount = Personel::whereIn('id', $filteredIds)
            ->update(['mesai_basladimi' => 0]);

        $afterValues = Personel::whereIn('id', $filteredIds)
            ->pluck('mesai_basladimi', 'id');

        $stillActiveIds = $afterValues
            ->filter(function ($value) { return (int) $value === 1; })
            ->keys()
            ->values()
            ->all();

        return response()->json([
            'success' => true,
            'updated' => $updatedCount,
            'after' => $afterValues,
            'still_active_ids' => $stillActiveIds,
            'message' => $updatedCount > 0
                ? 'Seçilen teknisyenlerin girişleri bloke edildi.'
                : 'Seçilen teknisyenler zaten blokeli görünüyor.',
        ]);
    }

    /**
     * Seçili teknisyenlerin yalnızca mesai durumunu güncelle (0/1).
     * aktif alanına dokunulmaz: pasif (aktif=0) personel mesai açılınca yeniden aktifleşmez;
     * mesai kapatılınca aktif personel yönlendirme listesinden düşmez.
     */
    private function updateTechnicianShift(Request $request, int $mesaiBasladimi)
    {
        $loggedInUser = Auth::user();
        $patronPozisyonId = 1071;
        $muhasebePozisyonId = 1080;

        if (!$loggedInUser || !in_array((int) $loggedInUser->poz_id, [$patronPozisyonId, $muhasebePozisyonId])) {
            return response()->json(['success' => false, 'message' => 'Bu işlem için yetkiniz bulunmamaktadır.'], 403);
        }

        $ids = $request->input('teknisyen_ids', []);
        if (!is_array($ids) || empty($ids)) {
            return response()->json(['success' => false, 'message' => 'Seçili teknisyen bulunamadı.'], 422);
        }

        $filteredIds = collect($ids)
            ->map(function ($id) { return (int) $id; })
            ->filter(function ($id) { return $id > 0; })
            ->values()
            ->all();

        if (empty($filteredIds)) {
            return response()->json(['success' => false, 'message' => 'Geçerli teknisyen bulunamadı.'], 422);
        }

        // Mesai aç: sadece aktif personelde mesai_basladimi=1; pasifler atlanır.
        // Mesai kapat: sadece mesai_basladimi=0; aktif bayrağı korunur.
        $query = Personel::whereIn('id', $filteredIds);
        if ($mesaiBasladimi === 1) {
            $query->where('aktif', 1);
        }

        $updatedCount = $query->update([
            'mesai_basladimi' => $mesaiBasladimi,
        ]);

        $afterRows = Personel::whereIn('id', $filteredIds)
            ->get(['id', 'mesai_basladimi', 'aktif']);

        $afterValues = $afterRows->pluck('mesai_basladimi', 'id');

        $wrongStateIds = $afterRows
            ->filter(function ($row) use ($mesaiBasladimi) {
                // Mesai açarken pasifler bilinçli atlanır; hata listesine yazılmaz.
                if ($mesaiBasladimi === 1 && (int) $row->aktif !== 1) {
                    return false;
                }
                return (int) $row->mesai_basladimi !== $mesaiBasladimi;
            })
            ->pluck('id')
            ->values()
            ->all();

        $skippedPassive = 0;
        if ($mesaiBasladimi === 1) {
            $skippedPassive = $afterRows->where('aktif', 0)->count();
        }

        $message = $updatedCount > 0
            ? 'Seçilen teknisyenlerin mesai durumu güncellendi.'
            : 'Seçilen teknisyenler zaten aynı durumda.';
        if ($skippedPassive > 0) {
            $message .= ' Pasif ' . $skippedPassive . ' teknisyen atlandı (aktif yapılmadı).';
        }

        return response()->json([
            'success' => true,
            'updated' => $updatedCount,
            'after' => $afterValues,
            'still_active_ids' => $wrongStateIds,
            'skipped_passive' => $skippedPassive,
            'message' => $message,
        ]);
    }

    /**
     * Seçili teknisyenlerin mesaisini kapat (yalnızca mesai_basladimi = 0; aktif değişmez).
     */
    public function closeTechnicianShift(Request $request)
    {
        return $this->updateTechnicianShift($request, 0);
    }

    /**
     * Seçili teknisyenlerin mesaisini aç (yalnızca aktif=1 olanlarda mesai_basladimi = 1).
     */
    public function openTechnicianShift(Request $request)
    {
        return $this->updateTechnicianShift($request, 1);
    }

    /**
     * Seçili teknisyenlerin güncel mesai durumunu döndür.
     */
    public function getTechnicianMesaiMap(Request $request)
    {
        $loggedInUser = Auth::user();
        $patronPozisyonId = 1071;
        $muhasebePozisyonId = 1080;

        if (!$loggedInUser || !in_array((int) $loggedInUser->poz_id, [$patronPozisyonId, $muhasebePozisyonId])) {
            return response()->json(['success' => false, 'message' => 'Bu işlem için yetkiniz bulunmamaktadır.'], 403);
        }

        $ids = $request->input('teknisyen_ids', []);
        if (!is_array($ids) || empty($ids)) {
            return response()->json(['success' => false, 'message' => 'Seçili teknisyen bulunamadı.'], 422);
        }

        $filteredIds = collect($ids)
            ->map(function ($id) { return (int) $id; })
            ->filter(function ($id) { return $id > 0; })
            ->values()
            ->all();

        if (empty($filteredIds)) {
            return response()->json(['success' => false, 'message' => 'Geçerli teknisyen bulunamadı.'], 422);
        }

        $mesaiMap = Personel::whereIn('id', $filteredIds)
            ->pluck('mesai_basladimi', 'id')
            ->map(function ($value) { return (int) $value; });

        return response()->json([
            'success' => true,
            'mesaiMap' => $mesaiMap,
        ]);
    }

    /**
     * Bekleyen ödemeler için teknisyen bazlı günlük özet.
     */
    public function pendingTechnicianSummary(Request $request)
    {
        Log::info('KasaController@pendingTechnicianSummary çağrıldı.', ['query_params' => $request->query()]);

        $loggedInUser = Auth::user();
        $teknisyenPozisyonId = 1077; // TŞRN Teknisyen pozisyon ID'si
        $operatorPozisyonId = 1073; // Operatör pozisyon ID'si
        $idariIslerPozisyonId = 1076; // İdari İşler pozisyon ID'si

        if ($loggedInUser && in_array($loggedInUser->poz_id, [$operatorPozisyonId, $idariIslerPozisyonId])) {
            abort(403, 'Kasa kayıtlarını görüntüleme yetkiniz bulunmamaktadır.');
        }

        $gun = 'Tümü';

        $teknisyenlerQuery = Personel::where('poz_id', $teknisyenPozisyonId)->orderBy('ad');
        if ($loggedInUser && $loggedInUser->poz_id === $teknisyenPozisyonId) {
            $teknisyenlerQuery->where('id', $loggedInUser->id);
        }
        $teknisyenler = $teknisyenlerQuery->get(['id', 'ad', 'calisma_sekli_type', 'calisma_sekli_adet_tutar']);

        $gunlukOzetler = [];
        foreach ($teknisyenler as $teknisyen) {
            $kasaQuery = Kasa::query()
                ->where(function ($q) use ($teknisyen) {
                    $q->where('personel_id', $teknisyen->id)
                        ->orWhere('ilgili_personel_id', $teknisyen->id)
                        ->orWhereHas('servis', function ($subQ) use ($teknisyen) {
                            $subQ->where('personel_id', $teknisyen->id);
                        });
                })
                ->where('gerceklesme', 0)
                ->where(function ($q) {
                    $q->where('silindi', '!=', 1)
                      ->orWhereNull('silindi');
                });

            $gelir = (clone $kasaQuery)->where('odeme_yonu', 1)->sum('tutar');
            $gider = (clone $kasaQuery)->where('odeme_yonu', -1)->sum('tutar');

            $calismaSekliType = $teknisyen->calisma_sekli_type ?? null;
            $teknisyenPayi = 0;
            $firmaPayi = 0;

            if ($calismaSekliType) {
                preg_match_all('/\d{1,3}/', (string) $calismaSekliType, $matches);
                $sayilar = $matches[0] ?? [];
                if (count($sayilar) >= 2) {
                    $teknisyenYuzde = (int) $sayilar[0];
                    $firmaYuzde = (int) $sayilar[1];
                    $toplamYuzde = $teknisyenYuzde + $firmaYuzde;

                    if ($toplamYuzde > 0 && $toplamYuzde <= 100) {
                        $teknisyenPayi = ($gelir * $teknisyenYuzde) / 100;
                        $firmaPayi = ($gelir * $firmaYuzde) / 100;
                    }
                } else {
                    $firmaPayi = $gelir;
                }
            } else {
                $firmaPayi = $gelir;
            }

            if ($gelir > 0 || $gider > 0) {
                $gunlukOzetler[] = [
                    'teknisyen' => $teknisyen,
                    'gelir' => $gelir,
                    'gider' => $gider,
                    'adetli' => false,
                    'teknisyen_payi' => $teknisyenPayi,
                    'firma_payi' => $firmaPayi,
                ];
            }
        }

        $toplamGelir = collect($gunlukOzetler)->sum('gelir');
        $toplamGider = collect($gunlukOzetler)->sum('gider');
        $toplamTeknisyenPayi = collect($gunlukOzetler)->sum('teknisyen_payi');
        $netKalan = $toplamGelir - $toplamGider - $toplamTeknisyenPayi;

        if ($request->ajax()) {
            $mesaiMap = collect($gunlukOzetler)->mapWithKeys(function ($ozet) {
                return [(int) $ozet['teknisyen']->id => (int) ($ozet['teknisyen']->mesai_basladimi ?? 0)];
            });
            return response()->json([
                'success' => true,
                'gun' => $gun,
                'html' => view('crm.kasa._technician_daily_rows_v2', [
                    'gun' => $gun,
                    'gunlukOzetler' => $gunlukOzetler,
                ])->render(),
                'toplamGelir' => $toplamGelir,
                'toplamGider' => $toplamGider,
                'netKalan' => $netKalan,
                'mesaiMap' => $mesaiMap,
            ]);
        }

        return view('crm.kasa.pending_technician_daily', [
            'gun' => $gun,
            'gunlukOzetler' => $gunlukOzetler,
            'toplamGelir' => $toplamGelir,
            'toplamGider' => $toplamGider,
            'netKalan' => $netKalan,
        ]);
    }

    /**
     * Bekleyen ödemeler için teknisyen bazlı detayları getirir.
     */
    public function pendingTechnicianDetail(Request $request)
    {
        $teknisyenId = (int) $request->input('teknisyen_id');
        $gun = 'Tümü';

        $loggedInUser = Auth::user();
        $teknisyenPozisyonId = 1077;
        $canOpenServisDetay = !($loggedInUser && $loggedInUser->poz_id === $teknisyenPozisyonId);
        if ($loggedInUser && $loggedInUser->poz_id === $teknisyenPozisyonId && $loggedInUser->id !== $teknisyenId) {
            return response()->json(['success' => false, 'message' => 'Yetkisiz işlem.'], 403);
        }

        $teknisyen = Personel::where('poz_id', $teknisyenPozisyonId)
            ->where('id', $teknisyenId)
            ->first(['id', 'ad', 'calisma_sekli_type', 'calisma_sekli_adet_tutar']);

        if (!$teknisyen) {
            return response()->json(['success' => false, 'message' => 'Teknisyen bulunamadı.'], 404);
        }

        $kasaHareketleri = Kasa::with(['odemeTuru', 'odemeSekli', 'servis.musteri', 'servis.servisDurum', 'servis.marka', 'servis.cihazTuru'])
            ->where(function ($q) use ($teknisyen) {
                $q->where('personel_id', $teknisyen->id)
                  ->orWhere('ilgili_personel_id', $teknisyen->id)
                  ->orWhereHas('servis', function ($subQ) use ($teknisyen) {
                      $subQ->where('personel_id', $teknisyen->id);
                  });
            })
            ->where('gerceklesme', 0)
            ->where(function ($q) {
                $q->where('silindi', '!=', 1)
                  ->orWhereNull('silindi');
            })
            ->orderBy('tarih')
            ->orderBy('saat')
            ->get();

        $html = view('crm.kasa._technician_daily_detail', [
            'gun' => $gun,
            'teknisyen' => $teknisyen,
            'adetli' => false,
            'servisler' => collect(),
            'kasaHareketleri' => $kasaHareketleri,
            'canOpenServisDetay' => $canOpenServisDetay,
        ])->render();

        return response()->json(['success' => true, 'html' => $html]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Genellikle form göstermek için kullanılır, API için gerekmeyebilir
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Log::info('KasaController@store çağrıldı.', ['data' => $this->sanitizeLogData($request->all())]);

        $loggedInUser = Auth::user();
        $allowedPozisyonIds = [1071, 1080]; // Patron, Muhasebe

        if (!$loggedInUser || !in_array((int) $loggedInUser->poz_id, $allowedPozisyonIds, true)) {
            Log::warning('Yetkisiz kasa kaydı oluşturma denemesi.', ['user_id' => $loggedInUser->id ?? null, 'poz_id' => $loggedInUser->poz_id ?? null]);
            return response()->json(['success' => false, 'message' => 'Yeni kasa kaydı oluşturma yetkiniz bulunmamaktadır.'], 403);
        }

        // Güncellemede odeme_yonu değiştirilmez; mevcut kayıttan korunur.

        $patronPozisyonId = 1071; // Tanımı başa taşıdım

        // Frontend 'yon' veya 'odeme_yonu' gönderebilir; validasyon öncesi tek kaynak: odeme_yonu
        $yonDeger = $request->filled('odeme_yonu') ? $request->input('odeme_yonu') : $request->input('yon');
        if ($yonDeger !== null && $yonDeger !== '') {
            $request->merge(['odeme_yonu' => (string) $yonDeger]);
        }

        $selectedOdemeTuruId = $request->input('odeme_turu_id');
        $odemeTuru = $selectedOdemeTuruId ? KasaOdemeTuru::find($selectedOdemeTuruId) : null;
        $muhattaplar = [];
        if ($odemeTuru && $odemeTuru->muhattap) {
            $muhattaplar = array_map('strtoupper', array_map('trim', explode(',', $odemeTuru->muhattap)));
        }

        $rules = [
            'odeme_turu_id' => 'required|exists:kasa_odeme_turu,id',
            'odeme_sekli_id' => 'required|exists:kasa_odeme_sekli,id',
            'gerceklesme' => 'required|boolean',
            'tarih' => 'required|date_format:Y-m-d',
            'saat' => 'required|date_format:H:i',
            'islem_tarihi' => 'nullable|date_format:Y-m-d',
            'tutar' => 'required|numeric|min:0',
            'aciklama' => 'nullable|string|max:1000',
            'odeme_yonu' => [
                'required',
                'integer',
                function ($attribute, $value, $fail) use ($odemeTuru) {
                    Log::info('[VALIDASYON] Ödeme Türü Yon:', ['odemeTuruYon' => $odemeTuru->yon ?? 'null', 'inputValue' => $value, 'odemeTuruId' => $odemeTuru->id ?? 'null']);
                    if ($odemeTuru) {
                        // Eğer ödeme türünün yon değeri 0 ise (esnek), hem 1 hem de -1 kabul et
                        if ($odemeTuru->yon == 0 && ($value == 1 || $value == -1)) {
                            // Geçerli
                        } else if ($odemeTuru->yon != $value) {
                            $fail('Seçilen ödeme yönü, ödeme türü ile uyumlu değil.');
                        }
                    }
                },
            ],
        ];

        // Koşullu validasyonlar
        if (in_array('PERSONEL', $muhattaplar)) {
            $rules['personel_id'] = 'required|exists:personel,id'; // Formdan gelen personel seçimi için
        } else {
            $rules['personel_id'] = 'nullable|exists:personel,id'; 
        }

        if (in_array('SERVIS', $muhattaplar)) {
            $rules['servis_id'] = ['nullable', 'integer', function ($attribute, $value, $fail) use ($loggedInUser, $patronPozisyonId) {
                if ($value) { // Servis ID varsa kontrol et
                    // Patron dışındaki kullanıcılar için servis atama kontrolü
                    if ($loggedInUser && $loggedInUser->poz_id != $patronPozisyonId) {
                        $isServiceAssigned = \App\Models\Servis::where('id', $value)
                                                          ->where('personel_id', $loggedInUser->id)
                                                          ->exists();
                        if (!$isServiceAssigned) {
                            return $fail('İlgili servis kaydı size ait değil. Üzerinde işlem yapamazsınız');
                        }
                    } else {
                        // Patronlar için sadece servisin varlığını kontrol et
                        if (!\App\Models\Servis::where('id', $value)->exists()) {
                            return $fail('Seçilen servis ID geçersiz.');
                        }
                    }
                }
            }];
        } else {
            $rules['servis_id'] = 'nullable|exists:servisler,id'; // Servis muhatabı olmayanlarda normal kontrol
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            Log::warning('Yeni kasa hareketi validasyon hatası.', ['errors' => $validator->errors()->toArray()]);
            return response()->json(['success' => false, 'message' => 'Doğrulama hatası!', 'errors' => $validator->errors()], 422);
        }

        try {
            $validated = $validator->validated();
            $dataToCreate = $validated;
            unset($dataToCreate['personel_id'], $dataToCreate['ilgili_personel_id'], $dataToCreate['odeme_yonu']);

            // Kullanıcının seçtiği ödeme yönünü kaydet (validasyondan geçti)
            $dataToCreate['odeme_yonu'] = (int) ($validated['odeme_yonu'] ?? 0);
            
            if (empty($dataToCreate['islem_tarihi'])) {
                $dataToCreate['islem_tarihi'] = null;
            }

            $formPersonelId = $request->input('personel_id');

            if ($odemeTuru && $odemeTuru->muhattap) {
                $muhattaplarList = array_map('strtoupper', array_map('trim', explode(',', $odemeTuru->muhattap)));
                if (in_array('SERVIS', $muhattaplarList) && in_array('PERSONEL', $muhattaplarList)) {
                    $dataToCreate['ilgili_personel_id'] = $formPersonelId; // Formdaki personel -> ilgili_personel_id
                    $dataToCreate['personel_id'] = Auth::id(); // İşlemi yapan -> Auth user
                } else if (in_array('PERSONEL', $muhattaplarList)){
                    $dataToCreate['personel_id'] = $formPersonelId; // Formdaki personel -> ana personel_id
                    $dataToCreate['ilgili_personel_id'] = null;
                } else {
                    // PERSONEL muhatabı olmayan türler
                    $dataToCreate['personel_id'] = Auth::id(); // İşlemi yapan -> Auth user
                    $dataToCreate['ilgili_personel_id'] = null;
                }
            } else {
                $dataToCreate['personel_id'] = $formPersonelId ?? Auth::id(); // Formdan geleni kullan, yoksa Auth user
                $dataToCreate['ilgili_personel_id'] = null;
            }
            // Servis İşlemleri (id=5) için servis teknisyenini ilgili_personel_id olarak yaz
            if ((int)$selectedOdemeTuruId === 5) {
                $servisPersonelId = null;
                if (!empty($dataToCreate['servis_id'])) {
                    $servisPersonelId = Servis::where('id', $dataToCreate['servis_id'])->value('personel_id');
                }
                if ($servisPersonelId) {
                    $dataToCreate['ilgili_personel_id'] = $servisPersonelId;
                    $dataToCreate['personel_id'] = Auth::id();
                } elseif ($formPersonelId) {
                    $dataToCreate['ilgili_personel_id'] = $formPersonelId;
                    $dataToCreate['personel_id'] = Auth::id();
                }
            }

            if (!in_array('SERVIS', $muhattaplar) && !isset($dataToCreate['servis_id'])) {
                $dataToCreate['servis_id'] = null;
            }

            $kasaHareketi = Kasa::create($dataToCreate);
            Log::info('Yeni kasa hareketi başarıyla oluşturuldu.', ['kasa_id' => $kasaHareketi->id]);

            $kasaHareketi->load(['odemeTuru', 'odemeSekli', 'personel', 'ilgiliPersonel', 'servis']);

            $aciklama_gosterilecek = $this->formatKasaAciklama($kasaHareketi);

            return response()->json([
                'success' => true,
                'message' => 'Kasa hareketi başarıyla kaydedildi!',
                'kasaHareketi' => $kasaHareketi,
                'odemeTuruAdi' => $kasaHareketi->odemeTuru->ad ?? 'N/A', 
                'odemeSekliAdi' => $kasaHareketi->odemeSekli->ad ?? 'N/A',
                'aciklama_gosterilecek' => $aciklama_gosterilecek
            ]);

        } catch (\Exception $e) {
            Log::error('Yeni kasa hareketi kaydedilirken hata: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'Kasa hareketi kaydedilirken bir sunucu hatası oluştu.'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Kasa $kasa)
    {
        Log::info('KasaController@show (getDetay) çağrıldı.', ['kasa_id' => $kasa->id]);
        try {
            $loggedInUser = Auth::user();
            $allowedPozisyonIds = [1071, 1080]; // Patron, Muhasebe
            if (!$loggedInUser || !in_array((int) $loggedInUser->poz_id, $allowedPozisyonIds, true)) {
                Log::warning('Yetkisiz kasa detayı erişimi.', [
                    'user_id' => $loggedInUser->id ?? null,
                    'kasa_id' => $kasa->id,
                ]);
                return response()->json(['success' => false, 'message' => 'Bu kasa kaydına erişim yetkiniz yok.'], 403);
            }

            $kasa->load(['odemeTuru', 'odemeSekli', 'personel', 'ilgiliPersonel', 'servis']);
            Log::debug('KasaController@show - Kasa odeme_yonu:', ['kasa_odeme_yonu' => $kasa->odeme_yonu]);
            Log::debug('KasaController@show - Kasa odemeTuru->yon:', ['odemeTuru_yon' => $kasa->odemeTuru->yon ?? 'N/A']);

            $tumPersoneller = Personel::orderBy('ad')->get(['id', 'ad']);
            $teknisyenler = Personel::where('poz_id', 1077)->orderBy('ad')->get(['id', 'ad']);
            $tumOdemeTurleri = KasaOdemeTuru::orderBy('sira')->get(['id', 'ad', 'yon', 'muhattap']);
            $tumOdemeSekilleri = KasaOdemeSekli::orderBy('sira')->get(['id', 'ad']);

            return response()->json([
                'success' => true,
                'kasaHareketi' => $kasa,
                'personeller' => $tumPersoneller,
                'teknisyenler' => $teknisyenler,
                'odemeTurleri' => $tumOdemeTurleri,
                'odemeSekilleri' => $tumOdemeSekilleri,
            ]);
        } catch (\Exception $e) {
            Log::error('Kasa detayı çekilirken hata: ' . $e->getMessage(), ['kasa_id' => $kasa->id]);
            return response()->json(['success' => false, 'message' => 'Kasa hareket detayları alınamadı.'], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Kasa $kasa)
    {
        Log::info('KasaController@update çağrıldı.', ['kasa_id' => $kasa->id, 'data' => $this->sanitizeLogData($request->all())]);

        $loggedInUser = Auth::user();
        $allowedPozisyonIds = [1071, 1080]; // Patron, Muhasebe
        if (!$loggedInUser || !in_array((int) $loggedInUser->poz_id, $allowedPozisyonIds, true)) {
            Log::warning('Yetkisiz kasa kaydı güncelleme denemesi.', ['user_id' => $loggedInUser->id ?? null]);
            return response()->json(['success' => false, 'message' => 'Kasa kaydı güncelleme yetkiniz yok.'], 403);
        }

        // 'yon' parametresini 'odeme_yonu' olarak request'e ekle ki validasyon görebilsin.
        $request->merge(['odeme_yonu' => $request->input('yon')]);

        $selectedOdemeTuruId = $request->input('odeme_turu_id');
        $odemeTuru = $selectedOdemeTuruId ? KasaOdemeTuru::find($selectedOdemeTuruId) : null;
        $muhattaplar = [];
        if ($odemeTuru && $odemeTuru->muhattap) {
            $muhattaplar = array_map('strtoupper', array_map('trim', explode(',', $odemeTuru->muhattap)));
        }

        $rules = [
            'odeme_turu_id' => 'required|exists:kasa_odeme_turu,id',
            'odeme_sekli_id' => 'required|exists:kasa_odeme_sekli,id',
            'gerceklesme' => 'required|boolean',
            'tarih' => 'required|date_format:Y-m-d',
            'saat' => 'required|date_format:H:i',
            'islem_tarihi' => 'nullable|date_format:Y-m-d',
            'tutar' => 'required|numeric|min:0',
            'servis_id' => 'nullable|exists:servisler,id',
            'aciklama' => 'nullable|string|max:1000',
        ];
    
        if (in_array('PERSONEL', $muhattaplar)) {
            $rules['personel_id'] = 'required|exists:personel,id'; // Formdan gelen personel seçimi için
        } else {
             // $rules['personel_id'] = 'nullable|exists:personel,id'; // Opsiyonel, aşağıdaki mantıkla yönetilecek
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            Log::warning('Kasa hareketi güncelleme validasyon hatası.', ['kasa_id' => $kasa->id, 'errors' => $validator->errors()->toArray()]);
            return response()->json(['success' => false, 'message' => 'Doğrulama hatası!', 'errors' => $validator->errors()], 422);
        }

        try {
            $validated = $validator->validated();
            $dataToUpdate = $validated;
            unset($dataToUpdate['personel_id'], $dataToUpdate['ilgili_personel_id'], $dataToUpdate['odeme_yonu']);
            $formPersonelId = $request->input('personel_id');

            // odeme_yonu güncellenmez; mevcut kayıt değeri korunur

            if ($odemeTuru && $odemeTuru->muhattap) {
                $muhattaplarList = array_map('strtoupper', array_map('trim', explode(',', $odemeTuru->muhattap)));
                if (in_array('SERVIS', $muhattaplarList) && in_array('PERSONEL', $muhattaplarList)) {
                    $dataToUpdate['ilgili_personel_id'] = $formPersonelId;
                    $dataToUpdate['personel_id'] = $kasa->personel_id; // Mevcut işlemi yapanı koru
                } else if (in_array('PERSONEL', $muhattaplarList)) {
                    $dataToUpdate['personel_id'] = $formPersonelId;
                    $dataToUpdate['ilgili_personel_id'] = null;
                } else {
                    $dataToUpdate['personel_id'] = $kasa->personel_id; // Mevcut işlemi yapanı koru
                    $dataToUpdate['ilgili_personel_id'] = null;
                }
            } else {
                $dataToUpdate['personel_id'] = $formPersonelId ?? $kasa->personel_id;
                $dataToUpdate['ilgili_personel_id'] = null;
            }
            
            if (isset($dataToUpdate['islem_tarihi']) && $dataToUpdate['islem_tarihi'] === '') {
                $dataToUpdate['islem_tarihi'] = null;
            }
            if (isset($dataToUpdate['servis_id']) && $dataToUpdate['servis_id'] === '') {
                $dataToUpdate['servis_id'] = null;
            }

            // Servis İşlemleri (id=5) için servis teknisyenini ilgili_personel_id olarak yaz
            if ((int)$selectedOdemeTuruId === 5) {
                $servisId = $dataToUpdate['servis_id'] ?? $kasa->servis_id;
                if ($servisId) {
                    $servisPersonelId = Servis::where('id', $servisId)->value('personel_id');
                    if ($servisPersonelId) {
                        $dataToUpdate['ilgili_personel_id'] = $servisPersonelId;
                        $dataToUpdate['personel_id'] = $kasa->personel_id; // İşlemi yapan kalsın
                    }
                }
            }

            $kasa->update($dataToUpdate);
            Log::info('Kasa hareketi başarıyla güncellendi.', ['kasa_id' => $kasa->id, 'updated_data' => $dataToUpdate]);

            $kasa->load(['odemeTuru', 'odemeSekli', 'personel', 'ilgiliPersonel', 'servis']);
            
            $aciklama_gosterilecek = $this->formatKasaAciklama($kasa);

            return response()->json([
                'success' => true,
                'message' => 'Kasa hareketi başarıyla güncellendi!',
                'kasaHareketi' => $kasa,
                'odemeTuruAdi' => $kasa->odemeTuru->ad ?? 'N/A', 
                'odemeSekliAdi' => $kasa->odemeSekli->ad ?? 'N/A',
                'aciklama_gosterilecek' => $aciklama_gosterilecek
            ]);

        } catch (\Exception $e) {
            Log::error('Kasa hareketi güncellenirken hata: ' . $e->getMessage(), ['kasa_id' => $kasa->id, 'trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'Kasa hareketi güncellenirken bir sunucu hatası oluştu.'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Kasa $kasa)
    {
        Log::info('KasaController@destroy çağrıldı.', ['kasa_id' => $kasa->id]);
        try {
            $loggedInUser = Auth::user();
            $allowedPozisyonIds = [1071, 1080]; // Patron, Muhasebe
            if (!$loggedInUser || !in_array((int) $loggedInUser->poz_id, $allowedPozisyonIds, true)) {
                Log::warning('Yetkisiz kasa kaydı silme denemesi.', ['user_id' => $loggedInUser->id ?? null]);
                return response()->json(['success' => false, 'message' => 'Kasa kaydı silme yetkiniz yok.'], 403);
            }

            // Fiziksel silme yerine soft delete uygula
            $kasa->silindi = 1;
            $kasa->silen_kisi_id = Auth::id();
            $kasa->silinme_tarihi = Carbon::now();
            $kasa->save();

            Log::info('Kasa hareketi başarıyla SOFT DELETE ile silindi.', ['kasa_id' => $kasa->id]);
            return response()->json(['success' => true, 'message' => 'Kasa hareketi başarıyla silindi.']);
        } catch (\Exception $e) {
            Log::error('Kasa hareketi soft delete yapılırken hata: ' . $e->getMessage(), ['kasa_id' => $kasa->id]);
            return response()->json(['success' => false, 'message' => 'Kasa hareketi silinirken bir hata oluştu.'], 500);
        }
    }

    /**
     * Kasa modalı için gerekli form verilerini döndürür.
     */
    public function getKasaFormData()
    {
        Log::info('KasaController@getKasaFormData çağrıldı.');
        try {
            $loggedInUser = Auth::user();
            $allowedPozisyonIds = [1071, 1080]; // Patron, Muhasebe
            if (!$loggedInUser || !in_array((int) $loggedInUser->poz_id, $allowedPozisyonIds, true)) {
                Log::warning('Yetkisiz kasa form data denemesi.', ['user_id' => $loggedInUser->id ?? null]);
                return response()->json(['success' => false, 'message' => 'Kasa form verilerine erişim yetkiniz yok.'], 403);
            }

            $personeller = Personel::orderBy('ad')->get(['id', 'ad', 'poz_id']);
            $teknisyenler = Personel::where('poz_id', 1077)->orderBy('ad')->get(['id', 'ad']);
            $odemeTurleri = KasaOdemeTuru::orderBy('sira')->get(['id', 'ad', 'yon', 'muhattap']);
            $odemeSekilleri = KasaOdemeSekli::orderBy('sira')->get(['id', 'ad']);
            
            return response()->json([
                'success' => true,
                'personeller' => $personeller,
                'teknisyenler' => $teknisyenler,
                'odemeTurleri' => $odemeTurleri,
                'odemeSekilleri' => $odemeSekilleri
            ]);
        } catch (\Exception $e) {
            Log::error('Kasa formu için veri çekilirken hata: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Form için gerekli veriler yüklenemedi.'], 500);
        }
    }

    /**
     * Belirtilen kasa hareketini "silindi" olarak işaretler (soft delete).
     */
    public function softDelete(Kasa $kasa)
    {
        Log::info('Kasa soft delete çağrıldı.', ['kasa_id' => $kasa->id]);
        try {
            $loggedInUser = Auth::user();
            $allowedPozisyonIds = [1071, 1080]; // Patron, Muhasebe
            if (!$loggedInUser || !in_array((int) $loggedInUser->poz_id, $allowedPozisyonIds, true)) {
                Log::warning('Yetkisiz kasa soft delete denemesi.', ['user_id' => $loggedInUser->id ?? null]);
                return response()->json(['success' => false, 'message' => 'Kasa kaydı silme yetkiniz yok.'], 403);
            }

            $kasa->silindi = 1;
            $kasa->silen_kisi_id = Auth::id(); 
            $kasa->silinme_tarihi = Carbon::now(); 
            $kasa->save();

            Log::info('Kasa kaydı başarıyla silindi (soft delete) olarak işaretlendi.', ['kasa_id' => $kasa->id]);
            return response()->json(['success' => true, 'message' => 'Kasa kaydı başarıyla silindi.']);

        } catch (\Exception $e) {
            Log::error('Kasa soft delete edilirken hata: ' . $e->getMessage(), ['kasa_id' => $kasa->id, 'trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'Kasa kaydı silinirken bir sunucu hatası oluştu.'], 500);
        }
    }

    // Kasa açıklaması formatlama için yardımcı fonksiyon
    private function formatKasaAciklama(Kasa $kasa): string
    {
        $aciklama_gosterilecek = '';
        $muhattaplar = [];
        if ($kasa->odemeTuru && $kasa->odemeTuru->muhattap) {
            $muhattaplar = array_map('strtoupper', array_map('trim', explode(',', $kasa->odemeTuru->muhattap)));
        }
        
        $personelGoster = in_array('PERSONEL', $muhattaplar);
        $servisGoster = in_array('SERVIS', $muhattaplar);
        $gosterilecekPersonelModeli = null;

        if ($personelGoster) {
            if (in_array('SERVIS', $muhattaplar) && $kasa->ilgili_personel_id) {
                $gosterilecekPersonelModeli = $kasa->ilgiliPersonel; // İlişki zaten yüklenmiş olmalı
            } else {
                $gosterilecekPersonelModeli = $kasa->personel; // İlişki zaten yüklenmiş olmalı
            }
        }

        if ($gosterilecekPersonelModeli) {
            $aciklama_gosterilecek .= '<strong>Personel:</strong> ' . ($gosterilecekPersonelModeli->ad ?? 'N/A') . '<br>';
        }

        if ($servisGoster && $kasa->servis_id) {
            $aciklama_gosterilecek .= '<strong>Servis No:</strong> ' . $kasa->servis_id . '<br>';
        }

        if (in_array('ACIKLAMA', $muhattaplar) || (!$personelGoster && !$servisGoster)) {
             $aciklama_gosterilecek .= Str::limit($kasa->aciklama ?? '', 70);
        } elseif (empty(trim(strip_tags($aciklama_gosterilecek))) && empty($kasa->aciklama)) {
            // Eğer hem ilişkili bilgilerden gelen açıklama boşsa hem de ana açıklama boşsa tire koy
            $aciklama_gosterilecek = '-';
        } else if (empty(trim(strip_tags($aciklama_gosterilecek))) && !empty($kasa->aciklama)){
            // Eğer ilişkili bilgilerden gelen açıklama boşsa ama ana açıklama doluysa ana açıklamayı göster
            $aciklama_gosterilecek = Str::limit($kasa->aciklama ?? '', 70);
        }
        
        return trim(strip_tags($aciklama_gosterilecek, '<strong><br>')) ?: '-'; // HTML taglarını koru, boşsa tire koy
    }

    /**
     * Seçilen kasa hareketlerinin gerçekleşme tarihlerini toplu olarak günceller.
     */
    public function bulkUpdateGerceklesmeTarihi(Request $request)
    {
        Log::debug('bulkUpdateGerceklesmeTarihi metodu çağrıldı.', ['request_data' => $this->sanitizeLogData($request->all())]);
        // Sadece Patron yetkisine sahip kullanıcıların erişimini kontrol et
        $loggedInUser = Auth::user();
        $patronPozisyonId = 1071; // Patron pozisyon ID'si

        if (!$loggedInUser || $loggedInUser->poz_id !== $patronPozisyonId) {
            Log::warning('Yetkisiz toplu güncelleme denemesi.', ['user_id' => $loggedInUser ? $loggedInUser->id : 'N/A']);
            return response()->json(['success' => false, 'message' => 'Bu işlemi gerçekleştirmek için yetkiniz yok.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'hareket_ids' => 'required|array',
            'hareket_ids.*' => 'integer|exists:kasa,id',
            'gerceklesme_tarihi' => 'required|date_format:Y-m-d',
        ]);

        if ($validator->fails()) {
            Log::warning('Toplu gerçekleşme tarihi güncelleme validasyon hatası.', ['errors' => $validator->errors()->toArray()]);
            return response()->json(['success' => false, 'message' => 'Doğrulama hatası!', 'errors' => $validator->errors()], 422);
        }

        try {
            $hareketIds = $request->input('hareket_ids');
            $gerceklesmeTarihi = $request->input('gerceklesme_tarihi');
            Log::debug('Toplu güncelleme için gelen veriler.', ['hareket_ids' => $hareketIds, 'gerceklesme_tarihi' => $gerceklesmeTarihi]);

            $affectedRows = Kasa::whereIn('id', $hareketIds)->update(['islem_tarihi' => $gerceklesmeTarihi]);
            Log::info('Toplu gerçekleşme tarihi başarıyla güncellendi.', ['hareket_ids' => $hareketIds, 'yeni_tarih' => $gerceklesmeTarihi, 'etkilenen_satir_sayisi' => $affectedRows]);
            return response()->json(['success' => true, 'message' => $affectedRows . ' adet kasa kaydının gerçekleşme tarihi başarıyla güncellendi.']);

        } catch (\Exception $e) {
            Log::error('Toplu gerçekleşme tarihi güncellenirken hata: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'Toplu gerçekleşme tarihi güncellenirken bir sunucu hatası oluştu.'], 500);
        }
    }
}
