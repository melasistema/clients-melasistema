<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'hourly_rate',
        'paid_at',
    ];

    protected $appends = ['total_earnings'];

    /**
     * Defensive guard against the same serialization cycle as Task: never serialize
     * the parent client back down when a project is nested under it.
     */
    protected $hidden = ['client'];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * `chaperone()` hydrates each task's inverse `project` relation in memory as
     * the tasks are loaded. Without it, serializing a task's appended
     * `this_task_total_entry` (which reads `$this->project->hourly_rate`) lazy-loads
     * the parent one query per task — an N+1 on every clients/projects listing.
     * The task's `$hidden = ['project']` keeps this hydration out of the JSON.
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class)->chaperone();
    }

    public function getTotalEarningsAttribute(): float
    {
        return ($this->tasks->sum('total_seconds') / 3600) * $this->hourly_rate;
    }
}
