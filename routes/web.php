<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MusteriController;
use App\Http\Controllers\ServisController;
use App\Http\Controllers\PersonelController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\IslemLogController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\KasaController;
use App\Http\Controllers\PanelController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\DeletedRecordsController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\TwoFactorController;

Route::get('/', function () {
    return redirect('/panel');
});

// Login formunu göstermek için manuel GET route (Auth::routes()'dan önce)
Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');

// Login işlemini gerçekleştirmek için manuel POST route
Route::post('login', [LoginController::class, 'login'])->middleware('throttle:10,1');

Auth::routes(['login' => false, 'register' => false, 'reset' => false, 'verify' => false]); // Login GET, register, reset, verify kapalı

// 2FA Rotaları
Route::get('/two-factor/setup', [TwoFactorController::class, 'setup'])->name('twofactor.setup')->middleware('auth');
Route::post('/two-factor/setup', [TwoFactorController::class, 'setupVerify'])->name('twofactor.setup.verify')->middleware('auth')->middleware('throttle:5,1');
Route::get('/two-factor/challenge', [TwoFactorController::class, 'challenge'])->name('twofactor.challenge')->middleware('auth');
Route::post('/two-factor/challenge', [TwoFactorController::class, 'challengeVerify'])->name('twofactor.challenge.verify')->middleware('auth')->middleware('throttle:5,1');

// Oturum canlı tutma (Yeni Servis modalı açıkken session süresini yeniler)
Route::get('/session/keepalive', function () {
    return response()->json(['ok' => true]);
})->middleware('auth')->name('session.keepalive');

// Panel route'u
Route::get('/panel', [PanelController::class, 'index'])->middleware('auth')->name('panel');
Route::get('/panel/il-kazanc', [PanelController::class, 'ilKazancTable'])->middleware('auth')->name('panel.ilKazanc');

// Müşteri CRUD rotaları
Route::get('/musteriler/search', [MusteriController::class, 'search'])->middleware('auth')->name('musteriler.search');
Route::resource('musteriler', MusteriController::class)->middleware('auth');
Route::get('/musteriler/{id}/detay', [MusteriController::class, 'getDetay'])->middleware('auth')->name('musteriler.detay');
Route::get('/ilceler/{il_id}', [MusteriController::class, 'getIlceler'])->name('ilceler.get')->middleware('auth');
Route::get('/iller', [MusteriController::class, 'getIller'])->name('iller.get')->middleware('auth');

// Servis CRUD rotaları
Route::get('/servisler/bekleyen-kayitlar', [ServisController::class, 'pendingIndex'])->middleware('auth')->name('servisler.pending');
Route::get('/servisler/bugunku-iptaller', [ServisController::class, 'todayCancellationsIndex'])->middleware('auth')->name('servisler.bugunkuIptaller');
Route::get('/servisler/ulasilamayan-musteriler', [ServisController::class, 'todayUnreachableIndex'])->middleware('auth')->name('servisler.ulasilamayanMusteriler');
Route::get('/servisler/operator-karsilastirma', [ServisController::class, 'operatorComparison'])->middleware('auth')->name('servisler.operatorComparison');
Route::post('/servisler/operator-karsilastirma-data', [ServisController::class, 'operatorComparisonData'])->middleware('auth')->name('servisler.operatorComparison.data');
Route::resource('servisler', ServisController::class)->middleware('auth');
Route::get('/servisler/{servis}/detay', [ServisController::class, 'getDetay'])->middleware('auth')->name('servisler.detay');
Route::put('/servisler/{servis}/durum-guncelle-detayli', [ServisController::class, 'updateDurumDetayli'])->middleware('auth')->name('servisler.durum.update.detayli');
Route::post('/servisler/bulk-durum', [ServisController::class, 'bulkUpdateDurum'])->middleware('auth')->name('servisler.bulkDurum');
Route::post('/servisler/bulk-soft-delete', [ServisController::class, 'bulkSoftDelete'])->middleware('auth')->name('servisler.bulkSoftDelete');
Route::post('/servisler/{servis}/soft-delete', [ServisController::class, 'softDelete'])->name('servisler.softDelete')->middleware('auth');

// Yeni: Belirli bir servise kasa hareketi ekleme
Route::post('/servisler/{servis}/kasa-hareketi-ekle', [ServisController::class, 'addKasaHareketi'])->name('servisler.addKasaHareketi')->middleware('auth');

