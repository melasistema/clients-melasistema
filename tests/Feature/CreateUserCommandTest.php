<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

/**
 * `app:create-user` provisions the owner account on a fresh single-user install.
 */
test('it creates a verified user from options', function () {
    $this->artisan('app:create-user', [
        '--name' => 'Owner',
        '--email' => 'Owner@Example.com',
        '--password' => 'supersecret',
    ])->assertSuccessful();

    $user = User::first();

    expect($user)->not->toBeNull()
        ->and($user->name)->toBe('Owner')
        ->and($user->email)->toBe('owner@example.com') // normalized to lowercase
        ->and($user->email_verified_at)->not->toBeNull()
        ->and(Hash::check('supersecret', $user->password))->toBeTrue();
});

test('it rejects an invalid email', function () {
    $this->artisan('app:create-user', [
        '--name' => 'Owner',
        '--email' => 'not-an-email',
        '--password' => 'supersecret',
    ])->assertFailed();

    expect(User::count())->toBe(0);
});

test('it rejects a too-short password', function () {
    $this->artisan('app:create-user', [
        '--name' => 'Owner',
        '--email' => 'owner@example.com',
        '--password' => 'short',
    ])->assertFailed();

    expect(User::count())->toBe(0);
});

test('it will not add a second user without confirmation', function () {
    User::factory()->create();

    $this->artisan('app:create-user', [
        '--name' => 'Second',
        '--email' => 'second@example.com',
        '--password' => 'supersecret',
    ])
        ->expectsConfirmation('A user already exists. This app is single-user by default — create another account anyway?', 'no')
        ->assertSuccessful();

    expect(User::count())->toBe(1);
});

test('it adds a second user when confirmed', function () {
    User::factory()->create();

    $this->artisan('app:create-user', [
        '--name' => 'Second',
        '--email' => 'second@example.com',
        '--password' => 'supersecret',
    ])
        ->expectsConfirmation('A user already exists. This app is single-user by default — create another account anyway?', 'yes')
        ->assertSuccessful();

    expect(User::count())->toBe(2);
});
