<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Force-deleting a client purges its whole project/task subtree via the DB
     * cascade, which bypasses those models' Eloquent events — so purge every
     * descendant's attachment files here. (Clients carry no attachments of their
     * own yet.)
     */
    protected static function booted(): void
    {
        static::forceDeleting(function (Client $client) {
            $client->projects()->withTrashed()->get()->each(function (Project $project) {
                $project->attachments()->get()->each->delete();
                $project->tasks()->withTrashed()->get()
                    ->each(fn (Task $task) => $task->attachments()->get()->each->delete());
            });
        });
    }

    protected $fillable = [
        'company_name',
        'contact_name',
        'contact_email',
        'contact_phone',
        'address',
        'vat_number',
        'unique_code',
        'user_id',
    ];

    protected $appends = ['total_earnings'];

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getTotalEarningsAttribute(): float
    {
        // Sum in integer cents (see Project::totalEarningsInCents) so the
        // client-level rollup can't accumulate floating-point error.
        return $this->projects->sum(fn (Project $project): int => $project->totalEarningsInCents()) / 100;
    }
}
