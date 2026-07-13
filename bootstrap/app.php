<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->trustProxies(at: '*');
        
        // 1. Wajib untuk Nuxt + Sanctum
        $middleware->statefulApi();

        // 2. Memaksa Session aktif di API (Agar Login tidak terlepas)
        $middleware->group('api', [
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
        ]);

        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);

        // 3. Solusi Ampuh: Paksa Laravel untuk TIDAK me-redirect ke rute 'login'
        $middleware->redirectGuestsTo(function (Request $request) {
            if ($request->is('api/*')) {
                return null; // Laravel akan otomatis kirim status 401 (Unauthenticated)
            }
            return route('login');
        });

        $middleware->validateCsrfTokens(except: [
            'api/public/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Menangani error jika user belum login di API
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'Sesi habis. Silakan login kembali.',
                ], 401);
            }
        });
    })->create();