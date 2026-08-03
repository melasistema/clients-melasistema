<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One polymorphic table for everything a task (and, later, a project) carries
 * alongside the work: uploaded files (screenshots, PDFs, HTML exports) and
 * external links (Figma, staging URLs). A single `kind` column splits the two.
 *
 * A file row stores `disk` + a RELATIVE `path` (never an absolute path or a
 * baked URL) so the same row resolves through `Storage::disk($disk)` whatever
 * the environment — Sail, S3, or a future NativePHP desktop build. Files live on
 * a private disk and are served only through an authorized streaming route.
 *
 * No soft deletes on purpose: there is no attachment trash, and a soft-deleted
 * file row would orphan its bytes on disk forever. Deleting an attachment is
 * immediate and purges the file (see the model's `deleting` hook), so bytes
 * never outlive their row.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attachments', function (Blueprint $table) {
            $table->id();
            // attachable_type + attachable_id (indexed together). No DB foreign
            // key — a morph can't have one — so parent force-deletes purge these
            // rows/files explicitly in the models' forceDeleting hooks.
            $table->morphs('attachable');
            // Denormalized owner: an O(1) tenancy check on the hot streaming route
            // (no walk up the morph chain per image request), robust even when the
            // parent task/project is soft-deleted.
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('kind', ['file', 'link']);
            // File columns (null for links).
            $table->string('disk')->nullable();
            $table->string('path')->nullable();
            $table->string('original_filename')->nullable();
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size_bytes')->nullable();
            $table->string('sha256', 64)->nullable(); // integrity / future dedup
            // Link column (null for files).
            $table->string('url')->nullable();
            // A link's label or an optional file caption.
            $table->string('title')->nullable();
            // Manual ordering within a parent (newest appended last by default).
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attachments');
    }
};
