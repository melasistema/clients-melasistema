<?php

use App\Models\Client;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;

/**
 * Every domain action must be scoped to the owner. These tests act as a second
 * user ("attacker") against records owned by someone else and assert 403 across
 * the whole Client -> Project -> Task hierarchy, including the timer endpoints.
 *
 * Before this suite existed the nested controllers resolved models straight from
 * the URL with no ownership check (IDOR): any authenticated user could read,
 * edit, or delete any other user's data by guessing IDs.
 */
function ownedChain(): array
{
    $owner = User::factory()->create();
    $client = Client::factory()->for($owner)->create();
    $project = Project::factory()->for($client)->create();
    $task = Task::factory()->for($project)->create();

    return [$owner, $client, $project, $task];
}

test('a user cannot view, edit, or delete another user\'s client', function () {
    [, $client] = ownedChain();
    $attacker = User::factory()->create();

    $this->actingAs($attacker)->get(route('clients.edit', $client))->assertForbidden();
    $this->actingAs($attacker)->put(route('clients.update', $client), [])->assertForbidden();
    $this->actingAs($attacker)->delete(route('clients.destroy', $client))->assertForbidden();

    $this->assertModelExists($client);
});

test('a user cannot touch another user\'s projects', function () {
    [, $client, $project] = ownedChain();
    $attacker = User::factory()->create();

    $this->actingAs($attacker)->get(route('clients.projects.index', $client))->assertForbidden();
    $this->actingAs($attacker)->get(route('clients.projects.create', $client))->assertForbidden();
    $this->actingAs($attacker)->post(route('clients.projects.store', $client), [])->assertForbidden();
    $this->actingAs($attacker)->get(route('clients.projects.edit', [$client, $project]))->assertForbidden();
    $this->actingAs($attacker)->put(route('clients.projects.update', [$client, $project]), [])->assertForbidden();
    $this->actingAs($attacker)->delete(route('clients.projects.destroy', [$client, $project]))->assertForbidden();

    $this->assertModelExists($project);
});

test('a user cannot touch another user\'s tasks or timers', function () {
    [, $client, $project, $task] = ownedChain();
    $attacker = User::factory()->create();

    $this->actingAs($attacker)->get(route('clients.projects.tasks.index', [$client, $project]))->assertForbidden();
    $this->actingAs($attacker)->get(route('clients.projects.tasks.show', [$client, $project, $task]))->assertForbidden();
    $this->actingAs($attacker)->post(route('clients.projects.tasks.store', [$client, $project]), [])->assertForbidden();
    $this->actingAs($attacker)->put(route('clients.projects.tasks.update', [$client, $project, $task]), [])->assertForbidden();
    $this->actingAs($attacker)->delete(route('clients.projects.tasks.destroy', [$client, $project, $task]))->assertForbidden();
    $this->actingAs($attacker)->post(route('clients.projects.tasks.startTimer', [$client, $project, $task]))->assertForbidden();
    $this->actingAs($attacker)->post(route('clients.projects.tasks.stopTimer', [$client, $project, $task]))->assertForbidden();

    $this->assertModelExists($task);
});

test('the owner can access their own hierarchy', function () {
    [$owner, $client, $project, $task] = ownedChain();

    $this->actingAs($owner)->get(route('clients.projects.index', $client))->assertOk();
    $this->actingAs($owner)->get(route('clients.projects.tasks.index', [$client, $project]))->assertOk();
    $this->actingAs($owner)->get(route('clients.projects.tasks.show', [$client, $project, $task]))->assertOk();
});

test('route scoping rejects a project that does not belong to the client in the url', function () {
    [$owner, $client] = ownedChain();
    // A project the owner owns, but under a *different* client than the URL names.
    $otherClient = Client::factory()->for($owner)->create();
    $strayProject = Project::factory()->for($otherClient)->create();

    $this->actingAs($owner)
        ->get(route('clients.projects.edit', [$client, $strayProject]))
        ->assertNotFound();
});

test('route scoping rejects a task that does not belong to the project in the url', function () {
    [$owner, $client, $project] = ownedChain();
    $otherProject = Project::factory()->for($client)->create();
    $strayTask = Task::factory()->for($otherProject)->create();

    $this->actingAs($owner)
        ->post(route('clients.projects.tasks.startTimer', [$client, $project, $strayTask]))
        ->assertNotFound();
});
