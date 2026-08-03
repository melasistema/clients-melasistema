<?php

use App\Models\Client;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;

/**
 * A task splits into a required short `title` (the list/timer headline) and an
 * optional long `description` body (shown on the task detail page). These pin
 * the validation and the new detail route.
 */
function ownedProject(): array
{
    $owner = User::factory()->create();
    $client = Client::factory()->for($owner)->create();
    $project = Project::factory()->for($client)->create();

    return [$owner, $client, $project];
}

test('creating a task requires a title but not a description', function () {
    [$owner, $client, $project] = ownedProject();

    $this->actingAs($owner)
        ->post(route('clients.projects.tasks.store', [$client, $project]), [
            'title' => '',
            'total_seconds' => 0,
        ])
        ->assertSessionHasErrors('title');

    $this->actingAs($owner)
        ->post(route('clients.projects.tasks.store', [$client, $project]), [
            'title' => 'Wireframes',
            'total_seconds' => 0,
        ])
        ->assertRedirect(route('clients.projects.tasks.index', [$client, $project]));

    $task = $project->tasks()->firstOrFail();

    expect($task->title)->toBe('Wireframes')
        ->and($task->description)->toBeNull();
});

test('a task can carry a full description body', function () {
    [$owner, $client, $project] = ownedProject();

    $this->actingAs($owner)
        ->post(route('clients.projects.tasks.store', [$client, $project]), [
            'title' => 'Homepage build',
            'description' => "Multi-line\nbody with details.",
            'total_seconds' => 0,
        ])
        ->assertRedirect();

    expect($project->tasks()->firstOrFail()->description)->toBe("Multi-line\nbody with details.");
});

test('the owner sees the task detail page with its title and body', function () {
    [$owner, $client, $project] = ownedProject();
    $task = Task::factory()->for($project)->create([
        'title' => 'Design & content',
        'description' => 'The long story.',
    ]);

    $this->actingAs($owner)
        ->get(route('clients.projects.tasks.show', [$client, $project, $task]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Tasks/Show')
            ->where('task.title', 'Design & content')
            ->where('task.description', 'The long story.')
        );
});

test('route scoping rejects a task detail under the wrong project', function () {
    [$owner, $client, $project] = ownedProject();
    $otherProject = Project::factory()->for($client)->create();
    $strayTask = Task::factory()->for($otherProject)->create();

    $this->actingAs($owner)
        ->get(route('clients.projects.tasks.show', [$client, $project, $strayTask]))
        ->assertNotFound();
});
