<?php

use App\Models\Client;
use App\Models\Payment;
use App\Models\Project;
use App\Models\User;

test('the owner can record a payment against a project', function () {
    $project = Project::factory()->for(Client::factory())->fixed(5000)->create();

    $this->actingAs($project->client->user)
        ->post(route('clients.projects.payments.store', [$project->client, $project]), [
            'amount' => 1500,
            'paid_at' => '2026-01-15',
            'note' => 'Deposit',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('payments', [
        'project_id' => $project->id,
        'amount' => '1500.00',
        'note' => 'Deposit',
    ]);

    expect($project->fresh()->loadMissing('payments')->amount_paid)->toBe(1500.0);
});

test('a payment requires a positive amount and a date', function () {
    $project = Project::factory()->for(Client::factory())->create();

    $this->actingAs($project->client->user)
        ->post(route('clients.projects.payments.store', [$project->client, $project]), [
            'amount' => 0,
            'paid_at' => '',
        ])
        ->assertSessionHasErrors(['amount', 'paid_at']);

    expect(Payment::count())->toBe(0);
});

test('a user cannot record a payment on another user\'s project', function () {
    $project = Project::factory()->for(Client::factory())->create();
    $intruder = User::factory()->create();

    $this->actingAs($intruder)
        ->post(route('clients.projects.payments.store', [$project->client, $project]), [
            'amount' => 100,
            'paid_at' => '2026-01-15',
        ])
        ->assertForbidden();

    expect(Payment::count())->toBe(0);
});

test('the owner can delete a payment', function () {
    $project = Project::factory()->for(Client::factory())->create();
    $payment = Payment::factory()->for($project)->create();

    $this->actingAs($project->client->user)
        ->delete(route('clients.projects.payments.destroy', [$project->client, $project, $payment]))
        ->assertRedirect();

    $this->assertSoftDeleted($payment);
});

test('a user cannot delete another user\'s payment', function () {
    $project = Project::factory()->for(Client::factory())->create();
    $payment = Payment::factory()->for($project)->create();
    $intruder = User::factory()->create();

    $this->actingAs($intruder)
        ->delete(route('clients.projects.payments.destroy', [$project->client, $project, $payment]))
        ->assertForbidden();

    $this->assertNotSoftDeleted($payment);
});

test('deleting a payment through a project it does not belong to 404s (scoped binding)', function () {
    $client = Client::factory()->create();
    $projectA = Project::factory()->for($client)->create();
    $projectB = Project::factory()->for($client)->create();
    $payment = Payment::factory()->for($projectA)->create();

    $this->actingAs($client->user)
        ->delete(route('clients.projects.payments.destroy', [$client, $projectB, $payment]))
        ->assertNotFound();

    $this->assertNotSoftDeleted($payment);
});
