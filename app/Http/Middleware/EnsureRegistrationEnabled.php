<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gates the public registration routes behind the features.registration_enabled
 * flag. The routes are always registered (so route('register') always resolves
 * for Ziggy), but they 404 unless a self-hoster opts into multi-user mode.
 */
class EnsureRegistrationEnabled
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless(config('features.registration_enabled'), 404);

        return $next($request);
    }
}
