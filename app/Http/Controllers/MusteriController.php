<?php

namespace App\Http\Controllers;

use App\Models\Musteri;
use App\Models\Il;
use App\Models\Ilce;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Models\ServisDurum;
use App\Models\ServisDurumSoru;
use App\Models\Personel;
use App\Models\Servis;
use App\Models\TnmMarka;
use App\Models\TnmCihazTuru;
use Illuminate\Support\Facades\Auth; // Eklendi
use App\Models\TnmPersonelPozisyon; // Eklendi
use App\Models\SettingsAuditLog;

class MusteriController extends Controller
{
    private function sanitizeLogData(array $data): array
    {
        $sensitiveKeys = ['tel1', 'tel2', 'adres', 'vno', 'vdaire', 'email'];
        foreach ($sensitiveKeys as $key) {
            if (array_key_exists($key, $data) && $data[$key] !== null) {
                $data[$key] = '[REDACTED]';
            }
        }
        return $data;
    }
    private function ensureMusteriManageAccess()
    {
        $loggedInUser = Auth::user();
        $allowedPozisyonIds = [1071, 1080, 1073]; // Patron, Muhasebe, Operatör
        if (!$loggedInUser || !in_array((int) $loggedInUser->poz_id, $allowedPozisyonIds, true)) {
            $req = request();
            if ($req->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Müşteri yönetimine erişim yetkiniz yok.'], 403);
            }
            abort(403, 'Bu sayfaya erişim yetkiniz bulunmamaktadır.');
        }

        return null;
    }

