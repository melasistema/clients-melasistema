<?php

use App\Models\Client;
use App\Models\Payment;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;

test('guests are redirected to the login page', function () {
    $response = $this->get('/dashboard');
    $response->assertRedirect('/login');
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get('/dashboard');
    $response->assertStatus(200);
});

test('the dashboard rolls up outstanding, received and tracked time for the owner', function () {
    $user = User::factory()->create();
    $client = Client::factory()->create(['user_id' => $user->id]);

    // Fixed-price, delivered (completed) project: owes 1000, paid 700 → 300 out.
    $delivered = Project::factory()->fixed(1000)->completed()->create(['client_id' => $client->id]);
    Payment::factory()->create(['project_id' => $delivered->id, 'amount' => 400, 'paid_at' => now()]);
    Payment::factory()->create(['project_id' => $delivered->id, 'amount' => 300, 'paid_at' => now()->startOfMonth()->subDay()]);

    // Hourly, still open: 1h @ 100 → owes 100, nothing paid → 100 out. Not delivered.
    $open = Project::factory()->create(['client_id' => $client->id, 'hourly_rate' => 100, 'agreed_fee' => null]);
    Task::factory()->create(['project_id' => $open->id, 'total_seconds' => 3600]);

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertInertia(fn ($page) => $page
            ->component('Dashboard')
            ->where('stats.outstanding', 400)                 // 300 + 100
            ->where('stats.outstanding_projects_count', 2)
            ->where('stats.received_this_month', 400)          // only the current-month payment
            ->where('stats.received_all_time', 700)
            ->where('stats.tracked_seconds', 3600)
            // Awaiting = delivered-but-unpaid only: the open project is excluded.
            ->has('awaiting_payment', 1)
            ->where('awaiting_payment.0.project_name', $delivered->name)
            ->where('awaiting_payment.0.outstanding', 300)
            ->has('recent_payments', 2)
            ->where('activeTimer', null)
        );
});

test('the running timer is shared as app chrome on every page', function () {
    $user = User::factory()->create();
    $client = Client::factory()->create(['user_id' => $user->id]);
    $project = Project::factory()->create(['client_id' => $client->id]);
    $task = Task::factory()->running()->create(['project_id' => $project->id]);

    // `activeTimer` is shared by HandleInertiaRequests, not a dashboard prop, so
    // the persistent LiveTimer renders it on any authenticated page. Assert it via
    // the shared prop (here on the clients index, deliberately not the dashboard).
    $this->actingAs($user)
        ->get('/clients')
        ->assertInertia(fn ($page) => $page
            ->where('activeTimer.task_id', $task->id)
            ->where('activeTimer.project_id', $project->id)
            ->where('activeTimer.client_id', $client->id)
            ->where('activeTimer.task_description', $task->description)
        );
});

test('the dashboard only counts the acting user\'s data', function () {
    $me = User::factory()->create();

    // Another freelancer's delivered, unpaid project must not touch my rollups.
    $other = User::factory()->create();
    $otherClient = Client::factory()->create(['user_id' => $other->id]);
    Project::factory()->fixed(9999)->completed()->create(['client_id' => $otherClient->id]);

    $this->actingAs($me)
        ->get('/dashboard')
        ->assertInertia(fn ($page) => $page
            ->where('stats.outstanding', 0)
            ->where('stats.outstanding_projects_count', 0)
            ->has('awaiting_payment', 0)
            ->has('recent_payments', 0)
            ->where('activeTimer', null)
        );
});
