<?php

use App\Models\Project;
use App\Models\Task;
use Illuminate\Database\Eloquent\Collection;

// Billing mode is derived from two fields (agreed_fee + hourly_rate), and it
// decides what a project is "owed" / earns. These run in memory (no DB).

test('a project with an agreed fee is fixed-price', function () {
    $project = new Project(['hourly_rate' => 95, 'agreed_fee' => 12000]);

    expect($project->billing_mode)->toBe('fixed');
});

test('a project with a positive rate and no fee is hourly', function () {
    $project = new Project(['hourly_rate' => 80, 'agreed_fee' => null]);

    expect($project->billing_mode)->toBe('hourly');
});

test('a project with a zero rate and no fee is non-billable', function () {
    $project = new Project(['hourly_rate' => 0, 'agreed_fee' => null]);

    expect($project->billing_mode)->toBe('non_billable');
});

test('a fixed-price project earns its fee, not its tracked time', function () {
    $project = new Project(['hourly_rate' => 95, 'agreed_fee' => 12000]);
    $project->setRelation('tasks', new Collection([
        new Task(['total_seconds' => 3600]), // 1h of effort — must not change earnings
    ]));

    expect($project->total_earnings)->toBe(12000.0);
});

test('an hourly project earns its tracked time value', function () {
    $project = new Project(['hourly_rate' => 80, 'agreed_fee' => null]);
    $project->setRelation('tasks', new Collection([
        new Task(['total_seconds' => 3600]), // 1h -> 80
        new Task(['total_seconds' => 1800]), // .5h -> 40
    ]));

    expect($project->total_earnings)->toBe(120.0);
});

test('a non-billable project earns nothing regardless of tracked time', function () {
    $project = new Project(['hourly_rate' => 0, 'agreed_fee' => null]);
    $project->setRelation('tasks', new Collection([
        new Task(['total_seconds' => 20000]),
    ]));

    expect($project->total_earnings)->toBe(0.0)
        ->and($project->total_tracked_seconds)->toBe(20000);
});
