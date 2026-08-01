<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Attach conservative, app-agnostic security headers to every web response.
     *
     * These are defense-in-depth and safe for an Inertia/Vue SPA. The CSP set here
     * is deliberately partial: it omits script-src/style-src (a strict CSP is fragile
     * with Vite's inline/hashed assets) and only locks down directives that don't
     * touch scripts or styles, so nothing in the SPA breaks.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Don't sniff a response's MIME type away from its declared Content-Type.
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Clickjacking: this app is never meant to be embedded in a frame.
        // (X-Frame-Options for older browsers; frame-ancestors in the CSP below
        // is the modern, superseding control.)
        $response->headers->set('X-Frame-Options', 'DENY');

        // Trim the referrer sent to other origins (don't leak full URLs/paths).
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Minimal CSP — script-src/style-src are intentionally left unset so Vite/Inertia
        // keep working; these directives don't restrict scripts or styles:
        //   base-uri 'self'      — block <base> injection hijacking relative URLs
        //   object-src 'none'    — no plugins/embeds (Flash-era injection surface)
        //   frame-ancestors      — clickjacking (the modern X-Frame-Options)
        //   form-action 'self'   — forms may only submit same-origin (all forms are Inertia)
        $response->headers->set(
            'Content-Security-Policy',
            "base-uri 'self'; object-src 'none'; frame-ancestors 'none'; form-action 'self'",
        );

        // HSTS is a browser-cached commitment. Assert it when the request arrived over
        // HTTPS, or when the operator has declared an https APP_URL (so it still fires
        // behind a TLS-terminating proxy where $request->secure() reads false). Browsers
        // ignore an HSTS header delivered over real HTTP, so declaring it is never harmful.
        if ($request->secure() || str_starts_with((string) config('app.url'), 'https://')) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}
