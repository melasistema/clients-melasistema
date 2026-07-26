<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Two orthogonal lifecycles get first-class, nullable-timestamp columns
     * (matching the repo's `timer_started_at` / `deleted_at` / former `paid_at`
     * idiom): completion for both projects and tasks, plus a fixed `agreed_fee`
     * that flips a project from hourly (owed = time x rate) to fixed-price
     * (owed = the fee). A null fee + a zero rate means non-billable.
     */
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->decimal('agreed_fee', 10, 2)->nullable()->after('hourly_rate');
            $table->timestamp('completed_at')->nullable()->after('agreed_fee');
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->timestamp('completed_at')->nullable()->after('total_seconds');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['agreed_fee', 'completed_at']);
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn('completed_at');
        });
    }
};
