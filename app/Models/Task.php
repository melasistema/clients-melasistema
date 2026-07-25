<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'description',
        'total_seconds',
        'is_running',
        'timer_started_at',
    ];

    protected $casts = [
        'is_running' => 'boolean',
        'timer_started_at' => 'datetime',
    ];

    protected $appends = ['this_task_total_entry'];

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

    public function getThisTaskTotalEntryAttribute(): float
    {
        return ($this->total_seconds / 3600) * $this->project->hourly_rate;
    }
}
