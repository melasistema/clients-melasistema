<?php

namespace App\Models\Concerns;

use App\Models\Attachment;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Gives a model a polymorphic `attachments` relation (files + links) and wires
 * its own cleanup: because a morph has no DB foreign key, the cascade that
 * purges a soft-deletable parent never reaches its attachments — so on
 * `forceDelete` we purge them here (each row's `deleting` hook removes the file).
 *
 * This covers a model's OWN attachments. A parent whose children are removed by
 * the *DB* cascade (Project -> tasks, Client -> projects/tasks) must additionally
 * purge those descendants' attachments in its own `forceDeleting` hook, since a
 * DB-level cascade bypasses the children's Eloquent events entirely.
 */
trait HasAttachments
{
    public static function bootHasAttachments(): void
    {
        static::forceDeleting(function ($model) {
            $model->attachments()->get()->each->delete();
        });
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable')->orderBy('position');
    }
}
