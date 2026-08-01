<?php

use App\Http\Middleware\EnsureRegistrationEnabled;
use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // `last_timer` holds the last-stopped task so the header timer bar can
        // persist across navigation until dismissed — read server-side and shared
        // by HandleInertiaRequests, so it stays plaintext like the other UI cookies.
        $middleware->encryptCookies(except: ['appearance', 'sidebar_state', 'last_timer']);

        $middleware->alias([
            'registration.enabled' => EnsureRegistrationEnabled::class,
        ]);

        $middleware->web(append: [
            SecurityHeaders::class,
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
