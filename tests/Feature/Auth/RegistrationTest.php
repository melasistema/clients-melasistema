<?php

// Public registration is closed by default (single-user app). The routes are
// always registered but the registration.enabled middleware 404s them unless
// REGISTRATION_ENABLED=true. These tests lock both states in.

test('registration screen is not available by default', function () {
    $response = $this->get('/register');

    $response->assertNotFound();
});

test('new users cannot register by default', function () {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertNotFound();
    $this->assertGuest();
});

test('registration screen is available when registration is enabled', function () {
    config()->set('features.registration_enabled', true);

    $this->get('/register')->assertOk();
});

test('new users can register when registration is enabled', function () {
    config()->set('features.registration_enabled', true);

    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});
