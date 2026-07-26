<?php

use App\Models\Client;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;

/**
 * Deletes are recoverable now (SoftDeletes on Client/Project/Task). A delete only
 * flags `deleted_at`; the DB cascade never fires, so a parent's subtree survives
 * intact for restore. Every read — scoped route bindings and the earnings rollups —
 * goes through relations that honour the soft-delete scope, so a trashed record's
 * subtree is hidden without any cascade bookkeeping.
 */
test('deleting a client soft-deletes it and preserves its subtree', function () {
    $user = User::factory()->create();
    $client = Client::factory()->for($user)->create();
    $project = Project::factory()->for($client)->create();
    $task = Task::factory()->for($project)->create();

    $this->actingAs($user)
        ->delete(route('clients.destroy', $client))
        ->assertRedirect(route('clients.index'));

    expect(Client::withTrashed()->whereKey($client->id)->exists())->toBeTrue()
        ->and(Client::whereKey($client->id)->exists())->toBeFalse()
        ->and($client->fresh()->trashed())->toBeTrue();

    // The DB cascade never ran, so the children remain for a later restore.
    $this->assertDatabaseHas('projects', ['id' => $project->id]);
    $this->assertDatabaseHas('tasks', ['id' => $task->id]);
});

test('a trashed client disappears from the index and its routes 404', function () {
    $user = User::factory()->create();
    $client = Client::factory()->for($user)->create();
    $client->delete();

    $this->actingAs($user)
        ->get(route('clients.index'))
        ->assertInertia(fn ($page) => $page->has('clients', 0));

    $this->actingAs($user)
        ->get(route('clients.edit', $client))
        ->assertNotFound();
});

test('restoring a client brings back its whole subtree', function () {
    $user = User::factory()->create();
    $client = Client::factory()->for($user)->create();
    Project::factory()->for($client)->create();

    $client->delete();
    $client->restore();

    // Children were never trashed, so they reappear with the parent automatically.
    $this->actingAs($user)
        ->get(route('clients.projects.index', $client))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->has('projects', 1));
});

test('deleting a project drops it from the client earnings rollup', function () {
    $user = User::factory()->create();
    $client = Client::factory()->for($user)->create();
    $project = Project::factory()->for($client)->create(['hourly_rate' => 100]);
    $task = Task::factory()->for($project)->create(['total_seconds' => 3600]); // €100

    expect($client->fresh()->total_earnings)->toBe(100.0);

    $project->delete();

    // projects() honours the soft-delete scope, so the rollup excludes it — but the
    // task rows are untouched and recoverable.
    expect($client->fresh()->total_earnings)->toBe(0.0);
    $this->assertDatabaseHas('tasks', ['id' => $task->id]);
});

test('deleting a task drops it from the project earnings rollup', function () {
    $client = Client::factory()->create();
    $project = Project::factory()->for($client)->create(['hourly_rate' => 100]);
    $task = Task::factory()->for($project)->create(['total_seconds' => 3600]); // €100

    expect($project->fresh()->total_earnings)->toBe(100.0);

    $task->delete();

    expect($project->fresh()->total_earnings)->toBe(0.0)
        ->and(Task::withTrashed()->whereKey($task->id)->exists())->toBeTrue();
});
