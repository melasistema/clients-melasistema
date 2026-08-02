<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'description',
        'total_seconds',
        'is_running',
        'timer_started_at',
        'completed_at',
    ];

    protected $casts = [
        'is_running' => 'boolean',
        'timer_started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    protected $appends = ['this_task_total_entry', 'is_completed'];

    /**
     * The `this_task_total_entry` accessor reads `$this->project->hourly_rate`,
     * which lazy-loads the parent project onto this task. Hide it so serialization
     * never walks back up the chain (task -> project -> tasks -> ...) into infinite
     * recursion. The frontend reads the appended totals, not the nested parent.
     */
    protected $hidden = ['project'];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * The dated ledger of banked work sessions. `total_seconds` stays the all-time
     * cache; each stop / switch / complete appends one entry here (with the rate
     * snapshotted). Deliberately kept out of `$appends` so the listings never
     * serialize it — the report queries entries explicitly, so no N+1 is introduced.
     */
    public function timeEntries(): HasMany
    {
        return $this->hasMany(TimeEntry::class);
    }

    /**
     * This task's earning in whole cents — integer money math (see
     * Project::totalEarningsInCents) so serialized totals never carry float
     * artifacts. The euro accessor divides by 100 at the boundary.
     */
    public function thisTaskTotalEntryInCents(): int
    {
        $rateCents = (int) round(((float) $this->project->hourly_rate) * 100);

        return (int) round($this->total_seconds * $rateCents / 3600);
    }

    public function getThisTaskTotalEntryAttribute(): float
    {
        return $this->thisTaskTotalEntryInCents() / 100;
    }

    public function getIsCompletedAttribute(): bool
    {
        return $this->completed_at !== null;
    }
}
