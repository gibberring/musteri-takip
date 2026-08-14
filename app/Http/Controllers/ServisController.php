<?php

namespace App\Http\Controllers;

use App\Models\Servis;
use App\Models\ServisDurum;
use App\Models\Islemloglari;
use App\Models\KasaOdemeSekli; // Yeni model eklendi
use App\Models\ServisDurumSoru;
use App\Models\Personel;
use App\Models\ServisDurumCevap;
use App\Models\ServisDurumCevap0;
use App\Models\Marka;
use App\Models\CihazTuru;
use App\Models\TnmMarka;
use App\Models\TnmCihazTuru;
use App\Models\Musteri;
use App\Models\Il;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Models\ServisFisi;
use App\Models\Kasa; // Kasa modeli eklendi
use App\Models\TnmPersonelPozisyon; // Eklendi
use App\Models\RoleAbility;
use App\Models\ServisResmi; // Resim modeli eklendi
use App\Models\Announcement;
use App\Models\SettingsAuditLog;
use App\Services\TeknisyenYonlendirmeBildirimService;

class ServisController extends Controller
{
    private function sanitizeLogData(array $data): array
    {
        $sensitiveKeys = [
            'tel1', 'tel2', 'adres', 'musteri_tel1', 'musteri_tel2',
            'musteri_adres', 'musteri_vno', 'musteri_vdaire', 'vno', 'vdaire',
            'musteri_imza_data', 'teknisyen_imza_data'
        ];
        foreach ($sensitiveKeys as $key) {
            if (array_key_exists($key, $data) && $data[$key] !== null) {
                $data[$key] = '[REDACTED]';
            }
        }
        return $data;
    }
    private function ensureServisAccess(Servis $servis)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Giriş gerekli.'], 401);
        }

        $patronPozisyonId = 1071;
        $muhasebePozisyonId = 1080;
        $operatorPozisyonId = 1073;
        $hariciOperatorPozisyonId = 1076;
        $teknisyenPozisyonId = 1077;

        // Patron / Muhasebe / Operatör: kısıtsız (ileri tarihli servis dahil)
        if (in_array((int) $user->poz_id, [$patronPozisyonId, $muhasebePozisyonId, $operatorPozisyonId], true)) {
            return null;
        }

        if (in_array((int) $user->poz_id, [$teknisyenPozisyonId, $hariciOperatorPozisyonId], true)) {
            if ((int) $servis->personel_id !== (int) $user->id) {
                return response()->json(['success' => false, 'message' => 'Bu servise erişim yetkiniz yok.'], 403);
            }
            // Yalnızca Teknisyen: gidiş tarihi (servisler.tarih) henüz gelmemişse detay/modal açamasın
            if ((int) $user->poz_id === $teknisyenPozisyonId && $servis->tarih) {
                $today = Carbon::today('Europe/Istanbul')->toDateString();
                $gidis = Carbon::parse($servis->tarih)->toDateString();
                if ($gidis > $today) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Bu servisin gidiş tarihi henüz gelmedi.',
                    ], 403);
                }
            }
            return null;
        }

        return response()->json(['success' => false, 'message' => 'Bu işlem için yetkiniz bulunmamaktadır.'], 403);
    }

    private function enrichIslemLoglari(array &$responseData): void
    {
        if (!empty($responseData['islemloglari']) && is_array($responseData['islemloglari'])) {
            foreach ($responseData['islemloglari'] as &$logItem) {
                $isSystem = empty($logItem['personel']) && empty($logItem['islemi_yapan_personel_id']);
                $logItem['is_system'] = $isSystem;
                if (!$logItem['servis_durum_id'] && ($logItem['aciklama'] ?? '') === 'Hatırlatma') {
                    $logItem['islem_adi'] = 'Ertelenen Servis';
                }
            }
            unset($logItem);
        }
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        Log::info('ServisController@index çağrıldı.');
        try {
            $loggedInUser = Auth::user();
            $tsrnTeknisyenPozisyonId = 1077;
            $hariciOperatorPozisyonId = 1076;
            $operatorPozisyonId = 1073;
            $muhasebePozisyonId = 1080;
            $patronPozisyonId = 1071;
            $teknisyenYonlendirildiStatusId = 9098; // Teknisyen Yönlendirildi durumu ID'si
            $forceOwnServis = false;
            if ($loggedInUser) {
                $pozId = (int) $loggedInUser->poz_id;
                // Patron / Muhasebe: kısıtsız (ileri tarihli 9098 dahil); canViewOwnServis override bile uygulanmaz
                if (!in_array($pozId, [$patronPozisyonId, $muhasebePozisyonId], true)) {
                    if ($pozId === $tsrnTeknisyenPozisyonId) {
                        $forceOwnServis = true;
                    } else {
                        $ownOverride = RoleAbility::query()
                            ->where('role_id', $pozId)
                            ->where('ability', 'canViewOwnServis')
                            ->value('allowed');
                        if ((int) $ownOverride === 1) {
                            $forceOwnServis = true;
                        }
                    }
                }
            }

            $searchValue = trim((string) $request->input('search.value', ''));
            if (
                $loggedInUser
                && (int) $loggedInUser->poz_id === $hariciOperatorPozisyonId
                && $searchValue !== ''
                && mb_strlen($searchValue) < 7
            ) {
                $searchValue = '';
            }
            // Teknisyen rütbesi genel arama yapamaz
            if ($loggedInUser && (int) $loggedInUser->poz_id === $tsrnTeknisyenPozisyonId) {
                $searchValue = '';
            }
            $pendingOnly = (int) $request->input('pending_only', 0) === 1;
            if ($pendingOnly) {
                if (!$loggedInUser || !in_array((int) $loggedInUser->poz_id, [$patronPozisyonId, $muhasebePozisyonId, $operatorPozisyonId], true)) {
                    abort(403, 'Bu sayfaya erişim yetkiniz bulunmamaktadır.');
                }
            }
            $skipOwnServisFilterForSearch = $loggedInUser
                && (int) $loggedInUser->poz_id === $hariciOperatorPozisyonId
                && $searchValue !== '';

            // Temel sorgu (ilişkiler ve silinmemişler)
            $baseQuery = Servis::with([
                'musteri' => function ($q) {
                    $q->with(['il', 'ilce']);
                },
                'marka', 
                'cihazTuru', 
                'servisDurum'
            ])
                ->where(function ($q) {
                    $q->where('silindi', '!=', 1)
                        ->orWhereNull('silindi');
                });

            if ($pendingOnly) {
                $baseQuery->whereIn('servisler.servis_durum_id', [9097, 9334]);
            }

            // Eğer kullanıcı sadece kendi servislerini görebiliyorsa filtrele
            if ($loggedInUser && $forceOwnServis && !$skipOwnServisFilterForSearch) {
                // Sadece "güncel" teknisyen yönlendirmesi (aynı mantık, korelasyon yok: önce servis
                // bazında MAX(cevap.id), sonra JOIN ile eşle — count() ile sunucu şişmez.
                $baseQuery->where('servisler.servis_durum_id', $teknisyenYonlendirildiStatusId)
                    ->whereIn('servisler.id', function ($q) use ($loggedInUser, $teknisyenYonlendirildiStatusId) {
                        $latestPerServis = DB::table('servisdurum_cevaplari as c2')
                            ->join('servisdurum_cevap0 as c0b', 'c2.durumCevap0_id', '=', 'c0b.id')
                            ->where('c0b.servis_durum_id', $teknisyenYonlendirildiStatusId)
                            ->where('c2.soru_id', 13234)
                            ->groupBy('c0b.servis_id')
                            ->selectRaw('c0b.servis_id as agg_sid, MAX(c2.id) as agg_max_id');

                        $q->from('servisdurum_cevaplari as c')
                            ->join('servisdurum_cevap0 as c0', 'c.durumCevap0_id', '=', 'c0.id')
                            ->joinSub($latestPerServis, 'agg', function ($join) {
                                $join->on('agg.agg_sid', '=', 'c0.servis_id')
                                    ->on('agg.agg_max_id', '=', 'c.id');
                            })
                            ->where('c.soru_id', 13234)
                            ->where('c.cevap', (string) $loggedInUser->id)
                            ->where('c0.servis_durum_id', $teknisyenYonlendirildiStatusId)
                            ->select('c0.servis_id');
                    });

                // İleri tarihli (gidiş > bugün) kayıtlar yalnızca teknisyene (1077) gizlensin; null tarih gösterilir.
                // Patron / Muhasebe forceOwnServis almaz; yine de tarihi pozisyonla kilitle (override karışmasın).
                if ((int) $loggedInUser->poz_id === $tsrnTeknisyenPozisyonId) {
                    $todayIstanbul = Carbon::today('Europe/Istanbul')->toDateString();
                    $baseQuery->where(function ($q) use ($todayIstanbul) {
                        $q->whereNull('servisler.tarih')
                            ->orWhereDate('servisler.tarih', '<=', $todayIstanbul);
                    });
                }
            }

            if ($request->ajax() || $request->expectsJson()) {
                $ajaxQuery = clone $baseQuery; // AJAX için temel sorguyu klonla

                // Bölgeye özgü filtre (il_id gönderilmişse)
                if ($request->filled('il_id') && $request->il_id !== '') {
                    $ajaxQuery->whereHas('musteri', function ($q) use ($request) {
                        $q->where('il_id', $request->il_id);
                    });
                }

                // Marka filtresi
                if ($request->filled('marka_id') && $request->marka_id !== '') {
                    $ajaxQuery->where('servisler.marka_id', $request->marka_id);
                }

                // Cihaz türü filtresi
                if ($request->filled('cihaz_tur_id') && $request->cihaz_tur_id !== '') {
                    $ajaxQuery->where('servisler.cihaz_tur_id', $request->cihaz_tur_id);
                }

                // Personel filtresi (operatör/teknisyen ayrımı)
                if ($request->filled('personel_id') && $request->personel_id !== '') {
                    $filterType = (string) $request->input('personel_filter_type', '');

                    if ($filterType === 'operator') {
                        // Operatör filtresi: servisi oluşturan kişi
                        if (!($loggedInUser && $forceOwnServis && $request->personel_id != $loggedInUser->id)) {
                            $ajaxQuery->where('servisler.olusturan_personel_id', $request->personel_id);
                        }
                    } else {
                        // Teknisyen (veya varsayılan) filtresi: mevcut servis personeli
                        if (!($loggedInUser && $forceOwnServis && $request->personel_id != $loggedInUser->id)) {
                            $ajaxQuery->where('servisler.personel_id', $request->personel_id);
                        }
                    }
                }

                // Servis durumu filtresi
                if ($request->filled('servis_durum_id') && $request->servis_durum_id !== '' && $request->servis_durum_id !== '0') {
                    $ajaxQuery->where('servisler.servis_durum_id', $request->servis_durum_id);
                }

                // Tarih filtreleri
                // Teknisyen filtresi / teknisyen kendi listesi: gidiş tarihi (servisler.tarih)
                // Diğer filtreler: kayıt tarihi (created_at)
                if ($request->filled('baslangic_tarih') || $request->filled('bitis_tarih')) {
                    $filterType = (string) $request->input('personel_filter_type', '');
                    $useGidisTarihi = ($filterType === 'teknisyen')
                        || ($loggedInUser && $forceOwnServis && !$skipOwnServisFilterForSearch);
                    $dateColumn = $useGidisTarihi ? 'servisler.tarih' : 'servisler.created_at';

                    if ($request->filled('baslangic_tarih')) {
                        $ajaxQuery->whereDate($dateColumn, '>=', $request->baslangic_tarih);
                    }
                    if ($request->filled('bitis_tarih')) {
                        $ajaxQuery->whereDate($dateColumn, '<=', $request->bitis_tarih);
                    }
                }
                
                $draw = (int) $request->input('draw');
                $start = (int) $request->input('start', 0);
                $length = (int) $request->input('length', 20);

                $recordsTotal = (clone $ajaxQuery)->count();

                $searchValue = trim((string) $request->input('search.value', ''));
                if (
                    $loggedInUser
                    && (int) $loggedInUser->poz_id === $hariciOperatorPozisyonId
                    && $searchValue !== ''
                    && mb_strlen($searchValue) < 7
                ) {
                    return response()->json([
                        'draw' => $draw,
                        'recordsTotal' => 0,
                        'recordsFiltered' => 0,
                        'data' => [],
                    ]);
                }
                if ($searchValue !== '') {
                    $numericSearch = preg_replace('/\D+/', '', $searchValue);
                    $ajaxQuery->where(function ($q) use ($searchValue, $numericSearch) {
                        $like = '%' . $searchValue . '%';

                        $q->where('servisler.id', 'like', $like)
                          ->orWhere('servisler.created_at', 'like', $like)
                          ->orWhere('servisler.cihaz_arizasi', 'like', $like)
                          ->orWhere('servisler.seri_no', 'like', $like)
                          ->orWhereHas('musteri', function ($mq) use ($like, $numericSearch) {
                              $mq->where('ad', 'like', $like)
                                 ->orWhere('tel1', 'like', $like)
                                 ->orWhere('tel2', 'like', $like)
                                 ->orWhere('adres', 'like', $like)
                                 ->orWhereHas('il', function ($iq) use ($like) {
                                     $iq->where('ad', 'like', $like);
                                 })
                                 ->orWhereHas('ilce', function ($iq) use ($like) {
                                     $iq->where('ad', 'like', $like);
                                 });

                              if ($numericSearch) {
                                  $phoneLike = '%' . $numericSearch . '%';
                                  $normalizeTel = function ($column) {
                                      return "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE($column,' ',''),'-',''),'/',''),'(',''),')','')";
                                  };
                                  $mq->orWhereRaw($normalizeTel('tel1') . ' LIKE ?', [$phoneLike])
                                     ->orWhereRaw($normalizeTel('tel2') . ' LIKE ?', [$phoneLike]);
                              }
                          })
                          ->orWhereHas('marka', function ($mq) use ($like) {
                              $mq->where('ad', 'like', $like);
                          })
                          ->orWhereHas('cihazTuru', function ($mq) use ($like) {
                              $mq->where('ad', 'like', $like);
                          })
                          ->orWhereHas('servisDurum', function ($mq) use ($like) {
                              $mq->where('ad', 'like', $like);
                          });
                    });
                }

                $recordsFiltered = (clone $ajaxQuery)->count();

                $orderColumn = $request->input('order.0.column');
                $orderDirInput = $request->input('order.0.dir', 'desc');
                $orderDir = in_array($orderDirInput, ['asc', 'desc'], true) ? $orderDirInput : 'desc';
                $orderColumnMap = [
                    0 => 'servisler.id',
                    1 => 'servisler.created_at',
                ];
                if (isset($orderColumnMap[$orderColumn])) {
                    $ajaxQuery->orderBy($orderColumnMap[$orderColumn], $orderDir);
                } else {
                    $ajaxQuery->latest('servisler.created_at');
                }

                if ($length > 0) {
                    $ajaxQuery->skip($start)->take($length);
                }

                $servislerListesi = $ajaxQuery->get();

                Log::info('ServisController@index AJAX isteği için filtreli veri çekildi.', [
                    'il_id' => $request->input('il_id'),
                    'marka_id' => $request->input('marka_id'),
                    'cihaz_tur_id' => $request->input('cihaz_tur_id'),
                    'personel_id' => $request->input('personel_id'),
                    'servis_durum_id' => $request->input('servis_durum_id'),
                    'baslangic_tarih' => $request->input('baslangic_tarih'),
                    'bitis_tarih' => $request->input('bitis_tarih'),
                    'sonuc_sayisi' => $servislerListesi->count(),
                    'records_total' => $recordsTotal,
                    'records_filtered' => $recordsFiltered
                ]);
                // AJAX istekleri için de atanan personel adını ekle
                foreach ($servislerListesi as $servis) {
                    $assignedPersonnel = null;
                    $assignedPersonnelId = null;
                    $servisDurumCevap = ServisDurumCevap::where('soru_id', 13234)
                        ->whereHas('durumCevap0', function ($q) use ($servis) {
                            $q->where('servis_id', $servis->id)
                              ->where('servis_durum_id', 9098);
                        })
                        ->orderByDesc('id')
                        ->first();

                    if ($servisDurumCevap && $servisDurumCevap->cevap) {
                        $personelId = (int) $servisDurumCevap->cevap;
                        $personel = Personel::find($personelId);
                        if ($personel) {
                            $assignedPersonnel = $personel->ad;
                            $assignedPersonnelId = $personelId;
                            Log::debug('AJAX için atanan personel bulundu.', ['servis_id' => $servis->id, 'personel_ad' => $assignedPersonnel]);
                        } else {
                            Log::debug('AJAX için personel ID bulundu ama personel bulunamadı.', ['servis_id' => $servis->id, 'personel_id' => $personelId]);
                        }
                    } else {
                        Log::debug('AJAX için servis durum cevap veya cevap bulunamadı.', ['servis_id' => $servis->id, 'servisDurumCevap' => $servisDurumCevap]);
                    }
                    $servis->assignedPersonnelName = $assignedPersonnel ?? 'Belirlenmedi';
                    $servis->assignedPersonnelId = $assignedPersonnelId;
                    $lastLog = Islemloglari::where('servis_id', $servis->id)
                        ->where(function ($q) {
                            $q->where('silindi', '!=', 1)
                              ->orWhereNull('silindi');
                        })
                        ->orderByDesc('id')
                        ->first(['aciklama']);
                    $servis->lastLogAciklama = $lastLog ? $lastLog->aciklama : null;
                    Log::debug('AJAX için son assignedPersonnelName.', ['servis_id' => $servis->id, 'assignedPersonnelName' => $servis->assignedPersonnelName]);
                }
                return response()->json([
                    'draw' => $draw,
                    'recordsTotal' => $recordsTotal,
                    'recordsFiltered' => $recordsFiltered,
                    'data' => $servislerListesi,
                ]);
            }
        
            // AJAX olmayan istek (ilk sayfa yüklemesi) için.
            $servisler = $baseQuery->latest('servisler.created_at')->paginate(15);  // latest() için de tablo adı
            
            // Her servis kaydı için atanan personeli dinamik olarak belirle
            foreach ($servisler as $servis) {
                $assignedPersonnel = null;
                $assignedPersonnelId = null;
                
                // ServisDurumCevap tablosunda, ilgili servise ait ve soru_id'si 13234 olan cevabı bul
                // Bu cevap, personel ID'sini string olarak tutuyor.
                $servisDurumCevap = ServisDurumCevap::where('soru_id', 13234)
                    ->whereHas('durumCevap0', function ($q) use ($servis) {
                        $q->where('servis_id', $servis->id)
                          ->where('servis_durum_id', 9098);
                    })
                    ->orderByDesc('id')
                    ->first();

                if ($servisDurumCevap && $servisDurumCevap->cevap) {
                    $personelId = (int) $servisDurumCevap->cevap; // Cevap string olarak geldiği için int'e çevir
                    $personel = Personel::find($personelId);
                    if ($personel) {
                        $assignedPersonnel = $personel->ad;
                        $assignedPersonnelId = $personelId;
                        Log::debug('Normal yükleme için atanan personel bulundu.', ['servis_id' => $servis->id, 'personel_ad' => $assignedPersonnel]);
                    } else {
                        Log::debug('Normal yükleme için personel ID bulundu ama personel bulunamadı.', ['servis_id' => $servis->id, 'personel_id' => $personelId]);
                    }
                } else {
                    Log::debug('Normal yükleme için servis durum cevap veya cevap bulunamadı.', ['servis_id' => $servis->id, 'servisDurumCevap' => $servisDurumCevap]);
                }
                
                $servis->assignedPersonnelName = $assignedPersonnel ?? 'Belirlenmedi';
                $servis->assignedPersonnelId = $assignedPersonnelId;
                $lastLog = Islemloglari::where('servis_id', $servis->id)
                    ->where(function ($q) {
                        $q->where('silindi', '!=', 1)
                          ->orWhereNull('silindi');
                    })
                    ->orderByDesc('id')
                    ->first(['aciklama']);
                $servis->lastLogAciklama = $lastLog ? $lastLog->aciklama : null;
                Log::debug('Normal yükleme için son assignedPersonnelName.', ['servis_id' => $servis->id, 'assignedPersonnelName' => $servis->assignedPersonnelName]);
            }
            // --- Modal için gerekli veriler --- 
            $servisDurumlar = ServisDurum::gorunur()->orderBy('sira')->get(['id', 'ad']);
            $servisDurumSorular = ServisDurumSoru::orderBy('sira')->get(); 
            $personeller = Personel::where('aktif', 1)->orderBy('ad')->get(['id', 'ad', 'poz_id']);
            $markalar = TnmMarka::orderBy('ad')->get(['id', 'ad']); // Sadece id ve ad
            $cihazTurleri = TnmCihazTuru::orderBy('ad')->get(['id', 'ad']); // Sadece id ve ad
            // İller: aktif bölgelere göre filtrele (varsa)
            $aktifIlCount = \Illuminate\Support\Facades\DB::table('aktif_iller')->count();
            if ($aktifIlCount > 0) {
                $aktifIlIds = \Illuminate\Support\Facades\DB::table('aktif_iller')->pluck('il_id');
                $iller = Il::whereIn('id', $aktifIlIds)->orderBy('ad')->get(['id', 'ad']);
            } else {
                $iller = Il::orderBy('ad')->get(['id', 'ad']);
            }

            // Filtrelenmiş personel listeleri (Operatör Servisleri dropdown: operatör + harici operatör)
            $operatorPozisyonId = 1073; // Operatör pozisyon ID'si
            $hariciOperatorPozisyonId = 1076; // Harici operatör (İdari İşler) pozisyon ID'si
            $teknisyenPozisyonId = 1077; // Teknisyen pozisyon ID'si
            
            $operatorPersonelleri = Personel::whereIn('poz_id', [$operatorPozisyonId, $hariciOperatorPozisyonId])->where('aktif', 1)->orderBy('ad')->get(['id', 'ad']);
            $teknisyenPersonelleri = Personel::where('poz_id', $teknisyenPozisyonId)->where('aktif', 1)->orderBy('ad')->get(['id', 'ad']);
            
            Log::info('Servisler ve modal verileri çekildi (normal yükleme).');

            $hideBolgeServisleri = $loggedInUser && (int) $loggedInUser->poz_id === $teknisyenPozisyonId;

        // View'a veriyi gönder
            return view('crm.proposal', compact(
                'servisler', 
                'servisDurumlar', 
                'servisDurumSorular', 
                'personeller', 
                'markalar', 
                'cihazTurleri',
                'iller', // İller view'a gönderildi
                'operatorPersonelleri', // Operatör personelleri
                'teknisyenPersonelleri', // Teknisyen personelleri
                'pendingOnly',
                'hideBolgeServisleri'
            ));
        } catch (\Exception $e) {
             Log::error('Servis listesi veya modal verileri çekilirken hata: ' . $e->getMessage());
             
            if ($request->ajax()) {
                return response()->json(['error' => 'Veriler yüklenirken bir sorun oluştu.'], 500);
            }

             // Hata durumunda boş listelerle veya hata mesajıyla view'ı döndür
             return view('crm.proposal', [
                'servisler' => collect(),
                'servisDurumlar' => collect(),
                'servisDurumSorular' => collect(),
                'personeller' => collect(),
                'markalar' => collect(),
                'cihazTurleri' => collect(),
                'iller' => collect(), // Hata durumunda boş koleksiyon
                'operatorPersonelleri' => collect(), // Hata durumunda boş koleksiyon
                'teknisyenPersonelleri' => collect(), // Hata durumunda boş koleksiyon
                'hideBolgeServisleri' => false
             ])->withErrors('Veriler yüklenirken bir sorun oluştu.');
        }
    }

    public function operatorComparison(Request $request)
    {
        $loggedInUser = Auth::user();
        $allowedPozisyonIds = [1071]; // Patron
        if (!$loggedInUser || !in_array((int) $loggedInUser->poz_id, $allowedPozisyonIds, true)) {
            abort(403, 'Bu sayfaya erişim yetkiniz bulunmamaktadır.');
        }

        $operatorPozisyonId = 1073;
        $hariciOperatorPozisyonId = 1076;
        $operators = Personel::whereIn('poz_id', [$operatorPozisyonId, $hariciOperatorPozisyonId])
            ->where('aktif', 1)
            ->orderBy('ad')
            ->get(['id', 'ad']);

        return view('crm.servis.operator-karsilastirma', [
            'operators' => $operators,
        ]);
    }

    public function operatorComparisonData(Request $request)
    {
        $loggedInUser = Auth::user();
        $allowedPozisyonIds = [1071]; // Patron
        if (!$loggedInUser || !in_array((int) $loggedInUser->poz_id, $allowedPozisyonIds, true)) {
            return response()->json(['success' => false, 'message' => 'Bu sayfaya erişim yetkiniz bulunmamaktadır.'], 403);
        }

        $dateFrom = $request->input('baslangic_tarih');
        $dateTo = $request->input('bitis_tarih');

        $operatorPozisyonId = 1073;
        $hariciOperatorPozisyonId = 1076;
        $operators = Personel::whereIn('poz_id', [$operatorPozisyonId, $hariciOperatorPozisyonId])
            ->where('aktif', 1)
            ->orderBy('ad')
            ->get(['id', 'ad']);

        $countsQuery = Servis::query()
            ->select('olusturan_personel_id', DB::raw('COUNT(*) as toplam'))
            ->whereNotNull('olusturan_personel_id')
            ->where(function ($q) {
                $q->where('silindi', '!=', 1)
                  ->orWhereNull('silindi');
            });

        if ($dateFrom) {
            $countsQuery->whereDate('servisler.created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $countsQuery->whereDate('servisler.created_at', '<=', $dateTo);
        }

        $counts = $countsQuery
            ->groupBy('olusturan_personel_id')
            ->pluck('toplam', 'olusturan_personel_id');

        $rows = $operators->map(function ($operator) use ($counts) {
            $count = (int) ($counts[$operator->id] ?? 0);
            return [
                'id' => $operator->id,
                'ad' => $operator->ad,
                'count' => $count,
            ];
        })->sortByDesc('count')->values();

        $totalCount = (int) $rows->sum('count');

        $html = view('crm.servis._operator_karsilastirma_table', [
            'rows' => $rows,
            'totalCount' => $totalCount,
        ])->render();

        return response()->json([
            'success' => true,
            'html' => $html,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
        ]);
    }

    public function pendingIndex(Request $request)
    {
        $request->merge(['pending_only' => 1]);
        return $this->index($request);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Yeni servis oluşturma formunu gösterme
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $loggedInUser = Auth::user();
        $tsrnTeknisyenPozisyonId = 1077; // TŞRN Teknisyen pozisyon ID'si

        // Eğer kullanıcı TŞRN Teknisyen ise, yeni servis kaydetme erişimini engelle
        if ($loggedInUser && $loggedInUser->poz_id == $tsrnTeknisyenPozisyonId) {
            Log::warning('TŞRN Teknisyenin yeni servis kaydetme denemesi engellendi.', ['user_id' => $loggedInUser->id]);
            return response()->json([
                'success' => false,
                'message' => 'Yeni servis kaydı oluşturma yetkiniz bulunmamaktadır.'
            ], 403); // 403 Forbidden
        }

        Log::info('ServisController@store çağrıldı.', ['request_data' => $this->sanitizeLogData($request->all())]);
        
        $musteriModu = $request->input('musteri_secim_modu', 'varolan'); // 'varolan' veya 'yeni'
        Log::info('Müşteri Modu:', ['mod' => $musteriModu]);

        // --- Genel Servis Alanları Validasyonu ---
        $servisValidator = Validator::make($request->all(), [
            'marka_id' => 'required|exists:tnm_markalar,id',
            'cihaz_tur_id' => 'required|exists:tnm_cihazturleri,id',
            'cihaz_model' => 'nullable|string|max:255',
            'seri_no' => 'nullable|string|max:255',
            'cihaz_arizasi' => 'required|string',
            'aciklama' => 'nullable|string',
        ]);

        if ($servisValidator->fails()) {
            Log::warning('Yeni servis - Servis validasyon hatası.', ['errors' => $servisValidator->errors()->toArray()]);
            return response()->json(['success' => false, 'errors' => $servisValidator->errors()], 422);
        }

        // --- Müşteri Validasyonu (Moda Göre) ---
        $musteriData = [];
        $yeniMusteriId = null;

        if ($musteriModu === 'yeni') {
            $yeniMusteriValidator = Validator::make($request->all(), [
                 // Yeni müşteri alanlarının name'leri: yeni_... şeklinde
                'yeni_musteri_tip' => 'required|in:0,1',
                'yeni_kayit_tarihi' => 'required|date_format:Y-m-d',
                'yeni_kayit_saati' => 'required|date_format:H:i',
                'yeni_ad' => 'required|string|max:255',
                'yeni_tel1' => 'required|string|max:20',
                'yeni_tel2' => 'nullable|string|max:20',
                'yeni_il_id' => 'required|exists:iller,id',
                'yeni_ilce_id' => 'required|exists:ilceler,id',
                'yeni_adres' => 'required|string|max:500',
                'yeni_vdaire' => 'required_if:yeni_musteri_tip,1|nullable|string|max:255',
                'yeni_vno' => 'required_if:yeni_musteri_tip,1|nullable|string|max:255',
            ]);

            if ($yeniMusteriValidator->fails()) {
                Log::warning('Yeni servis - Yeni müşteri validasyon hatası.', ['errors' => $yeniMusteriValidator->errors()->toArray()]);
                // Hata anahtarlarını JS'in anlayacağı hale getir (örn: yeni_ad -> ad)
                $formattedErrors = [];
                foreach ($yeniMusteriValidator->errors()->toArray() as $key => $messages) {
                    $formattedErrors[str_replace('yeni_', '', $key)] = $messages;
                }
                 return response()->json(['success' => false, 'errors' => $formattedErrors], 422);
            }
            $musteriData = $yeniMusteriValidator->validated(); // Sadece yeni müşteri için valide edilen data
            Log::info('Yeni Müşteri için valide edilen data:', $musteriData);

        } else { // Varolan Müşteri
            $varolanMusteriValidator = Validator::make($request->all(), [
                'musteri_id' => 'required|exists:musteriler,id'
            ]);
            if ($varolanMusteriValidator->fails()) {
                 Log::warning('Yeni servis - Varolan müşteri validasyon hatası.', ['errors' => $varolanMusteriValidator->errors()->toArray()]);
                return response()->json(['success' => false, 'errors' => $varolanMusteriValidator->errors()], 422);
            }
            $yeniMusteriId = $request->input('musteri_id'); // Varolan ID'yi kullan
        }

        // Başlangıç durumunu belirle (Örn: ID'si 9097 olan durum)
        $defaultDurumId = 9097; // Veya config dosyasından alınabilir
        $baslangicDurum = ServisDurum::find($defaultDurumId);
        if (!$baslangicDurum) {
             Log::error('Başlangıç servis durumu bulunamadı.', ['id' => $defaultDurumId]);
             return response()->json(['success' => false, 'message' => 'Sistem ayarlarında hata. Başlangıç durumu tanımlı değil.'], 500);
        }

        try {
            DB::beginTransaction();

            // Eğer yeni müşteri ise, önce onu oluştur
            if ($musteriModu === 'yeni') {
                $yeniMusteriData = [
                    'musteri_tip' => $musteriData['yeni_musteri_tip'],
                    'tarih' => $musteriData['yeni_kayit_tarihi'],
                    'saat' => $musteriData['yeni_kayit_saati'],
                    'ad' => $musteriData['yeni_ad'],
                    'tel1' => $musteriData['yeni_tel1'],
                    'tel2' => $musteriData['yeni_tel2'],
                    'il_id' => $musteriData['yeni_il_id'],
                    'ilce_id' => $musteriData['yeni_ilce_id'],
                    'adres' => $musteriData['yeni_adres'],
                    'vdaire' => $musteriData['yeni_musteri_tip'] == '1' ? $musteriData['yeni_vdaire'] : null,
                    'vno' => $musteriData['yeni_musteri_tip'] == '1' ? $musteriData['yeni_vno'] : null,
                    'aktif' => 1,
                    'personel_id' => auth()->id(),
                ];
                $musteri = Musteri::create($yeniMusteriData);
                $yeniMusteriId = $musteri->id;
                Log::info('Yeni müşteri başarıyla kaydedildi (servis kaydı için).', ['musteri_id' => $yeniMusteriId]);
            }

            // Şimdi servisi oluştur/kaydet
            $servisDataToCreate = $servisValidator->validated(); // Sadece servis için valide edilen data
            $servisDataToCreate['musteri_id'] = $yeniMusteriId; // Yeni veya varolan ID
            $servisDataToCreate['personel_id'] = auth()->id();
            $servisDataToCreate['olusturan_personel_id'] = auth()->id();
            $servisDataToCreate['servis_durum_id'] = $defaultDurumId;
            $servisDataToCreate['tarih'] = Carbon::now()->format('Y-m-d');
            $servisDataToCreate['saat'] = Carbon::now()->format('H:i:s');
            $servisDataToCreate['operator_not'] = $request->input('operator_not'); // Eklendi

            Log::info('Yeni servis oluşturulacak veri:', $servisDataToCreate);
            $yeniServis = Servis::create($servisDataToCreate);
            Log::info('Yeni servis başarıyla kaydedildi.', ['servis_id' => $yeniServis->id]);

            // İlk işlem logunu oluştur
            Islemloglari::create([
                'islemi_yapan_personel_id' => $servisDataToCreate['personel_id'],
                'servis_id' => $yeniServis->id,
                'servis_durum_id' => $defaultDurumId,
                'tarih' => $servisDataToCreate['tarih'],
                'saat' => $servisDataToCreate['saat'],
                'aciklama' => 'Yeni servis kaydı oluşturuldu.' . (isset($servisDataToCreate['aciklama']) && $servisDataToCreate['aciklama'] ? '<br>Ek Not: ' . $servisDataToCreate['aciklama'] : ''),
            ]);
            Log::info('Yeni servis için ilk log kaydı oluşturuldu.', ['servis_id' => $yeniServis->id]);

            DB::commit(); // Transaction başarılı, commit et
            
            return response()->json(['success' => true, 'message' => 'Yeni servis başarıyla kaydedildi!', 'servis' => $yeniServis]);

        } catch (\Exception $e) {
            DB::rollBack(); // Hata oluştu, transaction geri al
            Log::error('Yeni servis kaydedilirken hata oluştu: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'data' => $this->sanitizeLogData($request->all())
            ]);
            return response()->json(['success' => false, 'message' => 'Servis kaydedilirken bir sunucu hatası oluştu.'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Servis $servi) // Route Model Binding (servi ismini kontrol et)
    {
        // Belirli bir servisi gösterme
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Servis $servi)
    {
        // Servis düzenleme formunu gösterme
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Servis $servisler)
    {
        if ($resp = $this->ensureServisAccess($servisler)) {
            return $resp;
        }
        Log::info('Servis Update Metodu Başladı', ['id' => $servisler->id, 'gelen_veri' => $this->sanitizeLogData($request->all())]);

        // Orijinal verileri ve ilişkili adları al
        $originalData = $servisler->only(['marka_id', 'cihaz_tur_id', 'cihaz_model', 'seri_no', 'cihaz_arizasi']);
        $servisler->loadMissing('marka:id,ad', 'cihazTuru:id,ad'); // Eksikse ilişkileri yükle
        $originalMarkaAdi = $servisler->marka?->ad;
        $originalCihazTuruAdi = $servisler->cihazTuru?->ad;

        // Validasyon
        $validatedData = $request->validate([
            'marka_id' => 'nullable|exists:tnm_markalar,id', // Tablo adını kontrol edin
            'cihaz_tur_id' => 'nullable|exists:tnm_cihazturleri,id', // Tablo adı düzeltildi
            'cihaz_model' => 'nullable|string|max:255',
            'seri_no' => 'nullable|string|max:255',
            'cihaz_arizasi' => 'nullable|string',
            'musteri_ad' => 'nullable|string|max:255',
            'musteri_tel1' => 'nullable|string|max:50',
            'musteri_tel2' => 'nullable|string|max:50',
            'musteri_adres' => 'nullable|string|max:500',
            'musteri_vdaire' => 'nullable|string|max:255',
            'musteri_vno' => 'nullable|string|max:50',
            'musteri_il_id' => 'nullable|exists:iller,id',
            'musteri_ilce_id' => 'nullable|exists:ilceler,id',
        ]);

        Log::info('Validasyon başarılı', $validatedData);

        try {
            // Model zaten Route Model Binding ile geldi ($servisler)
            // Sadece fillable olan alanları güncelle
            $guncellendi = $servisler->update($validatedData);
            if ($servisler->musteri_id && $request->hasAny(['musteri_ad','musteri_tel1','musteri_tel2','musteri_adres','musteri_vdaire','musteri_vno','musteri_il_id','musteri_ilce_id'])) {
                $musteriModel = Musteri::find($servisler->musteri_id);
                $oncekiIletisim = $musteriModel ? $musteriModel->only(['ad', 'tel1', 'tel2']) : [];
                $musteriPayload = [
                    'ad' => $request->input('musteri_ad'),
                    'tel1' => $request->input('musteri_tel1'),
                    'tel2' => $request->input('musteri_tel2'),
                    'adres' => $request->input('musteri_adres'),
                    'vdaire' => $request->input('musteri_vdaire'),
                    'vno' => $request->input('musteri_vno'),
                ];
                if ($request->has('musteri_il_id')) {
                    $musteriPayload['il_id'] = $request->input('musteri_il_id');
                }
                if ($request->has('musteri_ilce_id')) {
                    $musteriPayload['ilce_id'] = $request->input('musteri_ilce_id');
                }
                Musteri::where('id', $servisler->musteri_id)->update($musteriPayload);
                if ($musteriModel) {
                    $musteriModel->refresh();
                    SettingsAuditLog::recordMusteriIletisimDegisikligi(
                        (int) $servisler->musteri_id,
                        $oncekiIletisim,
                        $musteriModel->only(['ad', 'tel1', 'tel2']),
                        auth()->id()
                    );
                }
            }

            if ($guncellendi) {
                Log::info('Servis başarıyla güncellendi', ['id' => $servisler->id]);

                // İlişkileri (tekrar) yükle ve yeni adları al
                $servisler->load('marka:id,ad', 'cihazTuru:id,ad');
                $yeniMarkaAdi = $servisler->marka?->ad;
                $yeniCihazTuruAdi = $servisler->cihazTuru?->ad;
                Log::info('İlişkiler load denendi', ['marka' => $servisler->marka, 'cihazTuru' => $servisler->cihazTuru]);

                // Değişiklikleri loglamak için açıklama oluştur
                $logAciklama = ""; // Başlangıçta boş
                $degisiklikVar = false;

                if ($originalData['marka_id'] != $servisler->marka_id) {
                    $logAciklama .= "- Marka: '" . ($originalMarkaAdi ?? 'Boş') . "' -> '" . ($yeniMarkaAdi ?? 'Boş') . "'<br>";
                    $degisiklikVar = true;
                }
                if ($originalData['cihaz_tur_id'] != $servisler->cihaz_tur_id) {
                     $logAciklama .= "- Cihaz Türü: '" . ($originalCihazTuruAdi ?? 'Boş') . "' -> '" . ($yeniCihazTuruAdi ?? 'Boş') . "'<br>";
                     $degisiklikVar = true;
                }
                // String karşılaştırmalarını mb_strtolower ile yap (Büyük/küçük harf ve Türkçe karakterler için daha güvenilir)
                if (mb_strtolower((string)$originalData['cihaz_model'], 'UTF-8') != mb_strtolower((string)$servisler->cihaz_model, 'UTF-8')) {
                    $logAciklama .= "- Model: '" . ($originalData['cihaz_model'] ?: 'Boş') . "' -> '" . ($servisler->cihaz_model ?: 'Boş') . "'<br>";
                    $degisiklikVar = true;
                }
                if (mb_strtolower((string)$originalData['seri_no'], 'UTF-8') != mb_strtolower((string)$servisler->seri_no, 'UTF-8')) {
                    $logAciklama .= "- Seri No: '" . ($originalData['seri_no'] ?: 'Boş') . "' -> '" . ($servisler->seri_no ?: 'Boş') . "'<br>";
                    $degisiklikVar = true;
                }
                if (mb_strtolower((string)$originalData['cihaz_arizasi'], 'UTF-8') != mb_strtolower((string)$servisler->cihaz_arizasi, 'UTF-8')) {
                    $logAciklama .= "- Cihaz Arızası: '" . ($originalData['cihaz_arizasi'] ?: 'Boş') . "' -> '" . ($servisler->cihaz_arizasi ?: 'Boş') . "'<br>";
                    $degisiklikVar = true;
                }

                // Eğer değişiklik varsa log kaydı oluştur
                if ($degisiklikVar) {
                    // Açıklamaya başlığı ekle
                    $logAciklama = "Cihaz Bilgileri Güncellendi:<br>" . $logAciklama;
                    try {
                        Islemloglari::create([
                            'islemi_yapan_personel_id' => auth()->id(), // Auth middleware kullanıldığını varsayıyoruz
                            'servis_id' => $servisler->id,
                            'servis_durum_id' => null, // Durum değişikliği değil, cihaz güncelleme logu
                            'tarih' => Carbon::now()->format('Y-m-d'),
                            'saat' => Carbon::now()->format('H:i:s'),
                            'aciklama' => $logAciklama,
                            // 'uye_firma_id' => $servisler->uye_firma_id, // Gerekliyse ekleyin
                        ]);
                        Log::info('Cihaz Güncelleme Log Kaydı Oluşturuldu', ['servis_id' => $servisler->id]);
                    } catch (\Exception $logException) {
                        Log::error('İşlem logu kaydedilirken hata oluştu', [
                            'servis_id' => $servisler->id,
                            'hata' => $logException->getMessage()
                        ]);
                    }
                }

                // Yanıt için ilişkileri ve veriyi hazırla
                $servisler->loadMissing([
                    'musteri' => function ($query) {
                        $query->select('id','ad','tel1','tel2','adres','il_id','ilce_id','musteri_tip','vdaire','vno')
                              ->with(['il:id,ad', 'ilce:id,ad']);
                    },
                    'islemloglari' => function ($query) {
                        $query->with(['personel:id,ad', 'servisDurum:id,ad'])->orderBy('id', 'desc');
                    }
                ]); // Logları da yükle

                $responseData = $servisler->toArray();
                $responseData['marka'] = $servisler->marka ? ['id' => $servisler->marka->id, 'ad' => $servisler->marka->ad] : null;
                $responseData['cihazTuru'] = $servisler->cihazTuru ? ['id' => $servisler->cihazTuru->id, 'ad' => $servisler->cihazTuru->ad] : null;
                $responseData['islemloglari'] = $servisler->islemloglari; // Yüklenen logları yanıta ekle
                
                Log::info('JSON için hazırlanan veri (güncelleme sonrası)', $responseData);

                return response()->json(['success' => true, 'message' => 'Cihaz bilgileri başarıyla güncellendi.', 'servis' => $responseData]);

            } else {
                Log::warning('Servis güncellenemedi (update metodu false döndü)', ['id' => $servisler->id]);
                return response()->json(['success' => false, 'message' => 'Cihaz bilgileri güncellenemedi.'], 500);
            }

        } catch (\Exception $e) {
            Log::error('Servis Güncelleme Hatası', [
                'id' => $servisler->id,
                'hata' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['success' => false, 'message' => 'Güncelleme sırasında sunucu hatası oluştu.'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Servis $servi)
    {
        // Servisi silme
    }

    /**
     * Belirli bir servisin detaylarını (loglar, kasa hareketleri) JSON olarak döndürür.
     */
    public function getDetay(Request $request, Servis $servis)
    {
        $user = Auth::user();
        $isHariciOperator = $user && (int) $user->poz_id === 1076;
        $searchOverride = $request->boolean('search_override');
        $searchLen = (int) $request->input('search_len', 0);
        if (!($isHariciOperator && $searchOverride && $searchLen >= 7)) {
            if ($resp = $this->ensureServisAccess($servis)) {
                return $resp;
            }
        }

        // Atanan teknisyen kendi yönlendirilmiş işinin detayını açınca görüldü işaretle
        if (
            $user
            && (int) $user->id === (int) ($servis->personel_id ?? 0)
            && (int) $servis->servis_durum_id === TeknisyenYonlendirmeBildirimService::DURUM_TEKNISYEN_YONLENDIRILDI
            && empty($servis->teknisyen_goruldu_at)
        ) {
            $servis->teknisyen_goruldu_at = Carbon::now();
            $servis->teknisyen_goruldu_personel_id = (int) $user->id;
            $servis->save();
        }

        // İlişkileri yükle
        $servis->load([
            'musteri' => function ($query) { // İlişkiyi detaylandırarak yükle
                $query->select('id','ad','tel1','tel2','adres','il_id','ilce_id','musteri_tip','vdaire','vno') // Musteri sütunları
                      ->with(['il:id,ad', 'ilce:id,ad']); // Il ve Ilce ilişkilerini de yükle (sadece id ve ad)
            },
            'personel:id,ad',
            'marka:id,ad',
            'cihazTuru:id,ad',
            'servisDurum:id,ad',
            'islemloglari' => function ($query) {
                $query->with(['personel:id,ad', 'servisDurum:id,ad'])->orderBy('id', 'desc');
            },
            'kasaHareketleri' => function ($query) {
                 $query->with([
                            'personel:id,ad',      // İşlemi yapan personel
                            'ilgiliPersonel:id,ad', // Tahsil eden (servise atanmış teknisyen)
                            'odemeTuru:id,ad,yon', // Ödeme türü, adı ve YÖNÜ
                            'odemeSekli:id,ad'     // Ödeme şekli ve adı
                        ])
                        ->where(function ($q) {
                            $q->where('silindi', '!=', 1)
                              ->orWhereNull('silindi');
                        })
                        ->select('kasa.*') // Kasa tablosundaki tüm sütunları seç (gerceklesme dahil)
                        ->orderBy('tarih', 'desc') // En son hareketler üste gelecek şekilde
                        ->orderBy('saat', 'desc');
            },
            'servisResimleri' => function ($query) {
                $query->select('id', 'servis_id', 'dosya_yolu', 'aciklama', 'ekleyen_personel_id', 'created_at')
                      ->with(['ekleyenPersonel:id,ad']);
            }
        ]);

        // Tüm servis durumlarını al (modal dropdown için); Haber Verecek (9110) görünmez, mevcut servis o durumdaysa listeye eklenir
        $tumServisDurumlari = ServisDurum::gorunur()->orderBy('sira')->get(['id', 'ad', 'hangi_asamalarda_gorunur']);
        if ((int) $servis->servis_durum_id === ServisDurum::GORUNMEZ_DURUM_ID) {
            $mevcutDurum = ServisDurum::find(ServisDurum::GORUNMEZ_DURUM_ID);
            if ($mevcutDurum) {
                $tumServisDurumlari->push($mevcutDurum);
            }
        }
        
        // Tüm ödeme şekillerini al (yeni ödeme ekleme formu için)
        $tumOdemeSekilleri = KasaOdemeSekli::orderBy('ad')->get(['id', 'ad']);

        // Yanıta eklenecek ek bilgileri hazırla
        $responseData = $servis->toArray(); // Servis verisini diziye çevir
        $this->enrichIslemLoglari($responseData);
        $responseData['mevcutDurumId'] = $servis->servis_durum_id;
        $responseData['mevcutDurumAdi'] = $servis->servisDurum ? $servis->servisDurum->ad : 'Belirlenmemiş';
        $responseData['tumDurumlar'] = $tumServisDurumlari;
        $responseData['tumOdemeSekilleri'] = $tumOdemeSekilleri; // Yanıta eklendi
        $responseData['loggedInUserPozId'] = Auth::check() ? Auth::user()->poz_id : null; // Eklendi: Giriş yapan kullanıcının pozisyon ID'si
        $responseData['servisResimleri'] = $servis->servisResimleri; // Servis resimleri eklendi
        // Tahsil eden için servis personeli kullanılacak (personel ilişkisinden geliyor)

        // Kaydı oluşturan kişi: ilk işlem logunun yapanı (loglar desc yüklendiği için last() en eski kaydı verir)
        $olusturanAd = null; $olusturanId = null;
        if ($servis->relationLoaded('islemloglari') && $servis->islemloglari->count() > 0) {
            $ilkLog = $servis->islemloglari->last();
            if ($ilkLog && $ilkLog->personel) { $olusturanAd = $ilkLog->personel->ad; $olusturanId = $ilkLog->personel->id; }
        }
        $responseData['olusturan_personel'] = $olusturanAd ? ['id' => $olusturanId, 'ad' => $olusturanAd] : null;
        $responseData['teknisyen_goruldu_at'] = $servis->teknisyen_goruldu_at
            ? $servis->teknisyen_goruldu_at->toIso8601String()
            : null;
        $responseData['teknisyen_goruldu_personel_id'] = $servis->teknisyen_goruldu_personel_id;

        // Hata ayıklama için
        // return response()->json($servis);

        // return response()->json($servis); // Eski yanıt
        return response()->json($responseData); // Güncellenmiş yanıt
    }

    /**
     * Belirli bir servisin durumunu ve ilişkili detaylarını günceller.
     */
    public function updateDurumDetayli(Request $request, Servis $servis)
    {
        if ($resp = $this->ensureServisAccess($servis)) {
            return $resp;
        }
        try {
            // Veri doğrulama
            $validator = Validator::make($request->all(), [
                'servis_durum_id' => 'required|exists:servis_durum,id',
                'dinamik_veriler' => 'array'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Geçersiz veri girişi.',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Mevcut durumu al
            $mevcutDurum = $servis->servis_durum_id;
            $yeniDurum = $request->servis_durum_id;

            // Durum değişikliği yapılıyor mu kontrol et
            if ($mevcutDurum == $yeniDurum) {
                return response()->json([
                    'success' => false,
                    'message' => 'Durum değişikliği yapılmadı.'
                ]);
            }

            $yarinGidilecekDurumId = 9477;
            if ((int) $yeniDurum === $yarinGidilecekDurumId) {
                $yarinGidilecekCount = Islemloglari::where('servis_id', $servis->id)
                    ->where('servis_durum_id', $yarinGidilecekDurumId)
                    ->notDeleted()
                    ->count();
                if ($yarinGidilecekCount >= 3) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Servis kaydı daha fazla ileri tarihe atılamaz. Yöneticinize başvurun.'
                    ], 422);
                }
            }

            // Teknisyen için ödeme zorunluluğu:
            // - Hedef: Yerinde Bakım Yapıldı 9105, Cihaz Teslim Edildi 9115, Fiyatta Anlaşılamadı 9106
            // - Kaynak Parça Gidecek 9103 veya Atölyeye Alındı 9100 iken hedef: Servisi Sonlandırıldı 9099, Teslimata Hazır 9114
            $teknisyenOdemeZorunluHedefDurumIds = [9105, 9115, 9106];
            $teknisyenKaynakDurumlarSonlandirmaOncesi = [9100, 9103]; // Atölyeye Alındı, Parça Gidecek
            $sonlandirmaVeTamamlamaHedefDurumIds = [9099, 9114]; // Servisi Sonlandırıldı, Teslimata Hazır (Tamamlandı)
            $teknisyenPozisyonId = 1077;
            $hariciOperatorPozisyonId = 1076;
            $teknisyenYonlendirildiDurumId = 9098;
            $user = Auth::user();

            // Teknisyen / Harici Operatör başka teknisyene yönlendiremez (9098)
            if (
                $user
                && in_array((int) $user->poz_id, [$teknisyenPozisyonId, $hariciOperatorPozisyonId], true)
                && (int) $yeniDurum === $teknisyenYonlendirildiDurumId
            ) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bu duruma geçiş yetkiniz yok.',
                ], 403);
            }

            $hedefteOdemeZorunlu = in_array((int) $yeniDurum, $teknisyenOdemeZorunluHedefDurumIds, true)
                || (
                    in_array((int) $mevcutDurum, $teknisyenKaynakDurumlarSonlandirmaOncesi, true)
                    && in_array((int) $yeniDurum, $sonlandirmaVeTamamlamaHedefDurumIds, true)
                );
            if ($user && (int) $user->poz_id === $teknisyenPozisyonId && $hedefteOdemeZorunlu) {
                $odemeVar = Kasa::where('servis_id', $servis->id)->where('personel_id', $user->id)->exists();
                if (!$odemeVar) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Önce ödeme ekleyin.',
                    ], 422);
                }
            }

            // Transaction başlat
            return DB::transaction(function () use ($servis, $yeniDurum, $mevcutDurum, $request, $teknisyenYonlendirildiDurumId) {
                // Durumu güncelle
                $servis->servis_durum_id = $yeniDurum;
                $servis->save();

                // Durum adını al
                $durumAdi = ServisDurum::find($yeniDurum)->ad;

                // Log açıklaması oluştur
                $logAciklama = "";

                // Dinamik form verilerini al ve doğrula
                $dinamikVeriler = $request->dinamik_veriler;
                Log::info('Dinamik Veriler:', [
                    'servis_id' => $servis->id,
                    'servis_durum_id' => $yeniDurum,
                    'dinamik_veriler' => $dinamikVeriler
                ]);

                if ($dinamikVeriler) {
                    // Cevap0 kaydı oluştur
                    $cevap0 = new ServisDurumCevap0();
                    $cevap0->servis_id = $servis->id;
                    $cevap0->servis_durum_id = $yeniDurum;
                    $cevap0->uye_firma_id = $servis->uye_firma_id;
                    $cevap0->personel_id = auth()->id();
                    $cevap0->tarih = Carbon::now()->format('Y-m-d');
                    $cevap0->saat = Carbon::now()->format('H:i:s');
                    $cevap0->save();

                    Log::info('Cevap0 kaydı oluşturuldu:', [
                        'cevap0_id' => $cevap0->id,
                        'servis_id' => $servis->id
                    ]);

                    foreach ($dinamikVeriler as $key => $value) {
                        // Soru ID'sini al (dinamik_soru[13234 formatından)
                        preg_match('/dinamik_soru\[(\d+)/', $key, $matches);
                        $soruId = $matches[1] ?? null;

                        if (!$soruId) continue; // Geçersiz soru ID, atla

                        // Soruyu kontrol et
                        $soru = ServisDurumSoru::find($soruId);
                        if (!$soru) {
                            Log::warning('Geçersiz soru ID:', ['soru_id' => $soruId]);
                            continue; // Geçersiz soru, atla
                        }

                        try {
                            // Teyid Araması: boş cevapları kaydetme (input opsiyonel)
                            if (in_array((int) $yeniDurum, [9334, 9100], true)) {
                                if ($value === null || (is_string($value) && trim($value) === '')) {
                                    continue;
                                }
                            }
                            // Her cevap için ayrı kayıt oluştur
                            $cevap = new ServisDurumCevap();
                            $cevap->soru_id = $soruId;
                            $cevap->cevap = $value;
                            $cevap->durumCevap0_id = $cevap0->id;
                            $cevap->uye_firma_id = $servis->uye_firma_id;
                            $cevap->save();

                            // Log açıklamısına detayları ekle
                            if ($soru->cevap_format === '[personelSor]') {
                                $personel = Personel::find($value);
                                $logAciklama .= ($logAciklama ? "<br>" : "") . "- " . $soru->soru . ": " . ($personel ? $personel->ad : 'Bilinmiyor');
                            } else if ($soru->cevap_format === '[saatAraligiSec]') {
                                $baslangic = $dinamikVeriler[$key . '_baslangic'] ?? '';
                                $bitis = $dinamikVeriler[$key . '_bitis'] ?? '';
                                $logAciklama .= ($logAciklama ? "<br>" : "") . "- " . $soru->soru . ": " . $baslangic . " - " . $bitis;
                            } else {
                                $logAciklama .= ($logAciklama ? "<br>" : "") . "- " . $soru->soru . ": " . $value;
                            }

                            // Özel alan güncellemeleri
                            if ($soru->cevap_format === '[personelSor]') {
                                $servis->personel_id = $value;
                                $servis->save();
                            }
                            // Gidiş tarihi → servisler.tarih (teknisyen listesi bu alana göre filtreler)
                            if ($soru->cevap_format === '[tarihSor]' && is_string($value) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
                                $servis->tarih = $value;
                                $servis->save();
                            }

                        } catch (\Exception $e) {
                            Log::error('Cevap kaydedilirken hata oluştu:', [
                                'soru_id' => $soruId,
                                'cevap' => $value,
                                'hata' => $e->getMessage()
                            ]);
                        }
                    }
                    // Failsafe: Teknisyen yönlendirme sorusundan personel_id'yi mutlaka güncelle
                    $teknisyenSoruId = 13234;
                    $teknisyenKey = 'dinamik_soru[' . $teknisyenSoruId . ']';
                    if (!empty($dinamikVeriler[$teknisyenKey])) {
                        $servis->personel_id = (int) $dinamikVeriler[$teknisyenKey];
                        $servis->save();
                    }
                    // Failsafe: Gidiş Tarihi sorusundan servisler.tarih'i mutlaka güncelle
                    $gidisTarihiSoruId = 13235;
                    $gidisKey = 'dinamik_soru[' . $gidisTarihiSoruId . ']';
                    if (!empty($dinamikVeriler[$gidisKey]) && preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $dinamikVeriler[$gidisKey])) {
                        $servis->tarih = (string) $dinamikVeriler[$gidisKey];
                        $servis->save();
                    }
                }

                $yarinGidilecekDurumId = 9477;
                if ((int) $yeniDurum === $yarinGidilecekDurumId) {
                    $teknisyenId = (int) ($servis->personel_id ?? 0);
                    if ($teknisyenId > 0) {
                        $notifyAt = Carbon::tomorrow()->startOfDay();
                        $baslik = 'Yarın Gidilecek - Servis #' . $servis->id;
                        $icerik = 'Servis #' . $servis->id . ' için "Yarın Gidilecek" kaydı bugün yeniden takip edilmelidir.';
                        Announcement::updateOrCreate(
                            [
                                'servis_id' => $servis->id,
                                'personel_id' => $teknisyenId,
                                'hedef_rol' => 'TEKNISYEN',
                                'published_at' => $notifyAt,
                            ],
                            [
                                'baslik' => $baslik,
                                'icerik' => $icerik,
                                'aktif' => true,
                                'processed_at' => null,
                                'olusturan_personel_id' => Auth::id(),
                            ]
                        );
                        $logAciklama .= ($logAciklama ? "<br>" : "") . "Yarın Gidilecek bildirimi planlandı: " . $notifyAt->format('d.m.Y H:i');
                    }
                } else {
                    // 9477 dışına çıkınca (Fiyatta Anlaşılamadı vb.) bekleyen tüm
                    // hatırlatmaları kapat; yalnızca gelecek published_at yetmez.
                    Announcement::where('servis_id', $servis->id)
                        ->where('hedef_rol', 'TEKNISYEN')
                        ->whereNull('processed_at')
                        ->update([
                            'aktif' => false,
                            'processed_at' => Carbon::now(),
                        ]);
                }

                // İşlem logu açıklaması veritabanı sütun limitini (500 karakter) aşmasın
                $aciklamaMaxLength = 500;
                if (mb_strlen($logAciklama) > $aciklamaMaxLength) {
                    throw ValidationException::withMessages([
                        'aciklama' => [
                            'Açıklama metni çok uzun (en fazla ' . $aciklamaMaxLength . ' karakter). Lütfen kısaltıp tekrar deneyin. (Mevcut: ' . mb_strlen($logAciklama) . ' karakter)',
                        ],
                    ]);
                }

                // İşlem logunu kaydet
                $log = new Islemloglari();
                $log->islemi_yapan_personel_id = auth()->id();
                $log->servis_id = $servis->id;
                $log->servis_durum_id = $yeniDurum;
                $log->tarih = Carbon::now()->format('Y-m-d');
                $log->saat = Carbon::now()->format('H:i:s');
                $log->aciklama = $logAciklama;
                $log->save();

                $freshServis = $servis->fresh([
                    'servisDurum',
                    'personel',
                    'musteri',
                    'islemloglari' => function ($query) {
                        $query->with(['personel:id,ad', 'servisDurum:id,ad'])->orderBy('id', 'desc');
                    }
                ]);

                $teknisyenWhatsapp = null;
                if ((int) $yeniDurum === (int) $teknisyenYonlendirildiDurumId) {
                    $teknisyenWhatsapp = app(TeknisyenYonlendirmeBildirimService::class)
                        ->handleYonlendirme($freshServis ?: $servis);
                    $freshServis = $servis->fresh([
                        'servisDurum',
                        'personel',
                        'musteri',
                        'islemloglari' => function ($query) {
                            $query->with(['personel:id,ad', 'servisDurum:id,ad'])->orderBy('id', 'desc');
                        }
                    ]);
                }

                $freshData = $freshServis->toArray();
                $this->enrichIslemLoglari($freshData);
                return response()->json([
                    'success' => true,
                    'message' => 'Durum başarıyla güncellendi.',
                    'servis' => $freshData,
                    'teknisyen_whatsapp' => $teknisyenWhatsapp,
                ]);
            });

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Açıklama metni çok uzun. Lütfen kısaltıp tekrar deneyin.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            // Hatayı logla
            Log::error('Durum güncelleme hatası: ' . $e->getMessage(), [
                'servis_id' => $servis->id,
                'yeni_durum' => $request->servis_durum_id,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Durum güncellenirken bir hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toplu servis durum güncelleme.
     */
    public function bulkUpdateDurum(Request $request)
    {
        $user = Auth::user();
        $patronPozisyonId = 1071;
        $muhasebePozisyonId = 1080;
        if (!$user || !in_array((int) $user->poz_id, [$patronPozisyonId, $muhasebePozisyonId], true)) {
            return response()->json(['success' => false, 'message' => 'Bu işlem için yetkiniz bulunmamaktadır.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'servis_ids' => 'required|array|min:1',
            'servis_ids.*' => 'integer|exists:servisler,id',
            'servis_durum_id' => 'required|exists:servis_durum,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Doğrulama hatası.', 'errors' => $validator->errors()], 422);
        }

        $ids = collect($request->input('servis_ids', []))
            ->map(function ($id) { return (int) $id; })
            ->filter(function ($id) { return $id > 0; })
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Geçerli servis bulunamadı.'], 422);
        }

        $durumId = (int) $request->input('servis_durum_id');
        $now = Carbon::now();

        $servisler = Servis::whereIn('id', $ids)
            ->where(function ($q) {
                $q->where('silindi', '!=', 1)->orWhereNull('silindi');
            })
            ->where('servis_durum_id', '!=', $durumId)
            ->get(['id', 'personel_id']);

        if ($servisler->isEmpty()) {
            return response()->json(['success' => true, 'message' => 'Güncellenecek kayıt bulunamadı.', 'updated' => 0]);
        }

        $yarinGidilecekDurumId = 9477;
        $notifyAt = Carbon::tomorrow()->startOfDay();
        DB::transaction(function () use ($servisler, $durumId, $now, $user, $yarinGidilecekDurumId, $notifyAt) {
            Servis::whereIn('id', $servisler->pluck('id'))
                ->update(['servis_durum_id' => $durumId]);

            foreach ($servisler as $servis) {
                $log = new Islemloglari();
                $log->islemi_yapan_personel_id = $user->id;
                $log->servis_id = $servis->id;
                $log->servis_durum_id = $durumId;
                $log->tarih = $now->format('Y-m-d');
                $log->saat = $now->format('H:i:s');
                $log->aciklama = ((int) $durumId === $yarinGidilecekDurumId)
                    ? ('Toplu durum güncelleme (Yarın Gidilecek bildirimi planlandı: ' . $notifyAt->format('Y-m-d H:i') . ')')
                    : 'Toplu durum güncelleme';
                $log->save();
            }

            if ((int) $durumId === $yarinGidilecekDurumId) {
                foreach ($servisler as $servis) {
                    $teknisyenId = (int) ($servis->personel_id ?? 0);
                    if ($teknisyenId <= 0) {
                        continue;
                    }
                    $baslik = 'Yarın Gidilecek - Servis #' . $servis->id;
                    $icerik = 'Servis #' . $servis->id . ' için "Yarın Gidilecek" kaydı bugün yeniden takip edilmelidir.';
                    Announcement::updateOrCreate(
                        [
                            'servis_id' => $servis->id,
                            'personel_id' => $teknisyenId,
                            'hedef_rol' => 'TEKNISYEN',
                            'published_at' => $notifyAt,
                        ],
                        [
                            'baslik' => $baslik,
                            'icerik' => $icerik,
                            'aktif' => true,
                            'processed_at' => null,
                            'olusturan_personel_id' => $user->id,
                        ]
                    );
                }
            } else {
                Announcement::whereIn('servis_id', $servisler->pluck('id'))
                    ->where('hedef_rol', 'TEKNISYEN')
                    ->whereNull('processed_at')
                    ->update([
                        'aktif' => false,
                        'processed_at' => $now,
                    ]);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Seçili kayıtların durumu güncellendi.',
            'updated' => $servisler->count()
        ]);
    }

    /**
     * Toplu servis soft delete.
     */
    public function bulkSoftDelete(Request $request)
    {
        $user = Auth::user();
        $patronPozisyonId = 1071;
        $muhasebePozisyonId = 1080;
        if (!$user || !in_array((int) $user->poz_id, [$patronPozisyonId, $muhasebePozisyonId], true)) {
            return response()->json(['success' => false, 'message' => 'Bu işlem için yetkiniz bulunmamaktadır.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'servis_ids' => 'required|array|min:1',
            'servis_ids.*' => 'integer|exists:servisler,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Doğrulama hatası.', 'errors' => $validator->errors()], 422);
        }

        $ids = collect($request->input('servis_ids', []))
            ->map(function ($id) { return (int) $id; })
            ->filter(function ($id) { return $id > 0; })
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Geçerli servis bulunamadı.'], 422);
        }

        $now = Carbon::now()->toDateTimeString();
        $updated = Servis::whereIn('id', $ids)
            ->where(function ($q) {
                $q->where('silindi', '!=', 1)->orWhereNull('silindi');
            })
            ->update([
                'silindi' => 1,
                'silen_kisi_id' => $user->id,
                'silinme_tarihi' => $now
            ]);

        // İlgili kasa kayıtlarını da soft delete olarak işaretle
        Kasa::whereIn('servis_id', $ids)
            ->where(function ($q) {
                $q->where('silindi', '!=', 1)->orWhereNull('silindi');
            })
            ->update(['silindi' => 1]);

        return response()->json([
            'success' => true,
            'message' => 'Seçili servis kayıtları silindi.',
            'updated' => $updated
        ]);
    }

    /**
     * Belirtilen servisi "silindi" olarak işaretler (soft delete).
     */
    public function softDelete(Request $request, Servis $servis)
    {
        $loggedInUser = Auth::user();
        $allowedPozisyonIds = [1071, 1080]; // Patron, Muhasebe
        if (!$loggedInUser || !in_array((int) $loggedInUser->poz_id, $allowedPozisyonIds, true)) {
            Log::warning('Yetkisiz servis silme denemesi (soft delete).', ['user_id' => $loggedInUser->id ?? null, 'servis_id' => $servis->id]);
            return response()->json(['success' => false, 'message' => 'Servis silme yetkiniz bulunmamaktadır.'], 403);
        }

        Log::info('Servis soft delete çağrıldı.', ['servis_id' => $servis->id]);
        try {
            $servis->silindi = 1;
            $servis->silen_kisi_id = Auth::id(); // Giriş yapan kullanıcının ID'si
            $servis->silinme_tarihi = Carbon::now()->toDateTimeString(); // Mevcut tarih ve saat
            $servis->save();

            // İlgili kasa kayıtlarını da soft delete olarak işaretle
            Kasa::where('servis_id', $servis->id)
                ->where(function ($q) {
                    $q->where('silindi', '!=', 1)
                      ->orWhereNull('silindi');
                })
                ->update(['silindi' => 1]);

            Log::info('Servis başarıyla silindi (soft delete) olarak işaretlendi.', ['servis_id' => $servis->id]);
            return response()->json(['success' => true, 'message' => 'Servis kaydı başarıyla silindi.']);

        } catch (\Exception $e) {
            Log::error('Servis soft delete edilirken hata: ' . $e->getMessage(), ['servis_id' => $servis->id, 'trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'Servis silinirken bir sunucu hatası oluştu.'], 500);
        }
    }

    // --- SERVİS FİŞİ PDF İŞLEMLERİ ---

    public function generateAndShowServisFisiPdf(Request $request, Servis $servis)
    {
        if ($resp = $this->ensureServisAccess($servis)) {
            return $resp;
        }
        ob_start(); // Çıktı tamponlamasını başlat

        // ... (mevcut veri toplama ve $data dizisini oluşturma kodunuz) ...
        $servis->load([
            'musteri.il',
            'musteri.ilce',
            'marka',
            'cihazTuru',
            'servisDurum',
            'islemloglari.personel',
            'islemloglari.servisDurum',
            'kasaHareketleri.personel',
            'kasaHareketleri.odemeSekli',
            'kasaHareketleri.odemeTuru',
            'personel',
        ]);

        // Soft-delete edilenler ilişki seviyesinde dışlanır; PDF'de yalnızca gelir/nötr hareketler
        $filteredKasaHareketleri = $servis->kasaHareketleri->filter(function ($hareket) {
            return (int) ($hareket->silindi ?? 0) !== 1 && $hareket->odeme_yonu != -1;
        });

        // PDF görünümüne gönderilecek $data dizisini oluştur
        $olusturanPersonel = $servis->personel;
        if ($olusturanPersonel) {
            Log::info('PDF için personel servis kaydından alındı.', ['personel_id' => $olusturanPersonel->id]);
        } else {
            Log::warning('Servis kaydında personel bulunamadı, firma bilgileri basılmayacak.');
        }
        
        if(!$olusturanPersonel) {
            Log::error('PDF için personel bulunamadı. Firma bilgileri boş bırakılacak.');
            $olusturanPersonel = new Personel();
        }

        Log::info('PDF için kullanılacak son personel bilgileri:', [
            'id' => $olusturanPersonel->id,
            'ad' => $olusturanPersonel->ad, // Eğer 'ad' alanı varsa
            'fis_firma' => $olusturanPersonel->fis_firma,
            'fis_tel' => $olusturanPersonel->fis_tel,
            'fis_adres' => $olusturanPersonel->fis_adres
        ]);

        $sabitNotlar = [
            "TÜKETİCİYE YAKIN SERVİS OLMAMASINDA EN YAKIN SERVİS SORUMLUDUR.",
            "TÜKETİCİYE EN YAKIN SERVİSİN HİZMET VERMEMESİYLE MALIN İADE MASRAFLARI TALEP EDİLEMEZ.",
            "YÖNETMELİĞİN 11.MADDESİNDEKİ HUSUSLARIN İÇERDİĞİ EVRAĞIN NÜSHASININ VERİLMESİ ZORUNLUDUR.",
            "KULLANIM SÜRESİNCE ONARIM AZAMİ SÜREYİ GEÇEMEZ TESLİMDEN İTİBAREN BAŞLAR. ARIZA TÜM İLETİŞİM YOLUYLA BİLDİRİLİR. UYUŞMAZLIKTA İSPAT YÜKÜMLÜLÜĞÜ TÜKETİCİYE AİTTİR.",
            "TAMİRİN TAMAMLANDIĞI TARİH BİLDİRİLMELİDİR. UYUŞMAZLIKTA İSPAT YÜKÜMLÜLÜĞÜ SERVİSİNDİR.",
            "DEĞİŞEN PARÇANIN 6 AY GARANTİSİ VARDIR.",
            "KULLANIM HATASI KAYNAKLI ARIZA ÜCRETLİDİR.",
            "ONARIM YAPILMADIGINDA SERVIS ÜCRETİ ALINIR.",
            "MÜŞTERİ İRADESİYLE OLAN İADELER ÜCRETE TABİ TUTULUR.",
            "GARANTİLİ İŞLEMLERLE İLGİLİ ARIZALARA 8 İŞ GÜNÜ İÇİNDE SERVİS ULAŞTIRILIR.",
            "BU FİŞ FATURA YERİNE GEÇMEZ.",
            "ÇALIŞMA SAATLERİ 09:00 - 19:00'DUR.",
            "PAZAR GÜNLERİ ŞİRKETİMİZ KAPALIDIR."
        ];

        // İşlem loglarındaki açıklamaları PDF için önceden formatla (tüm işlemler, en yeniden eskiye)
        $formattedIslemLoglari = $servis->islemloglari
            ->sortByDesc(function ($log) { return ($log->tarih ?? '') . ' ' . ($log->saat ?? ''); })
            ->values()
            ->map(function ($log) {
                $log->formatted_aciklama = $log->aciklama ?? '';
                return $log;
            });

        $musteriImza = $request->input('musteri_imza_data', null);
        $teknisyenImza = $request->input('teknisyen_imza_data', null);

        $tarih = Carbon::now()->format('Y-m-d');
        $saat = Carbon::now()->format('H:i:s');
        $ayYilKlasoru = Carbon::now()->format('Ym'); 
        $benzersizEk = Str::random(6); 
        $dosyaAdi = $servis->id . '_' . Carbon::now()->format('YmdHis') . '_' . $benzersizEk . '.pdf';
        $klasorYolu = 'uploads/servis_formlari/' . $ayYilKlasoru;
        $tamDosyaYolu = $klasorYolu . '/' . $dosyaAdi;

        $data = [
            'servis' => $servis,
            'personelBilgileri' => $olusturanPersonel, 
            'sabitNotlar' => $sabitNotlar,
            'servisFisi' => null, 
            'musteriImzaBase64' => $musteriImza, 
            'teknisyenImzaBase64' => $teknisyenImza,
            'formattedIslemLoglari' => $formattedIslemLoglari, // Formatlanmış logları view'a gönder
            'mus_imza' => $musteriImza,
            'tek_imza' => $teknisyenImza,
            'olusturanPersonel' => $olusturanPersonel,
            'kasaHareketleri' => $filteredKasaHareketleri, // Filtrelenmiş kasa hareketlerini kullan
        ];

        try {
            $pdf = app('dompdf.wrapper');
            
            // DOMPDF seçeneklerini ayarla
            $options = new \Dompdf\Options();
            $options->set('isHtml5Enabled', true);
            $options->set('isRemoteEnabled', true); // Harici kaynaklara erişim için (örn: CDN fontları, resimler)
            // $options->set('fontDir', storage_path('fonts/')); // Eğer özel fontlarınız varsa
            // $options->set('fontCache', storage_path('fonts/')); // Font cache yolu
            // $options->set('defaultFont', 'DejaVu Sans'); // Varsayılan fontu tekrar belirtmek

            $pdf->getDomPDF()->setOptions($options);
            $pdf->loadView('crm.servis_fisi_pdf', $data);
            
            Storage::disk('public')->makeDirectory($klasorYolu);
            Storage::disk('public')->put($tamDosyaYolu, $pdf->output());
            Log::info('Servis fişi PDF oluşturuldu ve kaydedildi: ' . $tamDosyaYolu);

            Log::info('ServisFisi create edilecek data:', [
                'servis_id' => $servis->id,
                'pdf' => $tamDosyaYolu,
                'pdf_length' => strlen($tamDosyaYolu), 
                'tarih' => $tarih,
                'saat' => $saat,
                'olusturan_personel_id' => $olusturanPersonel ? $olusturanPersonel->id : null,
                'mus_imza_length' => $musteriImza ? strlen($musteriImza) : 0, 
                'tek_imza_length' => $teknisyenImza ? strlen($teknisyenImza) : 0, 
            ]);

            $yeniFis = ServisFisi::create([
                'servis_id' => $servis->id,
                'pdf' => $tamDosyaYolu,
                'tarih' => $tarih,
                'saat' => $saat,
                'olusturan_personel_id' => $olusturanPersonel ? $olusturanPersonel->id : null, // Null kontrolü eklendi
                'mus_imza' => $musteriImza, 
                'tek_imza' => $teknisyenImza, 
            ]);
            Log::info('Yeni servis fişi veritabanına kaydedildi.', ['fis_id' => $yeniFis->id]);
            
            ob_end_clean(); // Çıktı tamponunu temizle ve kapat
            $response = $pdf->stream($dosyaAdi);
            $pdfUrl = Storage::disk('public')->url($tamDosyaYolu);
            return $response->header('X-Pdf-Url', $pdfUrl);

        } catch (\Exception $e) {
            ob_end_clean(); // Hata durumunda da tamponu temizle
            Log::error('PDF oluşturma veya kaydetme hatası (generateAndShowServisFisiPdf): ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response("PDF oluşturulurken bir hata oluştu: " . $e->getMessage(), 500);
        }
    }

    public function showServisFisiPdf(ServisFisi $servisFisi) // Route model binding
    {
        $servis = Servis::find($servisFisi->servis_id);
        if (!$servis) {
            abort(404, 'Servis bulunamadı.');
        }
        if ($resp = $this->ensureServisAccess($servis)) {
            return $resp;
        }
        // Kullanıcının bu fişe erişim yetkisi var mı kontrol edilebilir (opsiyonel)
        $dosyaYolu = $servisFisi->pdf;

        if (Storage::disk('public')->exists($dosyaYolu)) {
            return Storage::disk('public')->response($dosyaYolu);
        } else {
            Log::error('Servis fişi dosyası bulunamadı: ' . $dosyaYolu);
            abort(404, 'Servis fişi bulunamadı.');
        }
    }

    /**
     * Belirtilen servise yeni bir kasa hareketi ekler.
     */
    public function addKasaHareketi(Request $request, Servis $servis)
    {
        if ($resp = $this->ensureServisAccess($servis)) {
            return $resp;
        }
        $validator = Validator::make($request->all(), [
            'odeme_sekli_id' => 'required|exists:kasa_odeme_sekli,id',
            'gerceklesme' => 'required|in:0,1', // 0: Beklemede, 1: Tamamlandı
            'tutar' => 'required|numeric|min:0',
            // Ödeme türü (gelir/gider) varsayılan olarak müşteri ödemesi olduğu için "gelir" kabul edilecek.
            // Bu, kasa_odeme_turu tablosundaki bir ID'ye karşılık gelmeli.
            'odeme_turu_id' => 'required|integer|exists:kasa_odeme_turu,id',
            'tarih' => 'nullable|date_format:Y-m-d',
            'saat' => 'nullable|date_format:H:i',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $now = Carbon::now();
        $tarih = $request->filled('tarih') ? $request->input('tarih') : $now->format('Y-m-d');
        $saat = $request->filled('saat') ? $request->input('saat') : $now->format('H:i:s');
        if (strlen($saat) === 5) {
            $saat = $saat . ':00';
        }

        Log::debug('Servis detay ödeme ekleme tarih', [
            'app_timezone' => config('app.timezone'),
            'server_now' => $now->format('Y-m-d H:i:s'),
            'request_tarih' => $request->input('tarih'),
            'request_saat' => $request->input('saat'),
            'kullanilacak_tarih' => $tarih,
            'kullanilacak_saat' => $saat,
        ]);

        try {
            // Tahsil eden = servise atanmış teknisyen; her zaman güncel değeri al (refresh)
            $servis->refresh();
            $tahsilEdenPersonelId = ($servis->personel_id !== null && (int) $servis->personel_id > 0)
                ? (int) $servis->personel_id
                : null;

            $yeniHareket = Kasa::create([
                'servis_id' => $servis->id,
                'personel_id' => Auth::id(), // Giriş yapmış olan kullanıcı (işlemi yapan)
                'ilgili_personel_id' => $tahsilEdenPersonelId, // Tahsil eden = servise atanmış teknisyen
                'uye_firma_id' => $servis->uye_firma_id, // Servisin firma ID'si
                'odeme_sekli_id' => $request->odeme_sekli_id,
                'odeme_turu_id' => $request->odeme_turu_id, // Varsayılan olarak bir gelir türü ID'si (örn: 1)
                'odeme_yonu' => 1, // Servis detaydan eklenenler her zaman gelir (1) kabul edilir.
                'tutar' => $request->tutar,
                'gerceklesme' => $request->gerceklesme,
                'tarih' => $tarih,
                'islem_tarihi' => $tarih,
                'saat' => $saat,
                'aciklama' => 'Servis Detay ekranından manuel ödeme eklendi.',
            ]);

            // Yanıt için yeni eklenen hareketi ilişkileriyle birlikte yükle
            $yeniHareket->load(['personel:id,ad', 'ilgiliPersonel:id,ad', 'odemeSekli:id,ad', 'odemeTuru:id,ad,yon']);

            return response()->json([
                'success' => true,
                'message' => 'Ödeme başarıyla eklendi.',
                'yeniHareket' => $yeniHareket
            ]);

        } catch (\Exception $e) {
            Log::error('Kasa hareketi eklenirken hata: ' . $e->getMessage(), ['servis_id' => $servis->id, 'request_data' => $this->sanitizeLogData($request->all())]);
            return response()->json(['success' => false, 'message' => 'Ödeme eklenirken bir sunucu hatası oluştu.'], 500);
        }
    }


    public function listServisFisleri(Servis $servis)
    {
        if ($resp = $this->ensureServisAccess($servis)) {
            return $resp;
        }
        // Belirli bir servise ait, en yeniden eskiye sıralı fişleri getir
        // created_at sütunu olmadığı için id'ye veya tarih/saat kombinasyonuna göre sırala
        $fisler = $servis->fisleri()
            ->orderBy('tarih', 'desc')
            ->orderBy('saat', 'desc') // Aynı tarihtekileri saate göre sırala
            ->get([
                'id', 
                'pdf', 
                'tarih', 
                'saat' 
                // 'created_at' kaldırıldı
            ]);
        $fisler->transform(function ($fis) {
            $fis->pdf_url = $fis->pdf ? Storage::disk('public')->url($fis->pdf) : null;
            return $fis;
        });
        return response()->json($fisler);
    }

    /**
     * Servis için resim yüklemeyi işler.
     */
    public function uploadServisResmi(Request $request, Servis $servis)
    {
        $loggedInUser = Auth::user();
        $patronPozisyonId = 1071;
        $tsrnTeknisyenPozisyonId = 1077;

        // Sadece Patron veya TŞRN Teknisyen yetkisine sahip kullanıcıların resim yüklemesine izin ver
        if (!$loggedInUser || ($loggedInUser->poz_id != $patronPozisyonId && $loggedInUser->poz_id != $tsrnTeknisyenPozisyonId)) {
            return response()->json(['success' => false, 'message' => 'Bu işlemi yapmaya yetkiniz bulunmamaktadır.'], 403);
        }
        if ((int) $loggedInUser->poz_id === $tsrnTeknisyenPozisyonId && (int) $servis->personel_id !== (int) $loggedInUser->id) {
            return response()->json(['success' => false, 'message' => 'Sadece size atanmış servislere resim ekleyebilirsiniz.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'resim' => 'required|image|mimes:jpeg,png,jpg,gif|max:10240', // Max 10MB
            'aciklama' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $resim = $request->file('resim');

            // Dosya kontrolü
            if (!$resim || !$resim->isValid()) {
                return response()->json(['success' => false, 'message' => 'Geçersiz resim dosyası.'], 422);
            }

            // Dosya boyutu kontrolü (frontend ile aynı)
            if ($resim->getSize() > 10 * 1024 * 1024) { // 10MB
                return response()->json(['success' => false, 'message' => 'Dosya boyutu 10MB\'dan büyük olamaz.'], 422);
            }

            $currentDate = Carbon::now();
            $yilAyKlasoru = $currentDate->format('Y-m'); // Yıl-Ay şeklinde klasör
            $klasorYolu = 'uploads/servis_resimleri/' . $yilAyKlasoru;

            // Resmin adını benzersiz yap
            $resimAdi = Str::random(20) . '.' . $resim->getClientOriginalExtension();
            $tamDosyaYolu = $klasorYolu . '/' . $resimAdi;

            // Klasörü oluştur (varsa sorun olmaz)
            Storage::disk('public')->makeDirectory($klasorYolu);

            // Resmi storage/app/public altına kaydet
            $fileContent = file_get_contents($resim->getRealPath());
            if ($fileContent === false) {
                return response()->json(['success' => false, 'message' => 'Resim dosyası okunamadı.'], 500);
            }

            $saveResult = Storage::disk('public')->put($tamDosyaYolu, $fileContent);
            if (!$saveResult) {
                return response()->json(['success' => false, 'message' => 'Resim kaydedilirken disk hatası oluştu.'], 500);
            }

            // Veritabanına kaydet
            $servisResmi = ServisResmi::create([
                'servis_id' => $servis->id,
                'dosya_yolu' => $tamDosyaYolu,
                'aciklama' => $request->aciklama,
                'ekleyen_personel_id' => $loggedInUser->id,
            ]);

            Log::info('Servis resmi başarıyla yüklendi', [
                'servis_id' => $servis->id,
                'resim_id' => $servisResmi->id,
                'dosya_yolu' => $tamDosyaYolu,
                'boyut_kb' => round($resim->getSize() / 1024, 2)
            ]);

            return response()->json(['success' => true, 'message' => 'Resim başarıyla yüklendi.', 'resim' => $servisResmi]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Resim yükleme validation hatası', [
                'servis_id' => $servis->id,
                'errors' => $e->errors()
            ]);
            return response()->json(['success' => false, 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Servis resmi yüklenirken beklenmedik hata: ' . $e->getMessage(), [
                'servis_id' => $servis->id,
                'trace' => $e->getTraceAsString(),
                'file_info' => $resim ? [
                    'name' => $resim->getClientOriginalName(),
                    'size' => $resim->getSize(),
                    'mime' => $resim->getMimeType()
                ] : null
            ]);

            // Kullanıcı dostu hata mesajı
            $userMessage = 'Resim yüklenirken bir sunucu hatası oluştu.';
            if (strpos($e->getMessage(), 'disk') !== false || strpos($e->getMessage(), 'storage') !== false) {
                $userMessage = 'Disk alanı dolu veya yazma izni yok. Sistem yöneticisi ile iletişime geçin.';
            } elseif (strpos($e->getMessage(), 'memory') !== false) {
                $userMessage = 'Bellek yetersiz. Daha küçük bir resim dosyası deneyin.';
            }

            return response()->json(['success' => false, 'message' => $userMessage], 500);
        }
    }

    /**
     * Servis resmini güvenli bir şekilde sunar.
     */
    public function getServisResmi(ServisResmi $servisResmi)
    {
        $servisId = $servisResmi->servis_id;
        if (!$servisId) {
            abort(404, 'Resim bulunamadı.');
        }
        $servis = Servis::find($servisId);
        if (!$servis) {
            abort(404, 'Servis bulunamadı.');
        }
        if ($resp = $this->ensureServisAccess($servis)) {
            return $resp;
        }
        $dosyaYolu = $servisResmi->dosya_yolu;

        if (Storage::disk('public')->exists($dosyaYolu)) {
            return Storage::disk('public')->download($dosyaYolu);
        } else {
            abort(404, 'Resim bulunamadı.');
        }
    }
} 