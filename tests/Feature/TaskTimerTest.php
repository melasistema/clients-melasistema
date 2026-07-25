<?php

use App\Models\Client;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;

function timerTask(array $attributes = []): Task
{
    $client = Client::factory()->create();
    $project = Project::factory()->for($client)->create();

    return Task::factory()->for($project)->create($attributes);
}

test('starting a timer marks the task running and records the start time', function () {
    $task = timerTask(['is_running' => false, 'timer_started_at' => null]);

    $this->actingAs(User::factory()->create())
        ->post(route('clients.projects.tasks.startTimer', [
            $task->project->client, $task->project, $task,
        ]))
        ->assertRedirect();

    $task->refresh();
    expect($task->is_running)->toBeTrue()
        ->and($task->timer_started_at)->not->toBeNull();
});

test('stopping a timer accumulates elapsed seconds and clears the running state', function () {
    $task = timerTask([
        'is_running' => true,
        'timer_started_at' => now()->subSeconds(600),
        'total_seconds' => 100,
    ]);

    $this->actingAs(User::factory()->create())
        ->post(route('clients.projects.tasks.stopTimer', [
            $task->project->client, $task->project, $task,
        ]))
        ->assertRedirect();

    $task->refresh();
    expect($task->is_running)->toBeFalse()
        ->and($task->timer_started_at)->toBeNull()
        ->and($task->total_seconds)->toBeGreaterThanOrEqual(700);
});

test('timer routes are gated behind authentication', function () {
    $task = timerTask();

    $this->post(route('clients.projects.tasks.startTimer', [
        $task->project->client, $task->project, $task,
    ]))->assertRedirect(route('login'));
});
