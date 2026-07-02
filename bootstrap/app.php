<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');
        
        // $middleware->api(append: [
        //     \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        // ]);

        // $middleware->alias([
        //     'role' => \App\Http\Middleware\RoleMiddleware::class,
        // ]);

        // 1. GUNAKAN CARA RESMI INI (Otomatis menangani Sanctum)
    $middleware->statefulApi(); 

    $middleware->alias([
        'role' => \App\Http\Middleware\RoleMiddleware::class,
    ]);

        // $middleware->validateCsrfTokens(except: [
        //     'api/public/visitor',
        //     'api/v1/contact',
        //     'v1/contact',
        //     'api/contact',
        // ]);

        $middleware->validateCsrfTokens(except: [
    'api/*', // Ini akan mem-bypass CSRF untuk SEMUA rute yang diawali /api/
]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();