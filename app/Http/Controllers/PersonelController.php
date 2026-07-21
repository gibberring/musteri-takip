<?php

namespace App\Http\Controllers;

use App\Models\Personel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth; // Eklendi
use App\Models\TnmPersonelPozisyon; // Eklendi
use App\Models\Servis; // Eklendi
use App\Models\Kasa;
use Carbon\Carbon; // Eklendi
use Illuminate\Support\Str;
use App\Models\ServisDurum; // Global modal verileri için
use App\Models\ServisDurumSoru; // Global modal verileri için
use App\Models\TnmMarka; // Global modal verileri için
use App\Models\TnmCihazTuru; // Global modal verileri için
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PersonelController extends Controller
{
    /**
     * Şifre değişince ilgili personele ait web oturumlarını sonlandırır (session driver: database).
     * Kim kendi şifresini değiştiriyorsa mevcut oturumu hariç tutulur; diğer cihazlar düşer.
     */
    private function invalidateDatabaseSessionsForPersonelId(int $personelId): void
    {
        if (config('session.driver') !== 'database') {
            return;
        }
        $table = config('session.table', 'sessions');
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'user_id')) {
            return;
        }
        try {
            $q = DB::table($table)->where('user_id', $personelId);
            if (Auth::check() && (int) Auth::id() === $personelId) {
                $sid = session()->getId();
                if ($sid !== '' && $sid !== null) {
                    $q->where('id', '!=', $sid);
                }
            }
            $q->delete();
        } catch (\Throwable $e) {
            Log::warning('Şifre değişikliği sonrası oturum silinemedi.', [
                'personel_id' => $personelId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function sanitizeLogData(array $data): array
    {
        foreach (['sifre', 'sifre_confirmation', 'password', 'password_confirmation'] as $key) {
            if (array_key_exists($key, $data)) {
                $data[$key] = '[REDACTED]';
            }
        }
        return $data;
    }
    private function ensurePersonelManageAccess()
    {
        $loggedInUser = Auth::user();
        $allowedPozisyonIds = [1071, 1080]; // Patron, Muhasebe
        if (!$loggedInUser || !in_array((int) $loggedInUser->poz_id, $allowedPozisyonIds, true)) {
            $req = request();
            if ($req->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Personel yönetimine erişim yetkiniz yok.'], 403);
            }
            abort(403, 'Bu sayfaya erişim yetkiniz bulunmamaktadır.');
        }

        return null;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($resp = $this->ensurePersonelManageAccess()) {
            return $resp;
        }

        Log::info('PersonelController@index çağrıldı.');
        try {
            if ($request->ajax() || $request->expectsJson()) {
                $draw = (int) $request->input('draw', 1);
                $start = (int) $request->input('start', 0);
                $length = (int) $request->input('length', 50);
                $searchValue = trim((string) $request->input('search.value', ''));

                $baseQuery = Personel::with('pozisyon');
                $recordsTotal = (clone $baseQuery)->count();

                if ($searchValue !== '') {
                    $like = '%' . $searchValue . '%';
                    $baseQuery->where(function ($q) use ($like) {
                        $q->where('personel.id', 'like', $like)
                          ->orWhere('personel.ad', 'like', $like)
                          ->orWhere('personel.nick', 'like', $like)
                          ->orWhere('personel.email', 'like', $like)
                          ->orWhere('personel.tel1', 'like', $like)
                          ->orWhere('personel.tel2', 'like', $like)
                          ->orWhereHas('pozisyon', function ($pq) use ($like) {
                              $pq->where('ad', 'like', $like);
                          });
                    });
                }

                $recordsFiltered = (clone $baseQuery)->count();
                $personeller = (clone $baseQuery)
                    ->orderByDesc('id')
                    ->skip($start)
                    ->take($length)
                    ->get();

                $data = $personeller->map(function ($personel) use ($request) {
                    $profileUrl = route('personel.teknisyenProfil', ['personel' => $personel->id]);
                    $uyelik = $personel->aktif == 1
                        ? '<span class="badge bg-soft-success text-success">Aktif</span>'
                        : '<span class="badge bg-soft-danger text-danger">Pasif</span>';
                    $durum = $personel->mesai_basladimi == 1
                        ? '<span class="badge bg-soft-success text-success">Mesaide</span>'
                        : '<span class="badge bg-soft-danger text-danger">Çalışmıyor</span>';

                    $item = [
                        'personel_id' => $personel->id,
                        'id' => $personel->id,
                        'id_html' => '<a href="' . e($profileUrl) . '" class="fw-bold profile-link">#' . e($personel->id) . '</a>',
                        'ad' => e($personel->ad ?? ''),
                        'pozisyon' => e(optional($personel->pozisyon)->ad ?? 'N/A'),
                        'uyelik' => $uyelik,
                        'durum' => $durum,
                    ];
                    if ($request->expectsJson() && !$request->ajax()) {
                        $item['aktif'] = (int) $personel->aktif;
                        $item['mesai_basladimi'] = (int) ($personel->mesai_basladimi ?? 0);
                    }
                    return $item;
                })->values();

                return response()->json([
                    'draw' => $draw,
                    'recordsTotal' => $recordsTotal,
                    'recordsFiltered' => $recordsFiltered,
                    'data' => $data,
                ]);
            }

            $personeller = Personel::with('pozisyon')->latest()->get();
            Log::info('Personeller çekildi.', ['count' => $personeller->count()]);
            return view('crm.personel.index', compact('personeller'));
        } catch (\Exception $e) {
             Log::error('Personel listesi çekilirken hata: ' . $e->getMessage());
             return view('crm.personel.index', ['personeller' => collect()])->withErrors('Personel verileri yüklenirken bir sorun oluştu.');
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Yeni personel oluşturma formunu gösterme
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if ($resp = $this->ensurePersonelManageAccess()) {
            return $resp;
        }

        Log::info('PersonelController@store çağrıldı.', ['data' => $this->sanitizeLogData($request->all())]);

        $rules = [
            'ad' => 'required|string|max:255',
            'nick' => 'required|string|max:255|unique:personel,nick',
            'poz_id' => 'required|exists:tnm_personel_pozisyon,id',
            'aktif' => 'required|boolean',
            'sifre' => 'required|string|min:6|confirmed', // confirmed -> sifre_confirmation ile eşleşmeli
            'email' => 'nullable|string|email|max:255|unique:personel,email',
            'tel1' => 'nullable|string|max:50',
            'tel2' => 'nullable|string|max:50',
            'is_basi_tarih' => 'nullable|date_format:Y-m-d',
            'il_id' => 'nullable|exists:iller,id',
            'ilce_id' => 'nullable|exists:ilceler,id',
            'adres' => 'nullable|string|max:500',
            'servis_fis_kullanabilir' => 'nullable|boolean',
            'mesai_basladimi' => 'nullable|boolean',
            'e_fis_verebilir' => 'nullable|boolean',
            'yazici_icerigi' => 'nullable|string',
            'fis_firma' => 'nullable|string|max:500',
            'fis_tel' => 'nullable|string|max:50',
            'fis_adres' => 'nullable|string|max:500',
            'two_factor_required' => 'nullable|boolean',
            'calisma_sekli_type' => 'nullable|string|in:50_50,60_40,55_45,65_35,0_100',
            'calisma_sekli_adet_tutar' => 'nullable|numeric|min:0',
            'operator_kazanc_tutar' => 'nullable|numeric|min:0',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            Log::warning('Yeni personel validasyon hatası.', ['errors' => $validator->errors()->toArray()]);
            return response()->json(['success' => false, 'message' => 'Doğrulama hatası!', 'errors' => $validator->errors()], 422);
        }

        try {
            $dataToCreate = $request->except('sifre_confirmation', '_token');
            $dataToCreate['sifre'] = Hash::make($request->input('sifre'));
            // uye_firma_id ve kaydeden_personel_id gibi alanlar gerekiyorsa burada atanabilir.
            // Örnek: $dataToCreate['uye_firma_id'] = Auth::user()->uye_firma_id;
            // Örnek: $dataToCreate['kaydeden_personel_id'] = Auth::id();

            if (($dataToCreate['calisma_sekli_type'] ?? null) === 'adetli') {
                $dataToCreate['calisma_sekli_type'] = '0_100';
                $dataToCreate['calisma_sekli_adet_tutar'] = null;
            }
            if (($dataToCreate['poz_id'] ?? null) != 1077) {
                $dataToCreate['calisma_sekli_type'] = null;
                $dataToCreate['calisma_sekli_adet_tutar'] = null;
            }
            if (($dataToCreate['poz_id'] ?? null) != 1073) {
                $dataToCreate['operator_kazanc_tutar'] = null;
            }
            $personel = Personel::create($dataToCreate);
            Log::info('Yeni personel başarıyla oluşturuldu.', ['personel_id' => $personel->id]);

            // Yeni eklenen personelin pozisyon adını da yanıtla döndür
            $personel->load('pozisyon');
            $pozisyonAdi = $personel->pozisyon ? $personel->pozisyon->ad : 'N/A';

            return response()->json([
                'success' => true,
                'message' => 'Yeni personel başarıyla kaydedildi!',
                'personel' => $personel, // Yeni personel verisi
                'pozisyon_adi' => $pozisyonAdi // DataTable'a eklemek için pozisyon adı
            ]);

        } catch (\Exception $e) {
            Log::error('Yeni personel kaydedilirken hata: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'Yeni personel kaydedilirken bir sunucu hatası oluştu.'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Personel $personel) // Route Model Binding
    {
        // Belirli bir personeli gösterme (genellikle tam sayfa görünümü için)
        // AJAX modal için getDetay daha uygun olabilir
    }

    /**
     * Belirli bir personelin detaylarını JSON olarak döndürür (Modal için).
     */
    public function getDetay(Personel $personel)
    {
        if ($resp = $this->ensurePersonelManageAccess()) {
            return $resp;
        }
        Log::info('PersonelController@getDetay çağrıldı.', ['personel_id' => $personel->id]);
        try {
            $personel->load('pozisyon');
            $allowedPozisyonIds = [1071, 1073, 1076, 1077, 1080];
            $tumPozisyonlar = \App\Models\TnmPersonelPozisyon::whereIn('id', $allowedPozisyonIds)
                ->orderBy('ad')
                ->get(['id', 'ad']);
            $tumIller = \App\Models\Il::orderBy('ad')->get(['id', 'ad']);
            
            $kayitTarihiFormatted = $personel->created_at ? $personel->created_at->format('d-m-Y H:i') : 'N/A';

            $ilceler = collect();
            if ($personel->il_id) {
                $ilceler = \App\Models\Ilce::where('il_id', $personel->il_id)->orderBy('ad')->get(['id', 'ad']);
            }

            return response()->json([
                'personel' => $personel->toArray() + ['kayit_tarihi_formatted' => $kayitTarihiFormatted],
                'pozisyonlar' => $tumPozisyonlar,
                'iller' => $tumIller,
                'ilceler' => $ilceler
            ]);
        } catch (\Exception $e) {
            Log::error('Personel detayı çekilirken hata: ' . $e->getMessage(), ['personel_id' => $personel->id]);
            return response()->json(['error' => 'Personel detayları alınamadı.'], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Personel $personel)
    {
        // Personel düzenleme formunu gösterme
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Personel $personel)
    {
        if ($resp = $this->ensurePersonelManageAccess()) {
            return $resp;
        }
        Log::info('PersonelController@update çağrıldı.', ['personel_id' => $personel->id, 'data' => $this->sanitizeLogData($request->all())]);

        $rules = [
            'ad' => 'required|string|max:255',
            'nick' => ['required', 'string', 'max:255', Rule::unique('personel')->ignore($personel->id)],
            'poz_id' => 'required|exists:tnm_personel_pozisyon,id',
            'aktif' => 'required|boolean',
            'email' => ['nullable', 'string', 'email', 'max:255', Rule::unique('personel')->ignore($personel->id)],
            'tel1' => 'nullable|string|max:50',
            'tel2' => 'nullable|string|max:50',
            'is_basi_tarih' => 'nullable|date_format:Y-m-d',
            'il_id' => 'nullable|exists:iller,id',
            'ilce_id' => 'nullable|exists:ilceler,id', // il_id'ye bağlı kontrol de eklenebilir
            'adres' => 'nullable|string|max:500',
            'servis_fis_kullanabilir' => 'nullable|boolean',
            'mesai_basladimi' => 'nullable|boolean',
            'e_fis_verebilir' => 'nullable|boolean',
            'yazici_icerigi' => 'nullable|string',
            'fis_firma' => 'nullable|string|max:500',
            'fis_tel' => 'nullable|string|max:50',
            'fis_adres' => 'nullable|string|max:500',
            'sifre' => 'nullable|string|min:6|confirmed', // confirmed -> sifre_confirmation ile eşleşmeli
            'two_factor_required' => 'nullable|boolean',
            'calisma_sekli_type' => 'nullable|string|in:50_50,60_40,55_45,65_35,0_100',
            'calisma_sekli_adet_tutar' => 'nullable|numeric|min:0',
            'operator_kazanc_tutar' => 'nullable|numeric|min:0',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            Log::warning('Personel güncelleme validasyon hatası.', ['personel_id' => $personel->id, 'errors' => $validator->errors()->toArray()]);
            return response()->json(['success' => false, 'message' => 'Doğrulama hatası!', 'errors' => $validator->errors()], 422);
        }

        try {
            $dataToUpdate = $request->except('sifre', 'sifre_confirmation', '_token', '_method', 'personel_id');

            // Mesai/aktif tutarlılığı: Mesai 1 ise ikisi de 1; herhangi biri 0 ise ikisi de 0 (önce açma, sonra kapatma kontrolü).
            $aktif = isset($dataToUpdate['aktif']) ? (int) $dataToUpdate['aktif'] : (int) $personel->aktif;
            $mesaiBasladimi = isset($dataToUpdate['mesai_basladimi']) ? (int) $dataToUpdate['mesai_basladimi'] : (int) ($personel->mesai_basladimi ?? 0);
            if ($mesaiBasladimi === 1) {
                $dataToUpdate['aktif'] = 1;
                $dataToUpdate['mesai_basladimi'] = 1;
            } elseif ($aktif === 0 || $mesaiBasladimi === 0) {
                $dataToUpdate['aktif'] = 0;
                $dataToUpdate['mesai_basladimi'] = 0;
            }

            // Eğer şifre alanı doluysa ve geçerliyse, hash'leyip güncelle
            if ($request->filled('sifre')) {
                $dataToUpdate['sifre'] = Hash::make($request->input('sifre'));
            }

            if (($dataToUpdate['calisma_sekli_type'] ?? null) === 'adetli') {
                $dataToUpdate['calisma_sekli_type'] = '0_100';
                $dataToUpdate['calisma_sekli_adet_tutar'] = null;
            }
            if (($dataToUpdate['poz_id'] ?? $personel->poz_id) != 1077) {
                $dataToUpdate['calisma_sekli_type'] = null;
                $dataToUpdate['calisma_sekli_adet_tutar'] = null;
            }
            if (($dataToUpdate['poz_id'] ?? $personel->poz_id) != 1073) {
                $dataToUpdate['operator_kazanc_tutar'] = null;
            }
            $sifreDegisti = $request->filled('sifre');
            $personel->update($dataToUpdate);
            if ($sifreDegisti) {
                $this->invalidateDatabaseSessionsForPersonelId((int) $personel->id);
            }
            Log::info('Personel başarıyla güncellendi.', ['personel_id' => $personel->id]);

            // Güncellenmiş personeli ilişkileriyle birlikte döndür (JavaScript'te modalı yenilemek için)
            $personel->load('pozisyon');
            $allowedPozisyonIds = [1071, 1073, 1076, 1077, 1080];
            $tumPozisyonlar = \App\Models\TnmPersonelPozisyon::whereIn('id', $allowedPozisyonIds)
                ->orderBy('ad')
                ->get(['id', 'ad']);
            $tumIller = \App\Models\Il::orderBy('ad')->get(['id', 'ad']);
            $kayitTarihiFormatted = $personel->created_at ? $personel->created_at->format('d-m-Y H:i') : 'N/A';
            $ilceler = collect();
            if ($personel->il_id) {
                $ilceler = \App\Models\Ilce::where('il_id', $personel->il_id)->orderBy('ad')->get(['id', 'ad']);
            }

            return response()->json([
                'success' => true,
                'message' => 'Personel bilgileri başarıyla güncellendi!',
                'personel_data' => [
                    'personel' => $personel->toArray() + ['kayit_tarihi_formatted' => $kayitTarihiFormatted],
                    'pozisyonlar' => $tumPozisyonlar, // Modalın pozisyon dropdown'ını yeniden doldurmak için
                    'iller' => $tumIller,       // Modalın il dropdown'ını yeniden doldurmak için
                    'ilceler' => $ilceler     // Modalın ilçe dropdown'ını yeniden doldurmak için
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Personel güncellenirken hata: ' . $e->getMessage(), ['personel_id' => $personel->id, 'trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'Personel güncellenirken bir sunucu hatası oluştu.'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Personel $personel)
    {
        if ($resp = $this->ensurePersonelManageAccess()) {
            return $resp;
        }
        // Personeli silme
    }

    /**
     * Yeni personel modalı için gerekli form verilerini (pozisyonlar, iller vb.) döndürür.
     */
    public function getFormData()
    {
        if ($resp = $this->ensurePersonelManageAccess()) {
            return $resp;
        }
        Log::info('PersonelController@getFormData çağrıldı.');
        try {
            $loggedInUser = Auth::user();
            $idariIslerPozisyonId = 1076; // İdari İşler pozisyon ID'si
            if ($loggedInUser && $loggedInUser->poz_id === $idariIslerPozisyonId) {
                Log::warning('Yetkisiz personel form verisi erişim denemesi (İdari İşler).', ['user_id' => $loggedInUser->id]);
                return response()->json(['success' => false, 'message' => 'Bu sayfaya erişim yetkiniz yok.'], 403);
            }

            $allowedPozisyonIds = [1071, 1073, 1076, 1077, 1080];
            $pozisyonlar = \App\Models\TnmPersonelPozisyon::whereIn('id', $allowedPozisyonIds)
                ->orderBy('ad')
                ->get(['id', 'ad']);
            $iller = \App\Models\Il::orderBy('ad')->get(['id', 'ad']);
            
            return response()->json([
                'success' => true,
                'pozisyonlar' => $pozisyonlar,
                'iller' => $iller
            ]);
        } catch (\Exception $e) {
            Log::error('Personel form verileri çekilirken hata: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Form verileri yüklenemedi.'], 500);
        }
    }

    public function showTeknisyenProfil(Personel $personel)
    {
        $loggedInUser = Auth::user();
        $patronPozisyonId = 1071;
        $tsrnTeknisyenPozisyonId = 1077;
        $operatorPozisyonId = 1073;

        // Yetkilendirme kontrolü: Patron, TŞRN Teknisyen ve Operatör erişebilir.
        if (!($loggedInUser && in_array((int) $loggedInUser->poz_id, [$patronPozisyonId, $tsrnTeknisyenPozisyonId, $operatorPozisyonId], true))) {
            abort(403, 'Bu profil sayfasına erişim yetkiniz bulunmamaktadır.');
        }

        // Eğer giriş yapan kullanıcı bir TŞRN Teknisyen ise, sadece kendi profilini görebilmeli.
        if ($loggedInUser->poz_id === $tsrnTeknisyenPozisyonId && $loggedInUser->id !== $personel->id) {
            abort(403, 'Sadece kendi profilinizi görüntüleyebilirsiniz.');
        }

        Log::info('PersonelController@showTeknisyenProfil çağrıldı.', ['personel_id' => $personel->id]);

        return $this->renderPersonelProfil($personel, 'crm.personeller.teknisyen-profil', 'Teknisyen profil verileri yüklenirken bir sorun oluştu.');
    }

    public function showOperatorProfil(Personel $personel)
    {
        $loggedInUser = Auth::user();
        $patronPozisyonId = 1071;
        $operatorPozisyonId = 1073;

        if (!($loggedInUser && in_array((int) $loggedInUser->poz_id, [$patronPozisyonId, $operatorPozisyonId], true))) {
            abort(403, 'Bu profil sayfasına erişim yetkiniz bulunmamaktadır.');
        }

        if ((int) $loggedInUser->poz_id === $operatorPozisyonId && $loggedInUser->id !== $personel->id) {
            abort(403, 'Sadece kendi profilinizi görüntüleyebilirsiniz.');
        }

        Log::info('PersonelController@showOperatorProfil çağrıldı.', ['personel_id' => $personel->id]);

        return $this->renderPersonelProfil($personel, 'crm.personeller.operator-profil', 'Operatör profil verileri yüklenirken bir sorun oluştu.');
    }

    private function renderPersonelProfil(Personel $personel, string $viewName, string $errorMessage)
    {
        try {
            $loggedInUser = Auth::user();
            $personel->load('pozisyon'); // Personelin pozisyon bilgisini yükle

            $today = Carbon::today();
            $yesterday = Carbon::yesterday();
            $dayBeforeYesterday = Carbon::today()->subDays(2);

            $todayServiceCount = Servis::where('personel_id', $personel->id)->whereDate('created_at', $today)->count();
            $yesterdayServiceCount = Servis::where('personel_id', $personel->id)->whereDate('created_at', $yesterday)->count();
            $dayBeforeYesterdayServiceCount = Servis::where('personel_id', $personel->id)->whereDate('created_at', $dayBeforeYesterday)->count();

            // Son 7 günün servis adetleri için verileri hazırla (Toplam, Atölyeye Alınan, İptal Edilen)
            $serviceTotalData = [];
            $serviceAtolyeData = []; // Yeni: Atölyeye Alınan
            $serviceIptalData = []; // Yeni: İptal Edilen
            $tekrarArizaData = [];
            $labels = []; // Bu, hem servis hem de kasa grafikleri için kullanılacak

            for ($i = 6; $i >= 0; $i--) {
                $date = Carbon::now()->subDays($i);
                $serviceTotalData[] = Servis::where('personel_id', $personel->id)
                                            ->whereDate('created_at', $date)
                                            ->count();
                $labels[] = $date->format('d.m'); // Tarih etiketlerini doldur

                // Atölyeye alınan servisleri say (servis_durum_id == 2 varsayalım)
                $serviceAtolyeData[] = Servis::where('personel_id', $personel->id)
                                            ->whereDate('created_at', $date)
                                            ->where('servis_durum_id', 9100) // Atölyeye Alındı
                                            ->count();
                
                // İptal edilen servisleri say (servis_durum_id == 4 varsayalım)
                $serviceIptalData[] = Servis::where('personel_id', $personel->id)
                                           ->whereDate('created_at', $date)
                                           ->where('servis_durum_id', 9104) // Müşteri İptal Etti
                                           ->count();

                $tekrarArizaData[] = Servis::where('personel_id', $personel->id)
                                          ->whereDate('created_at', $date)
                                          ->where('servis_durum_id', 9116) // Tekrar Arıza
                                          ->count();
            }

            // Son 7 günün grafik verilerini hazırla (Kasa Hareketleri)
            $gelirData = [];
            $giderData = [];

            for ($i = 6; $i >= 0; $i--) {
                $date = Carbon::now()->subDays($i);
                // $labels[] = $date->format('d.m'); // Labels zaten yukarıda doldurulduğu için burada tekrar doldurmaya gerek yok

                // Bu teknisyene ait o günkü servislerden gelen gelir ve giderleri hesapla
                $dailyServisler = Servis::where('personel_id', $personel->id)
                                        ->whereDate('created_at', $date)
                                        ->get();

                $dailyGelir = $dailyServisler->sum(function($servis) {
                    return $servis->kasaHareketleri->where('odeme_yonu', 1)->sum('tutar');
                });
                $dailyGider = $dailyServisler->sum(function($servis) {
                    return $servis->kasaHareketleri->where('odeme_yonu', -1)->sum('tutar');
                });

                $gelirData[] = $dailyGelir;
                $giderData[] = $dailyGider;
            }

            $kasaChartData = [
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => 'Gelir',
                        'data' => $gelirData,
                        'backgroundColor' => 'rgba(75, 192, 192, 0.2)',
                        'borderColor' => 'rgba(75, 192, 192, 1)',
                        'borderWidth' => 1
                    ],
                    [
                        'label' => 'Gider',
                        'data' => $giderData,
                        'backgroundColor' => 'rgba(255, 99, 132, 0.2)',
                        'borderColor' => 'rgba(255, 99, 132, 1)',
                        'borderWidth' => 1
                    ]
                ]
            ];

            $gun = Carbon::today()->format('Y-m-d');
            $kasaQuery = Kasa::query()
                ->where(function ($q) use ($personel) {
                    $q->where('personel_id', $personel->id)
                        ->orWhere('ilgili_personel_id', $personel->id)
                        ->orWhereHas('servis', function ($subQ) use ($personel) {
                            $subQ->where('personel_id', $personel->id);
                        });
                })
                ->whereDate('tarih', $gun)
                ->where('gerceklesme', 1)
                ->where(function ($q) {
                    $q->where('silindi', '!=', 1)
                      ->orWhereNull('silindi');
                });

            $gunlukGelir = (clone $kasaQuery)->where('odeme_yonu', 1)->sum('tutar');
            $gunlukGider = (clone $kasaQuery)->where('odeme_yonu', -1)->sum('tutar');

            $calismaSekliType = $personel->calisma_sekli_type ?? null;
            $calismaAdetTutar = $personel->calisma_sekli_adet_tutar ?? null;
            $gunlukTeknisyenPayi = 0;
            $gunlukFirmaPayi = 0;

            $isAdetli = (Str::lower(trim((string) $calismaSekliType)) === 'adetli');
            if ($isAdetli) {
                $adet = Servis::query()
                    ->where('personel_id', $personel->id)
                    ->whereDate('tarih', $gun)
                    ->where(function ($q) {
                        $q->where('silindi', '!=', 1)
                          ->orWhereNull('silindi');
                    })
                    ->count();
                $gunlukFirmaPayi = $adet * (float) $calismaAdetTutar;
            } elseif ($calismaSekliType) {
                preg_match_all('/\d{1,3}/', (string) $calismaSekliType, $matches);
                $sayilar = $matches[0] ?? [];
                if (count($sayilar) >= 2) {
                    $teknisyenYuzde = (int) $sayilar[0];
                    $firmaYuzde = (int) $sayilar[1];
                    $toplamYuzde = $teknisyenYuzde + $firmaYuzde;
                    if ($toplamYuzde > 0 && $toplamYuzde <= 100) {
                        $gunlukTeknisyenPayi = ($gunlukGelir * $teknisyenYuzde) / 100;
                        $gunlukFirmaPayi = ($gunlukGelir * $firmaYuzde) / 100;
                    }
                }
            } else {
                $gunlukFirmaPayi = $gunlukGelir;
            }

            // Yeni: Servis detayları grafiği için verileri hazırla
            $serviceDetailChartData = [
                'labels' => $labels,
                'datasets' => [
                    [
                        'name' => 'Toplam Servis',
                        'data' => $serviceTotalData,
                    ],
                    [
                        'name' => 'Atölye',
                        'data' => $serviceAtolyeData,
                    ],
                    [
                        'name' => 'İptal',
                        'data' => $serviceIptalData,
                    ],
                ]
            ];

            $effectivePersonelId = ($loggedInUser && (int) $loggedInUser->poz_id === 1077)
                ? $loggedInUser->id
                : $personel->id;

            // Sekmeler için servis kayıtlarını çek
            $baseServisQuery = Servis::where('personel_id', $effectivePersonelId)
                                   ->where(function ($q) {
                                       $q->where('silindi', '!=', 1)
                                         ->orWhereNull('silindi');
                                   })
                                   ->with(['musteri', 'marka', 'cihazTuru', 'servisDurum']);

            $bekleyenIslerQuery = (clone $baseServisQuery)->where('servis_durum_id', 9098);
            $bekleyenIsler = $bekleyenIslerQuery->get();

            $parcaGidecekServisler = (clone $baseServisQuery)->where('servis_durum_id', 9103)->get();
            $yarinGidilecekServisler = (clone $baseServisQuery)->where('servis_durum_id', 9477)->get();
            $tekrarArizalar = (clone $baseServisQuery)->where('servis_durum_id', 9116)->get();
            $iptalServisler = (clone $baseServisQuery)->where('servis_durum_id', 9104)->get();
            $fiyatAnlasilmazligi = (clone $baseServisQuery)->where('servis_durum_id', 9106)->get();
            $musteriHaberVerecek = (clone $baseServisQuery)->where('servis_durum_id', 9110)->get();
            $ucretIadeSureci = (clone $baseServisQuery)->where('servis_durum_id', 9524)->get();
            $atolyedeServisler = (clone $baseServisQuery)->where('servis_durum_id', 9100)->get();

            $operatorKazancKayitlar = collect();
            $operatorKazancToplam = 0;
            $operatorKazancAdet = 0;
            $operatorKazancBugunTutar = 0;
            $operatorKazancBugunAdet = 0;
            $operatorKazancDunTutar = 0;
            $operatorKazancDunAdet = 0;
            $operatorKazancAyTutar = 0;
            $operatorKazancAyAdet = 0;
            $operatorKazancBirimTutar = 0;

            if ($viewName === 'crm.personeller.operator-profil') {
                $operatorKazancBirimTutar = (float) ($personel->operator_kazanc_tutar ?? 0);

                $operatorKazancRawQuery = Kasa::query()
                    ->select([
                        'servis_id',
                        DB::raw('MIN(created_at) as kazanım_tarihi'),
                        DB::raw('MIN(id) as first_kasa_id'),
                    ])
                    ->where('personel_id', $personel->id)
                    ->whereNotNull('servis_id')
                    ->where(function ($q) {
                        $q->where('silindi', '!=', 1)
                            ->orWhereNull('silindi');
                    })
                    ->groupBy('servis_id');

                $operatorKazancSummaryQuery = DB::query()->fromSub($operatorKazancRawQuery, 'k');

                $operatorKazancAdet = (clone $operatorKazancSummaryQuery)->count();
                $operatorKazancBugunAdet = (clone $operatorKazancSummaryQuery)
                    ->whereDate('kazanım_tarihi', Carbon::today())
                    ->count();
                $operatorKazancDunAdet = (clone $operatorKazancSummaryQuery)
                    ->whereDate('kazanım_tarihi', Carbon::yesterday())
                    ->count();
                $operatorKazancOncekiGunAdet = (clone $operatorKazancSummaryQuery)
                    ->whereDate('kazanım_tarihi', Carbon::today()->subDays(2))
                    ->count();
                $operatorKazancAyAdet = (clone $operatorKazancSummaryQuery)
                    ->whereYear('kazanım_tarihi', Carbon::today()->year)
                    ->whereMonth('kazanım_tarihi', Carbon::today()->month)
                    ->count();

                $operatorKazancToplam = $operatorKazancAdet * $operatorKazancBirimTutar;
                $operatorKazancBugunTutar = $operatorKazancBugunAdet * $operatorKazancBirimTutar;
                $operatorKazancDunTutar = $operatorKazancDunAdet * $operatorKazancBirimTutar;
                $operatorKazancAyTutar = $operatorKazancAyAdet * $operatorKazancBirimTutar;

                $operatorKazancRows = (clone $operatorKazancSummaryQuery)
                    ->orderByDesc('kazanım_tarihi')
                    ->paginate(20)
                    ->withQueryString();

                $operatorServisIds = $operatorKazancRows->getCollection()->pluck('servis_id')->filter()->unique()->values()->all();
                $operatorKasaIds = $operatorKazancRows->getCollection()->pluck('first_kasa_id')->filter()->unique()->values()->all();

                $operatorServisler = Servis::with(['musteri', 'servisDurum'])
                    ->whereIn('id', $operatorServisIds)
                    ->get()
                    ->keyBy('id');
                $operatorKasalar = Kasa::with('odemeSekli')
                    ->whereIn('id', $operatorKasaIds)
                    ->get()
                    ->keyBy('id');

                $operatorKazancKayitlar = $operatorKazancRows->setCollection(
                    $operatorKazancRows->getCollection()->map(function ($row) use ($operatorServisler, $operatorKasalar) {
                        $kasa = $operatorKasalar->get($row->first_kasa_id);
                        return (object) [
                            'servis_id' => $row->servis_id,
                            'kazanım_tarihi' => $row->kazanım_tarihi,
                            'servis' => $operatorServisler->get($row->servis_id),
                            'odeme_sekli' => $kasa ? $kasa->odemeSekli : null,
                        ];
                    })
                );

                $todayServiceCount = $operatorKazancBugunAdet;
                $yesterdayServiceCount = $operatorKazancDunAdet;
                $dayBeforeYesterdayServiceCount = $operatorKazancOncekiGunAdet;
            }

            // Global modal için gerekli veri setleri (layouts.app -> window.crmData)
            $servisDurumlar = ServisDurum::gorunur()->orderBy('sira')->get(['id', 'ad']);
            $servisDurumSorular = ServisDurumSoru::orderBy('sira')->get();
            $personeller = Personel::where('aktif', 1)->orderBy('ad')->get(['id', 'ad', 'poz_id']);
            $markalar = TnmMarka::orderBy('ad')->get(['id', 'ad']);
            $cihazTurleri = TnmCihazTuru::orderBy('ad')->get(['id', 'ad']);

            return view($viewName, compact(
                'personel',
                'kasaChartData',
                'todayServiceCount',
                'yesterdayServiceCount',
                'dayBeforeYesterdayServiceCount',
                'serviceDetailChartData',
                'labels',
                'tekrarArizaData',
                'gunlukGelir',
                'gunlukGider',
                'gunlukTeknisyenPayi',
                'gunlukFirmaPayi',
                'bekleyenIsler',
                'parcaGidecekServisler',
                'yarinGidilecekServisler',
                'tekrarArizalar',
                'iptalServisler',
                'fiyatAnlasilmazligi',
                'musteriHaberVerecek',
                'ucretIadeSureci',
                'atolyedeServisler',
                'operatorKazancKayitlar',
                'operatorKazancToplam',
                'operatorKazancAdet',
                'operatorKazancBugunTutar',
                'operatorKazancBugunAdet',
                'operatorKazancDunTutar',
                'operatorKazancDunAdet',
                'operatorKazancAyTutar',
                'operatorKazancAyAdet',
                'operatorKazancBirimTutar',
                'servisDurumlar',
                'servisDurumSorular',
                'personeller',
                'markalar',
                'cihazTurleri'
            ));

        } catch (\Exception $e) {
            Log::error($errorMessage . ' ' . $e->getMessage(), ['personel_id' => $personel->id, 'trace' => $e->getTraceAsString()]);
            abort(500, $errorMessage);
        }
    }
} 