// Servis Fişi PDF Rotaları
Route::get('/servisler/{servis}/fis/olustur-ve-goster', [ServisController::class, 'generateAndShowServisFisiPdf'])->name('servisler.fis.olusturVeGoster')->middleware('auth');
Route::post('/servisler/{servis}/fis/olustur-ve-goster', [ServisController::class, 'generateAndShowServisFisiPdf'])->name('servisler.fis.olusturVeGoster.post')->middleware('auth');
Route::get('/servisler/fis/{servisFisi}/goster', [ServisController::class, 'showServisFisiPdf'])->name('servisler.fis.goster')->middleware('auth');
Route::get('/servisler/{servis}/fisleri-listele', [ServisController::class, 'listServisFisleri'])->name('servisler.fis.listele')->middleware('auth');

// Yeni: Servis Resimleri Rotaları
Route::post('/servisler/{servis}/resim-yukle', [ServisController::class, 'uploadServisResmi'])->name('servisler.resim.yukle')->middleware('auth');
Route::get('/servis-resimleri/{servisResmi}/goster', [ServisController::class, 'getServisResmi'])->name('servisler.resim.goster')->middleware('auth'); // Auth middleware eklendi

// Personel CRUD rotaları
Route::get('/personeller', [PersonelController::class, 'index'])->name('personel.index')->middleware('auth');
Route::get('/personeller/{personel}/get-detay', [PersonelController::class, 'getDetay'])->name('personel.getDetay')->middleware('auth');
Route::put('/personeller/{personel}', [PersonelController::class, 'update'])->name('personeller.update')->middleware('auth');
Route::post('/personeller', [PersonelController::class, 'store'])->name('personeller.store')->middleware('auth');
Route::get('/personel-form-data', [PersonelController::class, 'getFormData'])->name('personel.formData')->middleware('auth');
Route::get('/personeller/{personel}/profil', [PersonelController::class, 'showTeknisyenProfil'])->name('personel.teknisyenProfil')->middleware('auth');
Route::get('/personeller/{personel}/operator-profil', [PersonelController::class, 'showOperatorProfil'])->name('personel.operatorProfil')->middleware('auth');

// Kasa rotaları
Route::get('/genelkasa/export', [KasaController::class, 'exportGenelKasa'])->name('kasa.exportGenelKasa')->middleware('auth');
Route::resource('genelkasa', KasaController::class)
    ->names('kasa')
    ->parameters(['genelkasa' => 'kasa'])
    ->whereNumber('kasa')
    ->middleware('auth');
Route::get('/kasa', [KasaController::class, 'technicianDailySummary'])
    ->name('kasa.technicianDaily')
    ->middleware('auth');
Route::get('/kasa/bekleyen-odemeler', [KasaController::class, 'pendingTechnicianSummary'])
    ->name('kasa.pending')
    ->middleware('auth');
Route::post('/kasa/bekleyen-odemeler', [KasaController::class, 'pendingTechnicianSummary'])
    ->name('kasa.pending.post')
    ->middleware('auth');
Route::post('/kasa/bekleyen-odemeler/teknisyen-detay', [KasaController::class, 'pendingTechnicianDetail'])
    ->name('kasa.pending.detail')
    ->middleware('auth');
Route::post('/kasa', [KasaController::class, 'technicianDailySummary'])
    ->name('kasa.technicianDaily.post')
    ->middleware('auth');
Route::post('/kasa/teknisyen-detay', [KasaController::class, 'technicianDailyDetail'])
    ->name('kasa.technicianDailyDetail')
    ->middleware('auth');
Route::post('/kasa/teknisyen-bloke', [KasaController::class, 'blockTechnicians'])
    ->name('kasa.blockTechnicians')
    ->middleware('auth');
Route::post('/kasa/teknisyen-mesai-kapat', [KasaController::class, 'closeTechnicianShift'])
    ->name('kasa.closeTechnicianShift')
    ->middleware('auth');
Route::post('/kasa/teknisyen-mesai-ac', [KasaController::class, 'openTechnicianShift'])
    ->name('kasa.openTechnicianShift')
    ->middleware('auth');
Route::post('/kasa/teknisyen-mesai-map', [KasaController::class, 'getTechnicianMesaiMap'])
    ->name('kasa.getTechnicianMesaiMap')
    ->middleware('auth');
Route::get('/kasa-form-data', [KasaController::class, 'getKasaFormData'])->name('kasa.formData')->middleware('auth');
Route::post('/genelkasa/{kasa}/soft-delete', [KasaController::class, 'softDelete'])
    ->name('kasa.softDelete')
    ->middleware('auth');
