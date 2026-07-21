<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Personel;
use Symfony\Component\HttpFoundation\Response;

/**
 * Giriş yapmış personelin aktif ve mesai_basladimi değerlerini her istekte kontrol eder.
 * Kasa sayfasından "mesai kapat" ile kapatılan kullanıcıların oturumu bir sonraki istekte sonlandırılır.
 */
class EnsureAktifVeMesai
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return $next($request);
        }

        // API (Bearer token): Bu middleware web session için; API'de token geçerliliği ayrı yönetilir
        if ($request->bearerToken()) {
            return $next($request);
        }

        $user = Auth::user();
        if (!$user instanceof Personel) {
            return $next($request);
        }

        // Veritabanından güncel değerleri al (cache'lenmiş model güncel olmayabilir)
        $personel = Personel::where('id', $user->id)->first(['id', 'aktif', 'mesai_basladimi']);
        if (!$personel) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('login')->with('message', 'Hesap bulunamadı.');
        }

        $aktif = (int) ($personel->aktif ?? 0);
        $mesaiBasladimi = (int) ($personel->mesai_basladimi ?? 0);

        if ($aktif === 1 && $mesaiBasladimi === 1) {
            return $next($request);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('message', 'Hesabınız veya mesainiz kapatıldığı için oturumunuz sonlandırıldı.');
    }
}
