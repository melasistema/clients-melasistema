<?php

use App\Models\Client;
use App\Models\Project;
use App\Models\Task;
use App\Models\TimeEntry;
use App\Models\User;

// Pins the time Report (ReportController): rolls up the dated time_entries ledger
// into hours + billable value, windowed by period, broken down by day and project,
// scoped to the owner and valued at each entry's snapshotted rate.

function reportEntry(User $user, array $entry = [], array $projectAttributes = []): TimeEntry
{
    $client = Client::factory()->create(['user_id' => $user->id]);
    $project = Project::factory()->for($client)->create($projectAttributes);
    $task = Task::factory()->for($project)->create();

    return TimeEntry::factory()->for($task)->create($entry);
}

test('guests are redirected to the login page', function () {
    $this->get('/report')->assertRedirect('/login');
});

test('the report rolls up hours and billable value for the current month', function () {
    $user = User::factory()->create();

    reportEntry($user, [
        'started_at' => now()->startOfMonth()->addDay(),
        'seconds' => 3600,
        'hourly_rate' => 50,
    ]);
    reportEntry($user, [
        'started_at' => now()->startOfMonth()->addDays(2),
        'seconds' => 1800,
        'hourly_rate' => 50,
    ]);

    $this->actingAs($user)
        ->get('/report')
        ->assertInertia(fn ($page) => $page
            ->component('Report/Index')
            ->where('period', 'this_month')
            ->where('stats.total_seconds', 5400)
            ->where('stats.total_value', 75)          // 1h + 0.5h @ 50
            ->where('stats.days_worked', 2)
            ->has('by_day', 2)
            ->has('by_project', 2)
        );
});

test('the report scopes entries to the authenticated user', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();

    reportEntry($user, ['started_at' => now()->startOfMonth()->addDay(), 'seconds' => 3600, 'hourly_rate' => 40]);
    reportEntry($other, ['started_at' => now()->startOfMonth()->addDay(), 'seconds' => 7200, 'hourly_rate' => 40]);

    $this->actingAs($user)
        ->get('/report')
        ->assertInertia(fn ($page) => $page
            ->where('stats.total_seconds', 3600)
            ->has('by_project', 1)
        );
});

test('the period filter windows entries to the selected range', function () {
    $user = User::factory()->create();

    reportEntry($user, ['started_at' => now()->startOfMonth()->addDay(), 'seconds' => 3600, 'hourly_rate' => 30]);
    reportEntry($user, ['started_at' => now()->subMonthNoOverflow()->startOfMonth()->addDay(), 'seconds' => 1800, 'hourly_rate' => 30]);

    // Default: current month only.
    $this->actingAs($user)
        ->get('/report')
        ->assertInertia(fn ($page) => $page->where('stats.total_seconds', 3600));

    // Last month only.
    $this->actingAs($user)
        ->get('/report?period=last_month')
        ->assertInertia(fn ($page) => $page
            ->where('period', 'last_month')
            ->where('stats.total_seconds', 1800)
        );

    // All time sees both sessions.
    $this->actingAs($user)
        ->get('/report?period=all_time')
        ->assertInertia(fn ($page) => $page
            ->where('period', 'all_time')
            ->where('stats.total_seconds', 5400)
        );
});

test('daily breakdown groups sessions on the same day', function () {
    $user = User::factory()->create();

    $day = now()->startOfMonth()->addDays(3);
    reportEntry($user, ['started_at' => $day->copy()->setTime(9, 0), 'seconds' => 3600, 'hourly_rate' => 20]);
    reportEntry($user, ['started_at' => $day->copy()->setTime(14, 0), 'seconds' => 1800, 'hourly_rate' => 20]);

    $this->actingAs($user)
        ->get('/report')
        ->assertInertia(fn ($page) => $page
            ->where('stats.days_worked', 1)
            ->has('by_day', 1)
            ->where('by_day.0.seconds', 5400)
        );
});

test('billable value uses each entry snapshotted rate', function () {
    $user = User::factory()->create();

    reportEntry($user, ['started_at' => now()->startOfMonth()->addDay(), 'seconds' => 3600, 'hourly_rate' => 42.50]);

    $this->actingAs($user)
        ->get('/report')
        ->assertInertia(fn ($page) => $page->where('stats.total_value', 42.5));
});
