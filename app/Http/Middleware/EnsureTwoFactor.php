<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureTwoFactor
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return $next($request);
        }

        // API: Bearer token ile gelen isteklerde 2FA zaten login/2fa aşamasında yapıldı
        if ($request->bearerToken()) {
            return $next($request);
        }

        $user = Auth::user();
        $isTwoFactorRequired = (bool) ($user->two_factor_required ?? false);
        if (!$isTwoFactorRequired) {
            return $next($request);
        }
        $routeName = $request->route() ? $request->route()->getName() : null;
        $path = $request->path();

        if ($routeName && str_starts_with($routeName, 'twofactor.')) {
            return $next($request);
        }

        if (in_array($path, ['login', 'logout'], true)) {
            return $next($request);
        }

        if (!(bool) ($user->two_factor_enabled ?? false) || empty($user->two_factor_secret)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'İki adımlı doğrulama kurulumu gerekli.'], 423);
            }
            return redirect()->route('twofactor.setup');
        }

        if (!session()->get('two_factor_passed', false)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'İki adımlı doğrulama gerekli.'], 423);
            }
            return redirect()->route('twofactor.challenge');
        }

        return $next($request);
    }
}
