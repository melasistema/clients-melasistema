<?php

use App\Models\Client;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * The clients/projects listings serialize the whole hierarchy, which triggers the
 * appended earnings accessors — including each task's `this_task_total_entry`, which
 * reads `$this->project->hourly_rate`. `Project::tasks()->chaperone()` hydrates that
 * inverse parent in memory during eager loading; without it, serialization fires one
 * query per task. These tests pin the query count so the N+1 cannot silently return.
 */
test('serializing the client hierarchy does not scale queries with task count', function () {
    $user = User::factory()->create();
    $client = Client::factory()->for($user)->create();
    $projectA = Project::factory()->for($client)->create();
    $projectB = Project::factory()->for($client)->create();
    Task::factory()->count(6)->for($projectA)->create();
    Task::factory()->count(6)->for($projectB)->create();

    DB::enableQueryLog();

    // Mirror ClientController@index, then force the appended accessors to run.
    $user->clients()->with('projects.tasks')->get()->toArray();

    // clients + projects + tasks = 3 queries, independent of the 12 tasks above.
    expect(DB::getQueryLog())->toHaveCount(3);
});

test('chaperone still yields correct per-task earnings', function () {
    $client = Client::factory()->create();
    $project = Project::factory()->for($client)->create(['hourly_rate' => 50]);
    Task::factory()->for($project)->create(['total_seconds' => 5400]); // 1.5h -> 75

    $task = $project->tasks()->first();

    expect($task->this_task_total_entry)->toBe(75.0)
        ->and($task->relationLoaded('project'))->toBeTrue();
});
