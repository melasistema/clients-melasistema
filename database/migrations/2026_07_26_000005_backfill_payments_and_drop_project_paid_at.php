<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Retire the single `projects.paid_at` boolean-ish marker in favour of the
     * payment ledger, preserving production history: every project that was
     * marked paid becomes one payment for its then-current earnings (summed task
     * seconds x hourly_rate) and is stamped completed. Only then is the column
     * dropped. All existing projects are hourly (no fee predates this change),
     * so owed = time x rate. Query-builder math keeps this SQLite/MySQL-portable.
     */
    public function up(): void
    {
        DB::transaction(function () {
            DB::table('projects')
                ->whereNotNull('paid_at')
                ->orderBy('id')
                ->each(function (object $project) {
                    $seconds = (int) DB::table('tasks')
                        ->where('project_id', $project->id)
                        ->whereNull('deleted_at')
                        ->sum('total_seconds');

                    $amount = round($seconds * (float) $project->hourly_rate / 3600, 2);

                    if ($amount > 0) {
                        DB::table('payments')->insert([
                            'project_id' => $project->id,
                            'amount' => $amount,
                            'paid_at' => $project->paid_at,
                            'note' => 'Imported from legacy paid marker',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }

                    // A paid project was necessarily delivered — mark it completed.
                    DB::table('projects')
                        ->where('id', $project->id)
                        ->update(['completed_at' => $project->paid_at]);
                });
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('paid_at');
        });
    }

    /**
     * Best-effort reverse: restore the column and re-derive paid_at from the
     * completed_at stamp of projects that carry a payment. Individual ledger
     * rows are not reconstructed.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->timestamp('paid_at')->nullable()->after('agreed_fee');
        });

        DB::table('projects')
            ->whereIn('id', fn ($query) => $query->select('project_id')->from('payments'))
            ->update(['paid_at' => DB::raw('completed_at')]);
    }
};
