<?php

use App\Models\User;
use Illuminate\Support\Facades\App;

test('the active locale and its translations are shared to the frontend', function () {
    // Pin the locale explicitly so the test is independent of the install's
    // APP_LOCALE (the owner may run the app in Italian).
    App::setLocale('en');

    $this->actingAs(User::factory()->create());

    $this->get('/dashboard')
        ->assertInertia(fn ($page) => $page
            ->where('locale', 'en')
            ->where('translations.common.edit', 'Edit')
            ->where('translations.common.nav.clients', 'Clients')
            ->where('translations.clients.title', 'Clients')
            ->where('translations.settings.nav.profile', 'Profile')
            ->where('translations.auth.login.submit', 'Log in')
            ->where('translations.dashboard.stats.outstanding', 'Outstanding')
        );
});

test('switching the app locale switches the shared translations', function () {
    App::setLocale('it');

    $this->actingAs(User::factory()->create());

    $this->get('/dashboard')
        ->assertInertia(fn ($page) => $page
            ->where('locale', 'it')
            ->where('translations.common.edit', 'Modifica')
            ->where('translations.common.nav.clients', 'Clienti')
            ->where('translations.clients.title', 'Clienti')
            ->where('translations.settings.nav.profile', 'Profilo')
            ->where('translations.auth.login.submit', 'Accedi')
            ->where('translations.dashboard.stats.outstanding', 'Da incassare')
        );
});

test('a locale with no lang files falls back to the fallback locale text', function () {
    // French has no lang/fr directory; the deep merge over the fallback locale
    // (en) means the frontend still gets English strings, never raw keys.
    App::setLocale('fr');

    $this->actingAs(User::factory()->create());

    $this->get('/dashboard')
        ->assertInertia(fn ($page) => $page
            ->where('locale', 'fr')
            ->where('translations.common.edit', 'Edit')
            ->where('translations.clients.title', 'Clients')
        );
});