Route::post('/kasa/bulk-update-gerceklesme-tarihi', [KasaController::class, 'bulkUpdateGerceklesmeTarihi'])->name('kasa.bulkUpdateGerceklesmeTarihi')->middleware('auth');
Route::get('/kasa/excel', [KasaController::class, 'exportTechnicianDailySummary'])->name('kasa.exportTechnicianDailySummary')->middleware('auth');

// İşlem Logları Rotaları
Route::resource('islemlog', IslemLogController::class)->only(['show', 'update', 'destroy'])->middleware('auth');

// Duyurular (Header ve Yönetim)
Route::get('/duyurular/son', [AnnouncementController::class, 'recent'])->middleware('auth');
Route::post('/duyurular/{id}/okundu', [AnnouncementController::class, 'markRead'])->middleware('auth');

// Patron JSON yönetim uçları
Route::get('/ayarlar/duyurular', [AnnouncementController::class, 'adminIndex'])->name('settings.annc.index')->middleware('auth');
Route::post('/ayarlar/duyurular', [AnnouncementController::class, 'store'])->name('settings.annc.store')->middleware('auth');
Route::post('/ayarlar/duyurular/{id}', [AnnouncementController::class, 'update'])->name('settings.annc.update')->middleware('auth');
Route::delete('/ayarlar/duyurular/{id}', [AnnouncementController::class, 'destroy'])->name('settings.annc.destroy')->middleware('auth');

// Ayarlar (Role-Ability) Rotaları
Route::get('/ayarlar', [SettingsController::class, 'index'])->name('settings.index')->middleware('auth');
Route::post('/ayarlar', [SettingsController::class, 'update'])->name('settings.update')->middleware('auth');
Route::get('/ayarlar/silinen-kayitlar', [DeletedRecordsController::class, 'index'])->name('settings.deletedRecords.index')->middleware('auth');
Route::post('/ayarlar/silinen-kayitlar/servis/{servis}/restore', [DeletedRecordsController::class, 'restoreServis'])->name('settings.deletedRecords.restoreServis')->middleware('auth');
Route::post('/ayarlar/silinen-kayitlar/kasa/{kasa}/restore', [DeletedRecordsController::class, 'restoreKasa'])->name('settings.deletedRecords.restoreKasa')->middleware('auth');
Route::post('/ayarlar/silinen-kayitlar/islemlog/{log}/restore', [DeletedRecordsController::class, 'restoreIslemLog'])->name('settings.deletedRecords.restoreIslemLog')->middleware('auth');
// Ayarlar: Markalar CRUD (basit)
Route::get('/ayarlar/markalar', [SettingsController::class, 'markalar'])->name('settings.markalar.index')->middleware('auth');
Route::post('/ayarlar/markalar', [SettingsController::class, 'markaStore'])->name('settings.markalar.store')->middleware('auth');
Route::put('/ayarlar/markalar/{id}', [SettingsController::class, 'markaUpdate'])->name('settings.markalar.update')->middleware('auth');
Route::delete('/ayarlar/markalar/{id}', [SettingsController::class, 'markaDestroy'])->name('settings.markalar.destroy')->middleware('auth');
// Ayarlar: Cihaz Türleri CRUD (basit)
Route::get('/ayarlar/cihaz-turleri', [SettingsController::class, 'cihazTurleri'])->name('settings.cihazTurleri.index')->middleware('auth');
Route::post('/ayarlar/cihaz-turleri', [SettingsController::class, 'cihazTuruStore'])->name('settings.cihazTurleri.store')->middleware('auth');
Route::put('/ayarlar/cihaz-turleri/{id}', [SettingsController::class, 'cihazTuruUpdate'])->name('settings.cihazTurleri.update')->middleware('auth');
Route::delete('/ayarlar/cihaz-turleri/{id}', [SettingsController::class, 'cihazTuruDestroy'])->name('settings.cihazTurleri.destroy')->middleware('auth');

// Ayarlar: Bölge Ayarları (aktif il/ilçe)
Route::get('/ayarlar/bolge', [SettingsController::class, 'regionsIndex'])->name('settings.regions.index')->middleware('auth');
Route::post('/ayarlar/bolge', [SettingsController::class, 'regionsSave'])->name('settings.regions.save')->middleware('auth');

Route::get('/ayarlar/whatsapp', [SettingsController::class, 'whatsappSettingsGet'])->name('settings.whatsapp.get')->middleware('auth');
Route::post('/ayarlar/whatsapp', [SettingsController::class, 'whatsappSettingsSave'])->name('settings.whatsapp.save')->middleware('auth');
Route::post('/ayarlar/whatsapp/test', [SettingsController::class, 'whatsappSettingsTest'])->name('settings.whatsapp.test')->middleware('auth');
