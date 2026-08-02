<?php

use App\Models\Client;
use App\Models\Project;
use App\Models\Task;
use App\Models\TimeEntry;

// Pins the dated time-entries ledger (app/Models/TimeEntry.php + TaskController's
// bankTimer): every banked session must write exactly one dated row with the rate
// snapshotted, while total_seconds stays the all-time cache the earnings roll up.

function entryTask(array $projectAttributes = [], array $taskAttributes = []): Task
{
    $client = Client::factory()->create();
    $project = Project::factory()->for($client)->create($projectAttributes);

    return Task::factory()->for($project)->create($taskAttributes);
}

test('stopping a timer writes one dated time entry with the banked seconds', function () {
    $task = entryTask(['hourly_rate' => 60], [
        'is_running' => true,
        'timer_started_at' => now()->subSeconds(600),
        'total_seconds' => 100,
    ]);

    $this->actingAs($task->project->client->user)
        ->post(route('clients.projects.tasks.stopTimer', [$task->project->client, $task->project, $task]))
        ->assertRedirect();

    expect($task->timeEntries()->count())->toBe(1);

    $entry = $task->timeEntries()->first();
    expect($entry->seconds)->toBeGreaterThanOrEqual(600)
        ->and($entry->started_at)->not->toBeNull()
        ->and($entry->ended_at)->not->toBeNull()
        ->and((float) $entry->hourly_rate)->toBe(60.0);
});

test('the entry snapshots the rate at bank time, immune to later rate changes', function () {
    $task = entryTask(['hourly_rate' => 50], [
        'is_running' => true,
        'timer_started_at' => now()->subSeconds(300),
        'total_seconds' => 0,
    ]);

    $this->actingAs($task->project->client->user)
        ->post(route('clients.projects.tasks.stopTimer', [$task->project->client, $task->project, $task]));

    // The project's rate rises AFTER the session was banked — the entry must not move.
    $task->project->update(['hourly_rate' => 80]);

    expect((float) $task->timeEntries()->first()->hourly_rate)->toBe(50.0);
});

test('a zero-second stop writes no entry', function () {
    $task = entryTask([], [
        'is_running' => true,
        'timer_started_at' => now(),
        'total_seconds' => 0,
    ]);

    $this->actingAs($task->project->client->user)
        ->post(route('clients.projects.tasks.stopTimer', [$task->project->client, $task->project, $task]));

    expect($task->timeEntries()->count())->toBe(0);
});

test('switching timers banks the previous task as a time entry', function () {
    $client = Client::factory()->create();
    $project = Project::factory()->for($client)->create();

    $running = Task::factory()->for($project)->create([
        'is_running' => true,
        'timer_started_at' => now()->subSeconds(300),
        'total_seconds' => 0,
    ]);
    $next = Task::factory()->for($project)->create(['is_running' => false, 'timer_started_at' => null]);

    $this->actingAs($client->user)
        ->post(route('clients.projects.tasks.startTimer', [$client, $project, $next]));

    expect($running->timeEntries()->count())->toBe(1)
        ->and($next->timeEntries()->count())->toBe(0);
});

test('completing a running task banks its session as a time entry', function () {
    $task = entryTask([], [
        'is_running' => true,
        'timer_started_at' => now()->subSeconds(120),
        'total_seconds' => 0,
    ]);

    $this->actingAs($task->project->client->user)
        ->post(route('clients.projects.tasks.complete', [$task->project->client, $task->project, $task]));

    $task->refresh();
    expect($task->timeEntries()->count())->toBe(1)
        ->and($task->is_completed)->toBeTrue()
        ->and($task->is_running)->toBeFalse();
});

test('total_seconds stays equal to the sum of the task time entries', function () {
    $task = entryTask([], ['is_running' => false, 'timer_started_at' => null, 'total_seconds' => 0]);
    $owner = $task->project->client->user;

    // Two separate tracked sessions banked one after the other.
    foreach ([200, 500] as $elapsed) {
        $task->update(['is_running' => true, 'timer_started_at' => now()->subSeconds($elapsed)]);
        $this->actingAs($owner)
            ->post(route('clients.projects.tasks.stopTimer', [$task->project->client, $task->project, $task]));
        $task->refresh();
    }

    expect($task->timeEntries()->count())->toBe(2)
        ->and($task->total_seconds)->toBe((int) $task->timeEntries()->sum('seconds'));
});

test('valueInCents uses the snapshotted rate', function () {
    $entry = TimeEntry::factory()->create(['seconds' => 3600, 'hourly_rate' => 42.50]);

    expect($entry->valueInCents())->toBe(4250);
});
