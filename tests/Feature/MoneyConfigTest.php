<?php

use App\Models\User;

test('the configured currency and locale are shared to the frontend', function () {
    $this->actingAs(User::factory()->create());

    $this->get('/dashboard')
        ->assertInertia(fn ($page) => $page
            ->where('money.currency', 'EUR')
            ->where('money.locale', 'it-IT')
        );
});

test('a self-hoster can switch currency and locale via config', function () {
    config(['money.currency' => 'USD', 'money.locale' => 'en-US']);

    $this->actingAs(User::factory()->create());

    $this->get('/dashboard')
        ->assertInertia(fn ($page) => $page
            ->where('money.currency', 'USD')
            ->where('money.locale', 'en-US')
        );
});
