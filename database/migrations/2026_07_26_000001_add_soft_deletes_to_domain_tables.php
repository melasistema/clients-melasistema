<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Make the Client -> Project -> Task hierarchy recoverable. Deletes were hard
 * (with DB cascade), so removing a client permanently destroyed its projects,
 * tasks, and the earnings history they carry. Adding `deleted_at` lets the models
 * use SoftDeletes: a delete only flags the row, and every read (scoped route
 * bindings, the earnings rollups) already goes through relations that respect the
 * soft-delete scope, so a trashed parent's subtree is hidden without extra work.
 * Purely additive — safe to run against production data.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', fn (Blueprint $table) => $table->softDeletes());
        Schema::table('projects', fn (Blueprint $table) => $table->softDeletes());
        Schema::table('tasks', fn (Blueprint $table) => $table->softDeletes());
    }

    public function down(): void
    {
        Schema::table('clients', fn (Blueprint $table) => $table->dropSoftDeletes());
        Schema::table('projects', fn (Blueprint $table) => $table->dropSoftDeletes());
        Schema::table('tasks', fn (Blueprint $table) => $table->dropSoftDeletes());
    }
};
