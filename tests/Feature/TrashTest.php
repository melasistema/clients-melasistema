<?php

use App\Models\Client;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;

/**
 * The Trash page and its restore / permanent-delete flows. Recovery routes are
 * flat and bound with withTrashed(); each is gated by the matching policy.
 */
test('the trash page lists only the user\'s trashed items, per level', function () {
    $user = User::factory()->create();

    $activeClient = Client::factory()->for($user)->create();
    $trashedClient = Client::factory()->for($user)->create();
    $trashedClient->delete();

    // A project trashed on its own, under a still-active client.
    $trashedProject = Project::factory()->for($activeClient)->create();
    $trashedProject->delete();

    // Another user's trashed client must never appear.
    $otherTrashed = Client::factory()->create();
    $otherTrashed->delete();

    $this->actingAs($user)
        ->get(route('trash.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Trash/Index')
            ->has('clients', 1)
            ->where('clients.0.id', $trashedClient->id)
            ->has('projects', 1)
            ->where('projects.0.id', $trashedProject->id)
            ->has('tasks', 0)
        );
});

test('a trashed project whose client is also trashed drops off the projects list', function () {
    $user = User::factory()->create();
    $client = Client::factory()->for($user)->create();
    $project = Project::factory()->for($client)->create();

    $project->delete();
    $client->delete();

    // The client now owns the trash entry; its project is no longer listed on its own.
    $this->actingAs($user)
        ->get(route('trash.index'))
        ->assertInertia(fn ($page) => $page->has('clients', 1)->has('projects', 0));
});

test('a user can restore a trashed client', function () {
    $user = User::factory()->create();
    $client = Client::factory()->for($user)->create();
    $client->delete();

    $this->actingAs($user)
        ->put(route('clients.restore', $client))
        ->assertRedirect(route('trash.index'));

    $this->assertNotSoftDeleted($client);
});

test('permanently deleting a client purges its whole subtree', function () {
    $user = User::factory()->create();
    $client = Client::factory()->for($user)->create();
    $project = Project::factory()->for($client)->create();
    $task = Task::factory()->for($project)->create();
    $client->delete();

    $this->actingAs($user)
        ->delete(route('clients.forceDelete', $client))
        ->assertRedirect(route('trash.index'));

    // Gone for real — the DB cascade took the projects and tasks with it.
    $this->assertDatabaseMissing('clients', ['id' => $client->id]);
    $this->assertDatabaseMissing('projects', ['id' => $project->id]);
    $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
});

test('a user can restore and permanently delete trashed projects and tasks', function () {
    $user = User::factory()->create();
    $client = Client::factory()->for($user)->create();
    $project = Project::factory()->for($client)->create();
    $task = Task::factory()->for($project)->create();

    $project->delete();
    $this->actingAs($user)->put(route('projects.restore', $project))->assertRedirect(route('trash.index'));
    $this->assertNotSoftDeleted($project);

    $task->delete();
    $this->actingAs($user)->delete(route('tasks.forceDelete', $task))->assertRedirect(route('trash.index'));
    $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
});

test('a user cannot restore or purge another user\'s trashed records', function () {
    $owner = User::factory()->create();
    $client = Client::factory()->for($owner)->create();
    $client->delete();

    $attacker = User::factory()->create();

    $this->actingAs($attacker)->put(route('clients.restore', $client))->assertForbidden();
    $this->actingAs($attacker)->delete(route('clients.forceDelete', $client))->assertForbidden();

    // Still safely in the owner's trash.
    $this->assertSoftDeleted($client);
});
