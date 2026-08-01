<?php

// Pins the SecurityHeaders middleware (app/Http/Middleware/SecurityHeaders.php)
// so a future middleware change can't silently drop these defense-in-depth headers.

test('web responses carry the baseline security headers', function () {
    $response = $this->get(route('login'));

    $response->assertHeader('X-Content-Type-Options', 'nosniff');
    $response->assertHeader('X-Frame-Options', 'DENY');
    $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
});

test('web responses carry the minimal, Vite-safe CSP', function () {
    // The CSP must lock down base-uri/object-src/frame-ancestors/form-action and
    // must NOT set script-src/style-src (those would break Vite's inline assets).
    $csp = $this->get(route('login'))->headers->get('Content-Security-Policy');

    expect($csp)
        ->toContain("base-uri 'self'")
        ->toContain("object-src 'none'")
        ->toContain("frame-ancestors 'none'")
        ->toContain("form-action 'self'")
        ->not->toContain('script-src')
        ->not->toContain('style-src');
});

test('HSTS is sent over HTTPS but not on plain HTTP with an http APP_URL', function () {
    config(['app.url' => 'http://localhost']);

    // Plain HTTP with an http APP_URL must not assert HSTS.
    $this->get(route('login'))->assertHeaderMissing('Strict-Transport-Security');

    // Over HTTPS the browser-cached HSTS commitment is emitted.
    $this->get('https://localhost/login')
        ->assertHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
});

test('HSTS still fires on a proxied HTTP request when APP_URL is https', function () {
    // Behind a TLS-terminating proxy the request reads as insecure, but an https
    // APP_URL means the operator has declared the site is served over HTTPS.
    config(['app.url' => 'https://localhost']);

    $this->get(route('login'))
        ->assertHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
});
