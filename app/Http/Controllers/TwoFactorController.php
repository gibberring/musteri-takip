<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\RateLimiter;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function setup()
    {
        $user = Auth::user();
        $isTwoFactorRequired = (bool) ($user->two_factor_required ?? false);
        if (!$isTwoFactorRequired) {
            return redirect()->intended('/');
        }

        if ($user->two_factor_enabled && $user->two_factor_secret) {
            return redirect()->route('twofactor.challenge');
        }

        $secret = session()->get('two_factor_setup_secret');
        if (!$secret) {
            $google2fa = new Google2FA();
            $secret = $google2fa->generateSecretKey();
            session(['two_factor_setup_secret' => $secret]);
        }

        $company = config('app.name', 'Servis Takip');
        $label = $user->nick ?? $user->ad ?? ('user-' . $user->id);
        $google2fa = new Google2FA();
        $otpauth = $google2fa->getQRCodeUrl($company, $label, $secret);

        return view('auth.twofactor-setup', [
            'otpauth' => $otpauth,
            'secret' => $secret,
        ]);
    }

    public function setupVerify(Request $request)
    {
        $user = Auth::user();
        $isTwoFactorRequired = (bool) ($user->two_factor_required ?? false);
        if (!$isTwoFactorRequired) {
            return redirect()->intended('/');
        }

        $request->validate([
            'code' => 'required|string|min:6|max:10',
        ]);

        $secret = session()->get('two_factor_setup_secret');
        if (!$secret) {
            return redirect()->route('twofactor.setup')->withErrors(['code' => 'Kurulum anahtarı bulunamadı.']);
        }

        $google2fa = new Google2FA();
        $isValid = $google2fa->verifyKey($secret, $request->input('code'));
        if (!$isValid) {
            return back()->withErrors(['code' => 'Doğrulama kodu hatalı.'])->withInput();
        }

        $user->two_factor_secret = Crypt::encryptString($secret);
        $user->two_factor_enabled = true;
        $user->two_factor_verified_at = now();
        $user->save();

        session()->forget('two_factor_setup_secret');
        session(['two_factor_passed' => true]);

        return redirect()->intended('/');
    }

    public function challenge()
    {
        $user = Auth::user();
        $isTwoFactorRequired = (bool) ($user->two_factor_required ?? false);
        if (!$isTwoFactorRequired) {
            return redirect()->intended('/');
        }

        if (!$user->two_factor_enabled || !$user->two_factor_secret) {
            return redirect()->route('twofactor.setup');
        }

        return view('crm.auth-verify-creative');
    }

    public function challengeVerify(Request $request)
    {
        $user = Auth::user();
        $isTwoFactorRequired = (bool) ($user->two_factor_required ?? false);
        if (!$isTwoFactorRequired) {
            return redirect()->intended('/');
        }

        $request->validate([
            'code' => 'required|string|min:6|max:10',
        ]);

        if (!$user->two_factor_enabled || !$user->two_factor_secret) {
            return redirect()->route('twofactor.setup');
        }

        $key = 'twofactor:' . $user->id;
        if (RateLimiter::tooManyAttempts($key, 5)) {
            return back()->withErrors(['code' => 'Çok fazla deneme. Lütfen biraz sonra tekrar deneyin.']);
        }
        RateLimiter::hit($key, 60);

        $secret = Crypt::decryptString($user->two_factor_secret);
        $google2fa = new Google2FA();
        $isValid = $google2fa->verifyKey($secret, $request->input('code'));
        if (!$isValid) {
            return back()->withErrors(['code' => 'Doğrulama kodu hatalı.'])->withInput();
        }

        session(['two_factor_passed' => true]);
        $user->two_factor_verified_at = now();
        $user->save();

        return redirect()->intended('/');
    }
}
