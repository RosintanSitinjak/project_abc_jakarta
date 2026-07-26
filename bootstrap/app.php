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
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');
        
        // Agar Nuxt & Laravel bisa bertukar data session/cookie
        $middleware->statefulApi();

        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);

        // SOLUSI KRUSIAL: Mengecualikan API dari CSRF 
        // Agar fungsi Edit/Delete tidak "Forbidden" setelah Merge Windows/Mac
        $middleware->validateCsrfTokens(except: [
            'api/*',
            'sanctum/csrf-cookie',
        ]);

        // SOLUSI: Mencegah redirect ke halaman 'login' (mencegah error 302/Route Login Not Found)
        $middleware->redirectGuestsTo(function (Request $request) {
            if ($request->is('api/*')) {
                return null; // Laravel akan kirim 401 Unauthorized (benar), bukan dilempar ke login
            }
            return '/login'; 
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();