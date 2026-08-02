<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->append(\App\Http\Middleware\NoIndexMiddleware::class);
        $middleware->append(\App\Http\Middleware\EnsureTwoFactor::class);
        // Oturum yüklendikten sonra çalışması için web grubuna ekleniyor (StartSession web içinde).
        // AuthenticateSession: şifre değişince eski oturumlar/remember cookie password_hash ile düşer.
        $middleware->web(append: [
            \Illuminate\Session\Middleware\AuthenticateSession::class,
            \App\Http\Middleware\EnsureAktifVeMesai::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
