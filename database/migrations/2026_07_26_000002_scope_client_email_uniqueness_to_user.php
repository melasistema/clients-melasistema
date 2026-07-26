<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Contact email was globally unique, so two freelancers could not both keep the
 * same end-client (e.g. hello@acme.com) — the second insert collided. Ownership
 * is per user, so uniqueness should be too: scope it to (user_id, contact_email).
 *
 * Safe on existing data: the old global unique already implied per-user
 * uniqueness, so no current rows can violate the new composite index; relaxing a
 * global constraint to a composite one never introduces a conflict.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropUnique(['contact_email']);
            $table->unique(['user_id', 'contact_email']);
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'contact_email']);
            $table->unique(['contact_email']);
        });
    }
};
