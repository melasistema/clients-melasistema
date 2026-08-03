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

    $this->actingAs($task->project->client->user)
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

    $this->actingAs($task->project->client->user)
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

test('starting a timer stops the owner\'s other running task and banks its time', function () {
    $client = Client::factory()->create();
    $project = Project::factory()->for($client)->create();

    // A task already running for ~5 minutes with 100s banked.
    $running = Task::factory()->for($project)->create([
        'is_running' => true,
        'timer_started_at' => now()->subSeconds(300),
        'total_seconds' => 100,
    ]);
    $next = Task::factory()->for($project)->create(['is_running' => false, 'timer_started_at' => null]);

    // A different freelancer's running timer must be untouched.
    $otherRunning = timerTask(['is_running' => true, 'timer_started_at' => now()->subSeconds(60)]);

    $this->actingAs($client->user)
        ->post(route('clients.projects.tasks.startTimer', [$client, $project, $next]))
        ->assertRedirect();

    $running->refresh();
    $next->refresh();
    $otherRunning->refresh();

    expect($next->is_running)->toBeTrue()
        ->and($running->is_running)->toBeFalse()
        ->and($running->timer_started_at)->toBeNull()
        ->and($running->total_seconds)->toBeGreaterThanOrEqual(400) // 100 + ~300 banked
        ->and($otherRunning->is_running)->toBeTrue();               // another user is unaffected
});

test('stopping a timer remembers it as the last stopped timer', function () {
    $task = timerTask([
        'is_running' => true,
        'timer_started_at' => now()->subSeconds(120),
        'total_seconds' => 0,
    ]);

    $this->actingAs($task->project->client->user)
        ->post(route('clients.projects.tasks.stopTimer', [$task->project->client, $task->project, $task]))
        ->assertCookie('last_timer');
});

test('stopping banks whole integer seconds (no fractional Carbon diff leaks)', function () {
    // timer_started_at persists at whole-second precision, but Carbon 3's
    // diffInSeconds(now()) is a float — without an (int) cast the fraction leaks
    // into the last_timer cookie and renders as "04:15:4.2241880...".
    $task = timerTask([
        'is_running' => true,
        'timer_started_at' => now()->subSeconds(120),
        'total_seconds' => 0,
    ]);

    $response = $this->actingAs($task->project->client->user)
        ->post(route('clients.projects.tasks.stopTimer', [$task->project->client, $task->project, $task]));

    // last_timer is an unencrypted cookie, so its value is the raw JSON payload.
    $cookie = collect($response->headers->getCookies())->first(fn ($c) => $c->getName() === 'last_timer');
    $payload = json_decode($cookie->getValue(), true);

    expect($payload['total_seconds'])->toBeInt();
});

test('the last stopped timer is shared to the frontend from its cookie', function () {
    $payload = json_encode([
        'client_id' => 1,
        'project_id' => 2,
        'task_id' => 3,
        'task_title' => 'Wireframes',
        'project_name' => 'Website',
        'total_seconds' => 900,
    ]);

    $this->actingAs(User::factory()->create())
        ->withUnencryptedCookie('last_timer', $payload)
        ->get('/dashboard')
        ->assertInertia(fn ($page) => $page
            ->where('lastTimer.task_id', 3)
            ->where('lastTimer.task_title', 'Wireframes')
            ->where('lastTimer.total_seconds', 900)
        );
});

test('starting a timer forgets the last stopped timer bar', function () {
    $task = timerTask(['is_running' => false, 'timer_started_at' => null]);

    $this->actingAs($task->project->client->user)
        ->withUnencryptedCookie('last_timer', json_encode(['task_id' => 99]))
        ->post(route('clients.projects.tasks.startTimer', [$task->project->client, $task->project, $task]))
        ->assertCookieExpired('last_timer');
});

test('dismissing forgets the last stopped timer cookie', function () {
    $this->actingAs(User::factory()->create())
        ->withUnencryptedCookie('last_timer', json_encode(['task_id' => 99]))
        ->post(route('timer.dismiss'))
        ->assertCookieExpired('last_timer');
});
