<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;

/**
 * A file or a link attached to a task (or project). `kind` splits the two: a
 * `file` row streams from a private disk through `attachments.show`; a `link`
 * row is just an external URL. See the create migration for the column story.
 */
class Attachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'kind',
        'disk',
        'path',
        'original_filename',
        'mime_type',
        'size_bytes',
        'sha256',
        'url',
        'title',
        'position',
    ];

    protected $casts = [
        'size_bytes' => 'integer',
        'position' => 'integer',
    ];

    /**
     * The frontend needs a stream URL and an "is this an image" hint; it never
     * needs the internal storage location or the parent relations.
     */
    protected $appends = ['is_image', 'stream_url'];

    protected $hidden = ['attachable', 'user', 'disk', 'path', 'sha256'];

    /**
     * A file's bytes are removed the moment its row is deleted — this is the
     * single place that happens, so it's always consistent whether the row is
     * deleted on its own or purged as part of a parent's subtree (see the
     * HasAttachments trait and the Project/Client forceDeleting hooks). There are
     * no soft deletes here, so `delete()` is always permanent.
     */
    protected static function booted(): void
    {
        static::deleting(function (Attachment $attachment) {
            if ($attachment->kind === 'file' && $attachment->path) {
                Storage::disk($attachment->disk)->delete($attachment->path);
            }
        });
    }

    public function attachable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getIsImageAttribute(): bool
    {
        return str_starts_with((string) $this->mime_type, 'image/');
    }

    /**
     * The authorized streaming URL for a file (null for links, which the
     * frontend opens at their own `url`).
     */
    public function getStreamUrlAttribute(): ?string
    {
        return $this->kind === 'file' ? route('attachments.show', $this->id) : null;
    }
}
