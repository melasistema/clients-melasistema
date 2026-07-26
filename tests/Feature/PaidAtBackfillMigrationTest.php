<?php

use App\Models\Client;
use App\Models\Payment;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// The migration that retires projects.paid_at must fold each legacy "paid"
// project into one ledger payment (for its then-current earnings) and stamp it
// completed — preserving real production history. RefreshDatabase has already
// dropped paid_at, so we re-create the pre-migration shape, seed a paid project,
// then run the migration's up() and assert the backfill.
test('the paid_at migration backfills a payment and completes the project', function () {
    $project = Project::factory()->for(Client::factory())->create(['hourly_rate' => 100]);
    Task::factory()->for($project)->create(['total_seconds' => 3600]); // 1h -> €100
    Task::factory()->for($project)->create(['total_seconds' => 1800]); // .5h -> €50

    // Re-introduce the legacy column and mark the project paid 20 days ago.
    Schema::table('projects', fn (Blueprint $table) => $table->timestamp('paid_at')->nullable());
    DB::table('projects')->where('id', $project->id)->update(['paid_at' => now()->subDays(20)]);

    $migration = require database_path('migrations/2026_07_26_000005_backfill_payments_and_drop_project_paid_at.php');
    $migration->up();

    // One payment for the full €150 earnings, project stamped completed, column gone.
    $project->refresh()->loadMissing('payments');
    expect($project->completed_at)->not->toBeNull()
        ->and($project->payments)->toHaveCount(1)
        ->and($project->amount_paid)->toBe(150.0)
        ->and($project->is_fully_paid)->toBeTrue()
        ->and(Schema::hasColumn('projects', 'paid_at'))->toBeFalse();

    $payment = Payment::first();
    expect((string) $payment->amount)->toBe('150.00')
        ->and($payment->note)->toBe('Imported from legacy paid marker');
});
