<?php

use App\Models\Client;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Database\Eloquent\Collection;

// Earnings are computed by Eloquent accessors that roll up the hierarchy:
// task seconds x project hourly_rate -> project total -> client total.
// These run entirely in memory (no DB) to keep them fast and focused on the math.

test('a task computes its own earning from seconds and the project rate', function () {
    $project = new Project(['hourly_rate' => 50]);
    $task = new Task(['total_seconds' => 5400]); // 1.5h
    $task->setRelation('project', $project);

    expect($task->this_task_total_entry)->toBe(75.0);
});

test('a project sums its tasks earnings', function () {
    $project = new Project(['hourly_rate' => 80]);
    $project->setRelation('tasks', new Collection([
        new Task(['total_seconds' => 3600]),  // 1h  -> 80
        new Task(['total_seconds' => 1800]),  // .5h -> 40
    ]));

    expect($project->total_earnings)->toBe(120.0);
});

test('a client sums its projects earnings', function () {
    $projectA = new Project(['hourly_rate' => 100]);
    $projectA->setRelation('tasks', new Collection([new Task(['total_seconds' => 3600])])); // 100

    $projectB = new Project(['hourly_rate' => 60]);
    $projectB->setRelation('tasks', new Collection([new Task(['total_seconds' => 1800])])); // 30

    $client = new Client;
    $client->setRelation('projects', new Collection([$projectA, $projectB]));

    expect($client->total_earnings)->toBe(130.0);
});

test('earnings are zero when there are no tasks', function () {
    $project = new Project(['hourly_rate' => 90]);
    $project->setRelation('tasks', new Collection);

    expect($project->total_earnings)->toBe(0.0);
});
