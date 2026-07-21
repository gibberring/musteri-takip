<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Personel;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use PragmaRX\Google2FA\Google2FA;

class AuthController extends Controller
{
    /**
     * API Login: nick + password. 2FA gerekliyse 423 döner, token verilmez.
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'nick'     => 'required|string',
            'password' => 'required|string',
        ]);

        $personel = Personel::where('nick', $request->input('nick'))->first();

        if (!$personel || !Hash::check($request->input('password'), $personel->sifre)) {
            throw ValidationException::withMessages([
                'nick' => ['Kullanıcı adı veya şifre yanlış.'],
            ]);
        }

        if ((int) ($personel->aktif ?? 0) !== 1 || (int) ($personel->mesai_basladimi ?? 0) !== 1) {
            $baslangic = $personel->is_basi_tarih ?: $personel->created_at;
            $bitis = $personel->updated_at ?: Carbon::now();
            $gunSayisi = $baslangic && $bitis
                ? (int) Carbon::parse($baslangic)->diffInDays(Carbon::parse($bitis))
                : 0;
            throw ValidationException::withMessages([
                'nick' => ['Hesabınız askıya alınmıştır. ' . $gunSayisi . ' gündür birlikte çalıştık. Özverili çalışmalarınız için teşekkür ederiz.'],
            ]);
        }

        $isTwoFactorRequired = (bool) ($personel->two_factor_required ?? false);
        if ($isTwoFactorRequired && !empty($personel->two_factor_secret) && (bool) ($personel->two_factor_enabled ?? false)) {
            return response()->json([
                'message' => 'İki adımlı doğrulama gerekli.',
                'two_factor_required' => true,
            ], 423);
        }

        $token = $personel->createToken('mobile')->plainTextToken;
        return response()->json([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => $this->userResource($personel),
        ]);
    }

    /**
     * 2FA doğrulaması: nick + password + code ile token alınır.
     */
    public function login2fa(Request $request): JsonResponse
    {
        $request->validate([
            'nick'     => 'required|string',
            'password' => 'required|string',
            'code'     => 'required|string|min:6|max:10',
        ]);

        $personel = Personel::where('nick', $request->input('nick'))->first();

        if (!$personel || !Hash::check($request->input('password'), $personel->sifre)) {
            throw ValidationException::withMessages([
                'nick' => ['Kullanıcı adı veya şifre yanlış.'],
            ]);
        }

        if ((int) ($personel->aktif ?? 0) !== 1 || (int) ($personel->mesai_basladimi ?? 0) !== 1) {
            throw ValidationException::withMessages([
                'nick' => ['Hesabınız askıda veya mesai başlamamış.'],
            ]);
        }

        if (!(bool) ($personel->two_factor_enabled ?? false) || empty($personel->two_factor_secret)) {
            return response()->json([
                'message' => 'İki adımlı doğrulama kurulumu gerekli.',
            ], 423);
        }

        $key = 'twofactor:api:' . $personel->id;
        if (RateLimiter::tooManyAttempts($key, 5)) {
            return response()->json([
                'message' => 'Çok fazla deneme. Lütfen biraz sonra tekrar deneyin.',
            ], 429);
        }
        RateLimiter::hit($key, 60);

        $secret = Crypt::decryptString($personel->two_factor_secret);
        $google2fa = new Google2FA();
        if (!$google2fa->verifyKey($secret, $request->input('code'))) {
            return response()->json([
                'message' => 'Doğrulama kodu hatalı.',
            ], 422);
        }

        $personel->two_factor_verified_at = now();
        $personel->save();

        $token = $personel->createToken('mobile')->plainTextToken;
        return response()->json([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => $this->userResource($personel),
        ]);
    }

    /**
     * Çıkış: mevcut token iptal.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Çıkış yapıldı.']);
    }

    /**
     * Giriş yapmış kullanıcı bilgisi (menü/rol için).
     */
    public function user(Request $request): JsonResponse
    {
        $personel = $request->user();
        $personel->load('pozisyon');
        return response()->json([
            'user' => $this->userResource($personel),
        ]);
    }

    private function userResource(Personel $personel): array
    {
        $pozisyon = $personel->relationLoaded('pozisyon') ? $personel->pozisyon : $personel->pozisyon()->first();
        return [
            'id'       => $personel->id,
            'nick'     => $personel->nick,
            'ad'       => $personel->ad ?? $personel->name ?? $personel->nick,
            'poz_id'   => (int) $personel->poz_id,
            'poz_ad'   => $pozisyon->ad ?? null,
            'email'    => $personel->email ?? null,
        ];
    }
}
