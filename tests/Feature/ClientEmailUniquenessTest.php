<?php

use App\Models\Client;
use App\Models\User;

/**
 * Client email uniqueness is scoped to the owner (user_id, contact_email), not
 * global — two freelancers may legitimately keep the same end-client.
 */
test('two different users may register a client with the same contact email', function () {
    $shared = 'hello@acme.example';

    $userA = User::factory()->create();
    Client::factory()->for($userA)->create(['contact_email' => $shared]);

    $userB = User::factory()->create();

    $this->actingAs($userB)
        ->post(route('clients.store'), [
            'company_name' => 'Acme (B copy)',
            'contact_name' => 'Jane Doe',
            'contact_email' => $shared,
        ])
        ->assertRedirect(route('clients.index'));

    $this->assertDatabaseHas('clients', [
        'contact_email' => $shared,
        'user_id' => $userB->id,
    ]);
});

test('a user cannot register two clients with the same contact email', function () {
    $user = User::factory()->create();
    Client::factory()->for($user)->create(['contact_email' => 'dup@acme.example']);

    $this->actingAs($user)
        ->post(route('clients.store'), [
            'company_name' => 'Duplicate',
            'contact_name' => 'Jane Doe',
            'contact_email' => 'dup@acme.example',
        ])
        ->assertSessionHasErrors('contact_email');
});

test('a client can keep its own email on update', function () {
    $user = User::factory()->create();
    $client = Client::factory()->for($user)->create(['contact_email' => 'keep@acme.example']);

    $this->actingAs($user)
        ->put(route('clients.update', $client), [
            'company_name' => 'Renamed',
            'contact_name' => $client->contact_name,
            'contact_email' => 'keep@acme.example',
        ])
        ->assertRedirect(route('clients.index'));
});
