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
     * These are defense-in-depth and safe for an Inertia/Vue SPA — no Content
     * Security Policy is set here, since Vite's inline styles/scripts make a
     * strict CSP fragile; add one deliberately (report-only first) if wanted.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Don't sniff a response's MIME type away from its declared Content-Type.
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Clickjacking: this app is never meant to be embedded in a frame.
        $response->headers->set('X-Frame-Options', 'DENY');

        // Trim the referrer sent to other origins (don't leak full URLs/paths).
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // HSTS is a browser-cached commitment, so only assert it once the request
        // actually arrived over HTTPS — never on plain HTTP or local http:// dev.
        if ($request->secure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}
