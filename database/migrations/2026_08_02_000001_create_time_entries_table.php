<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A dated ledger of banked work sessions. A task keeps its `total_seconds`
     * running cache untouched; every stop / switch / complete additionally writes
     * one row here — the session's start, end, elapsed seconds, and the project's
     * `hourly_rate` snapshotted at that moment — so a report can break work down by
     * day and value historical hours at the rate in force when they were worked.
     *
     * There is deliberately NO backfill: pre-feature tasks have no dated session
     * history to reconstruct (inventing one would fabricate dates that never
     * happened), so the ledger simply starts accruing from launch forward while
     * `total_seconds` remains the all-time total. Creating this empty table cannot
     * touch a single existing row.
     */
    public function up(): void
    {
        Schema::create('time_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->onDelete('cascade');
            $table->timestamp('started_at');
            $table->timestamp('ended_at');
            $table->integer('seconds');
            // Rate snapshot: the project's hourly_rate when this session was banked,
            // so a later rate change never re-prices already-worked hours. Stored as
            // decimal to match the repo's money idiom (cents only in computation).
            $table->decimal('hourly_rate', 8, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('time_entries');
    }
};
