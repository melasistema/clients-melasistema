<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The payment ledger: a project has many payments (deposit, milestones,
     * final balance). "Paid" and "outstanding" are derived from summing this
     * table against the project's owed amount — there is no single paid flag.
     *
     * FK is `onDelete('cascade')` like the rest of the hierarchy: dormant under
     * soft delete (trashing a project hides its payments with it), firing only
     * on the project's forceDelete().
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->date('paid_at');
            $table->string('note')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
