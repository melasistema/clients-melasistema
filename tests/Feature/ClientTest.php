<?php

use App\Models\Client;
use App\Models\User;

test('guests are redirected from the clients index', function () {
    $this->get(route('clients.index'))->assertRedirect(route('login'));
});

test('the clients index only lists the authenticated user\'s clients', function () {
    $user = User::factory()->create();
    $mine = Client::factory()->for($user)->create();
    $theirs = Client::factory()->create(); // belongs to another user

    $this->actingAs($user)
        ->get(route('clients.index'))
        ->assertInertia(fn ($page) => $page
            ->component('Clients/Index')
            ->has('clients', 1)
            ->where('clients.0.id', $mine->id)
        );

    expect(Client::whereKey($theirs->id)->exists())->toBeTrue();
});

test('a user can store a client, which is attached to them', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('clients.store'), [
            'company_name' => 'Acme Corporation',
            'contact_name' => 'John Carter',
            'contact_email' => 'john@acme.example',
        ])
        ->assertRedirect(route('clients.index'));

    $this->assertDatabaseHas('clients', [
        'company_name' => 'Acme Corporation',
        'contact_email' => 'john@acme.example',
        'user_id' => $user->id,
    ]);
});

test('storing a client requires the mandatory fields', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('clients.store'), [])
        ->assertSessionHasErrors(['company_name', 'contact_name', 'contact_email']);
});

test('a user can update a client', function () {
    $user = User::factory()->create();
    $client = Client::factory()->for($user)->create(['company_name' => 'Old Name']);

    $this->actingAs($user)
        ->put(route('clients.update', $client), [
            'company_name' => 'New Name',
            'contact_name' => $client->contact_name,
            'contact_email' => $client->contact_email,
        ])
        ->assertRedirect(route('clients.index'));

    expect($client->refresh()->company_name)->toBe('New Name');
});

test('a user can delete a client', function () {
    $user = User::factory()->create();
    $client = Client::factory()->for($user)->create();

    $this->actingAs($user)
        ->delete(route('clients.destroy', $client))
        ->assertRedirect(route('clients.index'));

    $this->assertModelMissing($client);
});
