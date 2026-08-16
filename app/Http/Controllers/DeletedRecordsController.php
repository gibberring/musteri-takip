<?php

namespace App\Http\Controllers;

use App\Models\Servis;
use App\Models\Kasa;
use App\Models\Islemloglari;
use App\Models\SettingsAuditLog;
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

    public function index()
    {
        $this->authorizePatron();

        // Tüm silinen kayıtları limitsiz çekmek (özellikle işlem logları) 128MB PHP
        // belleğini aşıp 500 veriyordu; audit sekmesiyle aynı üst sınır.
        $listLimit = 500;

        $silinenServisler = Servis::with(['musteri', 'personel', 'servisDurum', 'silenKisi'])
            ->where('silindi', 1)
            ->orderByDesc('silinme_tarihi')
            ->limit($listLimit)
            ->get();

        $silinenKasa = Kasa::with(['personel', 'ilgiliPersonel', 'odemeTuru', 'odemeSekli', 'servis', 'silenKisi'])
            ->where('silindi', 1)
            ->orderByDesc('silinme_tarihi')
            ->limit($listLimit)
            ->get();

        $silinenIslemLoglari = Islemloglari::onlyDeleted()
            ->with(['personel', 'servis', 'servisDurum', 'silenKisi'])
            ->orderByDesc('silinme_tarihi')
            ->limit($listLimit)
            ->get();

        $musteriIletisimGuncellemeleri = SettingsAuditLog::query()
            ->where('action', SettingsAuditLog::ACTION_MUSTERI_CONTACT_UPDATED)
            ->with(['personel:id,ad'])
            ->orderByDesc('id')
            ->limit($listLimit)
            ->get();

        return view('settings.deleted-records', compact(
            'silinenServisler',
            'silinenKasa',
            'silinenIslemLoglari',
            'musteriIletisimGuncellemeleri'
        ));
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
