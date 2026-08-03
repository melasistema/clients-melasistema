<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Split a task's single `description` into a short `title` (list headline,
     * shown in the timer bar) and a longer, optional `description` body (shown on
     * the new task detail page).
     *
     * Kept deliberately simple — there are very few real task rows, so instead of
     * an elaborate data-safety dance we accept a small, clean loss: the existing
     * single value becomes the title, and the body starts empty rather than
     * echoing the title on the detail page.
     */
    public function up(): void
    {
        // 1. Add `title` non-null with a default, so it's safe on existing rows.
        Schema::table('tasks', function (Blueprint $table) {
            $table->string('title')->default('')->after('project_id');
        });

        // 2. The existing single value *is* the headline — copy it across.
        DB::table('tasks')->update(['title' => DB::raw('description')]);

        // 3. Widen `description` into an optional long body.
        Schema::table('tasks', function (Blueprint $table) {
            $table->text('description')->nullable()->change();
        });

        // 4. Clear the now-duplicated body (deliberate small loss for elegance).
        DB::table('tasks')->update(['description' => null]);
    }

    /**
     * Reverse: fold the title back into the description, then drop the title.
     */
    public function down(): void
    {
        DB::table('tasks')->update([
            'description' => DB::raw('COALESCE(description, title)'),
        ]);

        Schema::table('tasks', function (Blueprint $table) {
            $table->string('description')->nullable(false)->change();
            $table->dropColumn('title');
        });
    }
};
