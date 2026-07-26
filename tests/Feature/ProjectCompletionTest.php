<?php

use App\Models\Client;
use App\Models\Payment;
use App\Models\Project;
use App\Models\User;

test('the owner can complete a project', function () {
    $project = Project::factory()->for(Client::factory())->create(['completed_at' => null]);

    $this->actingAs($project->client->user)
        ->post(route('clients.projects.complete', [$project->client, $project]))
        ->assertRedirect();

    expect($project->fresh()->completed_at)->not->toBeNull();
});

test('the owner can reopen a completed project', function () {
    $project = Project::factory()->for(Client::factory())->completed()->create();

    $this->actingAs($project->client->user)
        ->post(route('clients.projects.reopen', [$project->client, $project]))
        ->assertRedirect();

    expect($project->fresh()->completed_at)->toBeNull();
});

test('a fully paid project cannot be reopened', function () {
    $project = Project::factory()->for(Client::factory())->fixed(1000)->completed()->create();
    Payment::factory()->for($project)->create(['amount' => 1000]);

    expect($project->fresh()->loadMissing('payments')->is_fully_paid)->toBeTrue();

    $this->actingAs($project->client->user)
        ->post(route('clients.projects.reopen', [$project->client, $project]))
        ->assertForbidden();

    expect($project->fresh()->completed_at)->not->toBeNull();
});

test('a user cannot complete another user\'s project', function () {
    $project = Project::factory()->for(Client::factory())->create();
    $intruder = User::factory()->create();

    $this->actingAs($intruder)
        ->post(route('clients.projects.complete', [$project->client, $project]))
        ->assertForbidden();
});