    private function ensureMusteriLookupAccess()
    {
        $loggedInUser = Auth::user();
        $allowedPozisyonIds = [1071, 1080, 1073, 1076]; // Patron, Muhasebe, Operatör, Harici Operatör
        if (!$loggedInUser || !in_array((int) $loggedInUser->poz_id, $allowedPozisyonIds, true)) {
            return response()->json(['success' => false, 'message' => 'Bu işlem için yetkiniz bulunmamaktadır.'], 403);
        }
        return null;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($resp = $this->ensureMusteriManageAccess()) {
            return $resp;
        }

        Log::info('MusteriController@index çağrıldı.');
        try {
            if ($request->ajax() || $request->has('draw') || $request->expectsJson()) {
                $baseQuery = Musteri::query();

                $recordsTotal = (clone $baseQuery)->count();

                $searchValue = trim((string) $request->input('search.value', $request->input('q', '')));
                if ($searchValue !== '') {
                    $numericSearch = preg_replace('/\D+/', '', $searchValue);
                    $baseQuery->where(function ($q) use ($searchValue, $numericSearch) {
                        $like = '%' . $searchValue . '%';

                        $q->where('musteriler.ad', 'like', $like)
                          ->orWhere('musteriler.tel1', 'like', $like)
                          ->orWhere('musteriler.tel2', 'like', $like)
                          ->orWhere('musteriler.adres', 'like', $like)
                          ->orWhereHas('ilce', function ($iq) use ($like) {
                              $iq->where('ad', 'like', $like);
                          })
                          ->orWhereHas('ilce.il', function ($iq) use ($like) {
                              $iq->where('ad', 'like', $like);
                          });

                        if ($numericSearch) {
                            $phoneLike = '%' . $numericSearch . '%';
                            $normalizeTel = function ($column) {
                                return "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE($column,' ',''),'-',''),'/',''),'(',''),')','')";
                            };
                            $q->orWhereRaw($normalizeTel('tel1') . ' LIKE ?', [$phoneLike])
                              ->orWhereRaw($normalizeTel('tel2') . ' LIKE ?', [$phoneLike]);
                        }
                    });
                }

                $recordsFiltered = (clone $baseQuery)->count();

                $orderColumn = $request->input('order.0.column');
                $orderDirInput = $request->input('order.0.dir', 'desc');
                $orderDir = in_array($orderDirInput, ['asc', 'desc'], true) ? $orderDirInput : 'desc';
                $orderColumnMap = [
                    0 => 'musteriler.id',
                    1 => 'musteriler.ad',
                    2 => 'musteriler.tel1',
                    3 => 'musteriler.adres',
                    4 => 'musteriler.musteri_tip',
                    5 => 'musteriler.created_at',
                ];
                if (isset($orderColumnMap[$orderColumn])) {
                    $baseQuery->orderBy($orderColumnMap[$orderColumn], $orderDir);
                } else {
                    $baseQuery->latest('musteriler.created_at');
                }

                $start = (int) $request->input('start', 0);
                $length = (int) $request->input('length', 20);
                if ($length > 0) {
                    $baseQuery->skip($start)->take($length);
                }

                $musteriler = $baseQuery
                    ->with(['ilce:id,ad,il_id', 'ilce.il:id,ad'])
                    ->get();

                return response()->json([
                    'draw' => (int) $request->input('draw'),
                    'recordsTotal' => $recordsTotal,
                    'recordsFiltered' => $recordsFiltered,
                    'data' => $musteriler,
                ]);
            }

            $musteriler = collect();
            
            // Modal için illeri aktif bölgelere göre filtrele
            $aktifIlCount = \Illuminate\Support\Facades\DB::table('aktif_iller')->count();
            if ($aktifIlCount > 0) {
                $aktifIlIds = \Illuminate\Support\Facades\DB::table('aktif_iller')->pluck('il_id');
                $iller = Il::whereIn('id', $aktifIlIds)->orderBy('ad')->get(['id', 'ad']);
            } else {
                $iller = Il::orderBy('ad')->get(['id', 'ad']);
            }
            
            // Layouts.app için gerekli genel veriler
            $servisDurumlar = ServisDurum::gorunur()->orderBy('sira')->get();
            $servisDurumSorular = ServisDurumSoru::all();
            $personeller = Personel::where('aktif', 1)->orderBy('ad')->get();
            $markalar = TnmMarka::orderBy('ad')->get();
            $cihazTurleri = TnmCihazTuru::orderBy('ad')->get();
            
            Log::info('Müşteriler, iller ve genel veriler başarıyla çekildi.');
            
            // Doğru view'ı ve gerekli tüm verileri gönder
            return view('crm.musteri.index', compact(
                'musteriler', 
                'iller', 
                'servisDurumlar', 
                'servisDurumSorular', 
                'personeller', 
                'markalar', 
                'cihazTurleri'
            ));

        } catch (\Exception $e) {
            Log::error('Müşteri listesi veya diğer veriler çekilirken hata: ' . $e->getMessage());
            // Hata durumunda boş listelerle veya hata mesajıyla view'ı döndür
            return view('crm.musteri.index', [
                'musteriler' => collect(), 
                'iller' => collect(),
                'servisDurumlar' => collect(),
                'servisDurumSorular' => collect(),
                'personeller' => collect(),
                'markalar' => collect(),
                'cihazTurleri' => collect(),
            ])->withErrors('Veriler yüklenirken bir sorun oluştu.');
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if ($resp = $this->ensureMusteriManageAccess()) {
            return $resp;
        }
        Log::info('MusteriController@create çağrıldı.'); // Log güncellendi
        try {
            // İller: aktif bölgelere göre filtrele (yoksa tümü)
            $aktifIlCount = \Illuminate\Support\Facades\DB::table('aktif_iller')->count();
            if ($aktifIlCount > 0) {
                $aktifIlIds = \Illuminate\Support\Facades\DB::table('aktif_iller')->pluck('il_id');
                $iller = Il::whereIn('id', $aktifIlIds)->orderBy('ad')->get(['id', 'ad']);
            } else {
                $iller = Il::orderBy('ad')->get(['id', 'ad']);
            }
            Log::info('İller başarıyla çekildi.', ['count' => $iller->count()]);
            return view('crm.musteri.create', compact('iller'));
        } catch (\Exception $e) {
            Log::error('İller çekilirken hata oluştu: ' . $e->getMessage());
            // Hata durumunda boş iller listesi ile view'ı döndür veya hata mesajı göster
            return view('crm.musteri.create', ['iller' => collect()])->withErrors('İller yüklenirken bir sorun oluştu.');
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if ($resp = $this->ensureMusteriManageAccess()) {
            return $resp;
        }
        Log::info('MusteriController@store çağrıldı.', ['request_data' => $this->sanitizeLogData($request->all())]);

        $validator = Validator::make($request->all(), [
            'musteri_tip' => 'required|in:0,1',
            'kayit_tarihi' => 'required|date_format:Y-m-d',
            'kayit_saati' => 'required|date_format:H:i',
            'ad' => 'required|string|max:255',
            'tel1' => 'required|string|max:20',
            'tel2' => 'nullable|string|max:20',
            'il_id' => 'required|exists:iller,id',
            'ilce_id' => 'required|exists:ilceler,id',
            'adres' => 'required|string|max:500',
            'vdaire' => 'required_if:musteri_tip,1|nullable|string|max:255',
            'vno' => 'required_if:musteri_tip,1|nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            Log::warning('Müşteri kaydetme validasyon hatası.', ['errors' => $validator->errors()->toArray()]);
            // AJAX isteği olduğu için JSON yanıtı dön
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $dataToCreate = [
                'musteri_tip' => $request->input('musteri_tip'),
                'tarih' => $request->input('kayit_tarihi'),
                'saat' => $request->input('kayit_saati'),
                'ad' => $request->input('ad'),
                'tel1' => $request->input('tel1'),
                'tel2' => $request->input('tel2'),
                'il_id' => $request->input('il_id'),
                'ilce_id' => $request->input('ilce_id'),
                'adres' => $request->input('adres'),
                'vdaire' => $request->input('musteri_tip') == '1' ? $request->input('vdaire') : null,
                'vno' => $request->input('musteri_tip') == '1' ? $request->input('vno') : null,
                'personel_id' => auth()->id(), // Giriş yapmış kullanıcının ID'si
                // 'uye_firma_id' => auth()->user()->uye_firma_id ?? null, // Gerekliyse
            ];
            
            Log::info('Müşteri oluşturulacak veri:', $dataToCreate);

            $yeniMusteri = Musteri::create($dataToCreate);
            
            Log::info('Yeni müşteri başarıyla kaydedildi.', ['musteri_id' => $yeniMusteri->id]);
            
            // AJAX isteği olduğu için JSON yanıtı dön
            return response()->json(['success' => true, 'message' => 'Müşteri başarıyla kaydedildi!', 'musteri' => $yeniMusteri]);

        } catch (\Exception $e) {
            Log::error('Müşteri kaydedilirken hata oluştu: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'data' => $this->sanitizeLogData($request->all())
                ]);
            // AJAX isteği olduğu için JSON yanıtı dön
            return response()->json(['success' => false, 'message' => 'Müşteri kaydedilirken bir sunucu hatası oluştu.'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Musteri $musteri) // Route Model Binding kullanılıyor
    {
        // Belirli bir müşteriyi gösterme işlemleri
        // return view('musteriler.show', compact('musteri')); // View döndürme (henüz oluşturulmadı)
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Musteri $musteri)
    {
        // Müşteri düzenleme formunu gösterme işlemleri
        // return view('musteriler.edit', compact('musteri')); // View döndürme (henüz oluşturulmadı)
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $musteriler) // Parametre adını route definition ile eşleştir ({musteriler})
    {
        if ($resp = $this->ensureMusteriManageAccess()) {
            return $resp;
        }
        // ID'yi parametreden al
        $id = $musteriler; 
        Log::info('MusteriController@update çağrıldı.', ['musteri_id' => $id, 'request_data' => $this->sanitizeLogData($request->all())]);
        
        // Modeli manuel olarak bul
        try {
            $musteri = Musteri::findOrFail($id);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning('Müşteri bulunamadı (update)', ['musteri_id' => $id]);
            return response()->json(['success' => false, 'message' => 'Müşteri bulunamadı.'], 404);
        }

        // Validasyon
        $validator = Validator::make($request->all(), [
            'musteri_tip' => 'required|in:0,1',
            // 'kayit_tarihi' ve 'kayit_saati' genellikle güncellenmez, formda yoksa validasyondan çıkar
            'ad' => 'required|string|max:255',
            'tel1' => 'required|string|max:20',
            'tel2' => 'nullable|string|max:20',
            'il_id' => 'required|exists:iller,id',
            'ilce_id' => 'required|exists:ilceler,id',
            'adres' => 'required|string|max:500',
            'vdaire' => 'required_if:musteri_tip,1|nullable|string|max:255',
            'vno' => 'required_if:musteri_tip,1|nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            Log::warning('Müşteri güncelleme validasyon hatası.', ['musteri_id' => $musteri->id, 'errors' => $validator->errors()->toArray()]);
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }
        
        try {
            // Sadece validate edilen ve fillable olan verileri al
            $dataToUpdate = $validator->validated();
            $oncekiIletisim = $musteri->only(['ad', 'tel1', 'tel2']);

             // Vergi alanlarını müşteri tipine göre ayarla (eğer bireysel ise null yap)
            if ($dataToUpdate['musteri_tip'] == 0) {
                $dataToUpdate['vdaire'] = null;
                $dataToUpdate['vno'] = null;
            }

            Log::info('Müşteri güncellenecek veri:', $dataToUpdate);

            $guncellendi = $musteri->update($dataToUpdate);

            if ($guncellendi) {
                $musteri->refresh();
                SettingsAuditLog::recordMusteriIletisimDegisikligi(
                    (int) $musteri->id,
                    $oncekiIletisim,
                    $musteri->only(['ad', 'tel1', 'tel2']),
                    Auth::id()
                );
                Log::info('Müşteri başarıyla güncellendi.', ['musteri_id' => $musteri->id]);
                 // Güncellenmiş veriyi ilişkilerle birlikte dön (getDetay gibi)
                 $musteri->load(['ilce:id,ad,il_id', 'ilce.il:id,ad', 'servisler:id,musteri_id,servis_durum_id,created_at', 'servisler.servisDurum:id,ad']);
                return response()->json(['success' => true, 'message' => 'Müşteri başarıyla güncellendi!', 'musteri' => $musteri]);
            } else {
                Log::warning('Müşteri güncellenemedi (update metodu false döndü)', ['musteri_id' => $musteri->id]);
                return response()->json(['success' => false, 'message' => 'Müşteri güncellenemedi.'], 500);
            }

        } catch (\Exception $e) {
            Log::error('Müşteri güncellenirken hata oluştu: ' . $e->getMessage(), [
                'musteri_id' => $musteri->id,
                'trace' => $e->getTraceAsString(),
                'data' => $this->sanitizeLogData($request->all())
                ]);
            return response()->json(['success' => false, 'message' => 'Müşteri güncellenirken bir sunucu hatası oluştu.'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Musteri $musteri)
    {
        // Müşteriyi silme işlemleri
        // $musteri->delete();
        // return redirect()->route('musteriler.index'); // Yönlendirme
    }

    /**
     * Belirli bir ile ait ilçeleri JSON olarak döndürür.
     */
    public function getIlceler(Request $request, $il_id)
    {
        if ($resp = $this->ensureMusteriLookupAccess()) {
            return $resp;
        }
         Log::info('MusteriController@getIlceler çağrıldı.', ['il_id' => $il_id]);
        try {
            // il_id'nin geçerli bir sayı olup olmadığını kontrol et
            if (!is_numeric($il_id) || $il_id <= 0) {
                 Log::warning('Geçersiz il_id.', ['il_id' => $il_id]);
                return response()->json(['error' => 'Geçersiz İl ID'], 400);
            }
            // Aktif ilçe yapılandırması varsa sadece aktif ilçeleri döndür
            $aktifIlceCount = \Illuminate\Support\Facades\DB::table('aktif_ilceler')->count();
            if ($aktifIlceCount > 0) {
                $aktifIlceIds = \Illuminate\Support\Facades\DB::table('aktif_ilceler')->where('il_id', $il_id)->pluck('ilce_id');
                $ilceler = Ilce::where('il_id', $il_id)
                    ->whereIn('id', $aktifIlceIds)
                    ->orderBy('ad')
                    ->get(['id', 'ad']);
            } else {
                $ilceler = Ilce::where('il_id', $il_id)->orderBy('ad')->get(['id', 'ad']);
            }
            Log::info('İlçeler başarıyla çekildi.', ['il_id' => $il_id, 'count' => $ilceler->count()]);
            return response()->json($ilceler);
        } catch (\Exception $e) {
             Log::error('İlçeler çekilirken hata oluştu: ' . $e->getMessage(), ['il_id' => $il_id]);
            return response()->json(['error' => 'İlçeler getirilirken bir hata oluştu.'], 500);
        }
    }

    /**
     * Belirli bir müşterinin detaylarını JSON olarak döndürür.
     */
    public function getDetay(Request $request, $id) // Route model binding yerine ID kullandık
    {
        if ($resp = $this->ensureMusteriManageAccess()) {
            return $resp;
        }
        Log::info('MusteriController@getDetay çağrıldı', ['musteri_id' => $id]);
        try {
            $musteri = Musteri::with(['ilce:id,ad,il_id', 'ilce.il:id,ad', 'servisler:id,musteri_id,servis_durum_id,created_at', 'servisler.servisDurum:id,ad']) // İlişkili verileri yükle
                              ->findOrFail($id); // ID ile bul veya 404 döndür
            
            Log::info('Müşteri detayları başarıyla çekildi', ['musteri_id' => $id]);
            return response()->json($musteri);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning('Müşteri bulunamadı (getDetay)', ['musteri_id' => $id]);
            return response()->json(['error' => 'Müşteri bulunamadı.'], 404);
        } catch (\Exception $e) {
            Log::error('Müşteri detayları getirilirken hata oluştu: ' . $e->getMessage(), ['musteri_id' => $id]);
            return response()->json(['error' => 'Müşteri detayları getirilirken bir sunucu hatası oluştu.'], 500);
        }
    }

    /**
     * Yeni Servis modalı için müşteri autocomplete araması.
     */
    public function search(Request $request)
    {
        $q = trim((string) $request->input('q', ''));
        if (mb_strlen($q) < 2) {
            return response()->json(['results' => []]);
        }

        $limit = (int) $request->input('limit', 20);
        if ($limit < 1) {
            $limit = 20;
        }
        $limit = min($limit, 30);

        $numericSearch = preg_replace('/\D+/', '', $q);
        $like = '%' . $q . '%';

        $query = Musteri::query()
            ->select(['id', 'ad', 'tel1', 'tel2'])
            ->where(function ($builder) use ($like, $numericSearch) {
                $builder->where('ad', 'like', $like)
                    ->orWhere('tel1', 'like', $like)
                    ->orWhere('tel2', 'like', $like);

                if ($numericSearch !== null && $numericSearch !== '') {
                    $phoneLike = '%' . $numericSearch . '%';
                    $normalizeTel = function ($column) {
                        return "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE($column,' ',''),'-',''),'/',''),'(',''),')','')";
                    };
                    $builder->orWhereRaw($normalizeTel('tel1') . ' LIKE ?', [$phoneLike])
                        ->orWhereRaw($normalizeTel('tel2') . ' LIKE ?', [$phoneLike]);
                }
            })
            ->orderBy('ad')
            ->limit($limit);

        $results = $query->get()->map(function (Musteri $musteri) {
            $tel = $musteri->tel1 ?: ($musteri->tel2 ?: '');
            return [
                'id' => $musteri->id,
                'ad' => $musteri->ad,
                'tel' => $tel,
                'label' => $musteri->ad . ($tel !== '' ? ' (' . $tel . ')' : ''),
            ];
        })->values();

        return response()->json(['results' => $results]);
    }

    /**
     * Tüm illeri JSON olarak döndürür.
     */
    public function getIller(Request $request)
    {
        if ($resp = $this->ensureMusteriLookupAccess()) {
            return $resp;
        }
        Log::info('MusteriController@getIller çağrıldı.');
        try {
            // Aktif il yapılandırması varsa sadece aktifle işaretlenen illeri döndür
            $aktifIlCount = \Illuminate\Support\Facades\DB::table('aktif_iller')->count();
            if ($aktifIlCount > 0) {
                $aktifIlIds = \Illuminate\Support\Facades\DB::table('aktif_iller')->pluck('il_id');
                $iller = Il::whereIn('id', $aktifIlIds)->orderBy('ad')->get(['id', 'ad']);
            } else {
                $iller = Il::orderBy('ad')->get(['id', 'ad']);
            }
            return response()->json($iller);
        } catch (\Exception $e) {
            Log::error('Tüm iller çekilirken hata oluştu: ' . $e->getMessage());
            return response()->json(['error' => 'İller getirilirken bir hata oluştu.'], 500);
        }
    }
} 