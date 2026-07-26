<?php

use App\Models\Client;
use App\Models\Project;
use App\Models\Task;

function completionTask(array $attributes = []): Task
{
    $project = Project::factory()->for(Client::factory())->create();

    return Task::factory()->for($project)->create($attributes);
}

test('the owner can complete a task', function () {
    $task = completionTask(['completed_at' => null]);

    $this->actingAs($task->project->client->user)
        ->post(route('clients.projects.tasks.complete', [$task->project->client, $task->project, $task]))
        ->assertRedirect();

    expect($task->fresh()->completed_at)->not->toBeNull();
});

test('completing a running task stops its timer and banks the elapsed seconds', function () {
    $task = completionTask([
        'is_running' => true,
        'timer_started_at' => now()->subSeconds(600),
        'total_seconds' => 100,
        'completed_at' => null,
    ]);

    $this->actingAs($task->project->client->user)
        ->post(route('clients.projects.tasks.complete', [$task->project->client, $task->project, $task]))
        ->assertRedirect();

    $task->refresh();
    expect($task->completed_at)->not->toBeNull()
        ->and($task->is_running)->toBeFalse()
        ->and($task->timer_started_at)->toBeNull()
        ->and($task->total_seconds)->toBeGreaterThanOrEqual(700);
});

test('the owner can reopen a completed task', function () {
    $task = completionTask(['completed_at' => now()]);

    $this->actingAs($task->project->client->user)
        ->post(route('clients.projects.tasks.reopen', [$task->project->client, $task->project, $task]))
        ->assertRedirect();

    expect($task->fresh()->completed_at)->toBeNull();
});
