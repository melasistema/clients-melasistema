<?php

// Pins the SecurityHeaders middleware (app/Http/Middleware/SecurityHeaders.php)
// so a future middleware change can't silently drop these defense-in-depth headers.

test('web responses carry the baseline security headers', function () {
    $response = $this->get(route('login'));

    $response->assertHeader('X-Content-Type-Options', 'nosniff');
    $response->assertHeader('X-Frame-Options', 'DENY');
    $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
});

test('HSTS is only sent over HTTPS, never on plain HTTP', function () {
    // Plain HTTP (the default in tests) must not assert HSTS.
    $this->get(route('login'))->assertHeaderMissing('Strict-Transport-Security');

    // Over HTTPS the browser-cached HSTS commitment is emitted.
    $this->get('https://localhost/login')
        ->assertHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
});
