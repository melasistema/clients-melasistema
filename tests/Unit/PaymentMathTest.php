<?php

use App\Models\Payment;
use App\Models\Project;
use Illuminate\Database\Eloquent\Collection;

// "Amount paid" / "outstanding" / "fully paid" are derived by summing the
// payment ledger in integer cents against what the project is owed. In memory.

test('amount paid sums the ledger in whole cents without float drift', function () {
    $project = new Project(['agreed_fee' => 5000]);
    $project->setRelation('payments', new Collection([
        new Payment(['amount' => 1500.10]),
        new Payment(['amount' => 1000.05]),
    ]));

    expect($project->amount_paid)->toBe(2500.15);
});

test('outstanding is owed minus paid', function () {
    $project = new Project(['agreed_fee' => 5000]);
    $project->setRelation('payments', new Collection([
        new Payment(['amount' => 3600]), // 30% deposit
    ]));

    expect($project->outstanding)->toBe(1400.0);
});

test('a project is fully paid once the ledger covers what is owed', function () {
    $project = new Project(['agreed_fee' => 7000]);
    $project->setRelation('payments', new Collection([
        new Payment(['amount' => 3500]),
        new Payment(['amount' => 3500]),
    ]));

    expect($project->is_fully_paid)->toBeTrue()
        ->and($project->outstanding)->toBe(0.0);
});

test('a partially paid project is not fully paid', function () {
    $project = new Project(['agreed_fee' => 7000]);
    $project->setRelation('payments', new Collection([
        new Payment(['amount' => 3500]),
    ]));

    expect($project->is_fully_paid)->toBeFalse();
});

test('a non-billable project owes nothing and is never fully paid', function () {
    $project = new Project(['hourly_rate' => 0, 'agreed_fee' => null]);
    $project->setRelation('payments', new Collection);

    expect($project->outstanding)->toBe(0.0)
        ->and($project->is_fully_paid)->toBeFalse();
});
