<?php

// Public registration is intentionally disabled (see routes/auth.php).
// These tests lock that behavior in so the routes can't be re-enabled by accident.

test('registration screen is not available', function () {
    $response = $this->get('/register');

    $response->assertNotFound();
});

test('new users cannot register', function () {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertNotFound();
    $this->assertGuest();
});
