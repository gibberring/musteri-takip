<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Personel;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Kullanıcı rolüne göre login sonrası yönlendirme.
     *
     * @return string
     */
    protected function redirectTo()
    {
        $user = auth()->user();
        $teknisyenPozisyonId = 1077;

        if ($user && (int) $user->poz_id === $teknisyenPozisyonId) {
            return '/personeller/' . $user->id . '/profil';
        }

        return $this->redirectTo;
    }

    /**
     * Login sonrası yönlendirme (intended override).
     */
    protected function authenticated(Request $request, $user)
    {
        $request->session()->put('two_factor_passed', false);

        $isTwoFactorRequired = (bool) ($user->two_factor_required ?? false);
        if ($isTwoFactorRequired) {
            if (!$user->two_factor_enabled || !$user->two_factor_secret) {
                return redirect()->route('twofactor.setup');
            }
            return redirect()->route('twofactor.challenge');
        }

        $teknisyenPozisyonId = 1077;
        if ($user && (int) $user->poz_id === $teknisyenPozisyonId) {
            return redirect('/personeller/' . $user->id . '/profil');
        }

        return redirect()->intended($this->redirectTo);
    }

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

    /**
     * Get the login username to be used by the controller.
     *
     * @return string
     */
    public function username()
    {
        return 'nick'; // Login için 'email' yerine 'nick' sütununu kullan
    }

    /**
     * Limit login attempts to reduce brute force risk.
     */
    protected function maxAttempts()
    {
        return 5;
    }

    /**
     * Lockout duration in minutes.
     */
    protected function decayMinutes()
    {
        return 2;
    }

    /**
     * Show the application's login form.
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm()
    {
        return view('crm.auth-login-creative'); // Kendi login view'ımızı gösteriyoruz
    }

    /**
     * Login doğrulamasında aktiflik ve mesai kontrolü.
     */
    protected function credentials(Request $request)
    {
        return array_merge(
            $request->only($this->username(), 'password'),
            ['aktif' => 1, 'mesai_basladimi' => 1]
        );
    }

    /**
     * Login başarısız olduğunda özel mesaj göster.
     */
    protected function sendFailedLoginResponse(Request $request)
    {
        $username = $request->input($this->username());
        if ($username) {
            $personel = Personel::where('nick', $username)->first(['id', 'aktif', 'mesai_basladimi', 'is_basi_tarih', 'updated_at', 'created_at']);
            if ($personel && ((int) $personel->aktif === 0 || (int) $personel->mesai_basladimi === 0)) {
                $baslangic = $personel->is_basi_tarih ?: $personel->created_at;
                $bitis = $personel->updated_at ?: Carbon::now();
                $gunSayisi = 0;
                if ($baslangic && $bitis) {
                    $gunSayisi = (int) Carbon::parse($baslangic)->diffInDays(Carbon::parse($bitis));
                }

                throw ValidationException::withMessages([
                    $this->username() => ['Hesabınız askıya alınmıştır. ' . $gunSayisi . ' gündür birlikte çalıştık. Özverili çalışmalarınız için teşekkür ederiz.'],
                ]);
            }
        }

        throw ValidationException::withMessages([
            $this->username() => ['Kullanıcı adı veya şifre yanlış.'],
        ]);
    }
}
