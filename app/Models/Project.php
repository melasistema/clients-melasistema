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
     * Cast the money column so it is a canonical 2-decimal value in every
     * environment. Without this, MySQL (production) returns `hourly_rate` as a
     * string while SQLite (tests) returns a float — a silent dev/prod divergence.
     */
    protected $casts = [
        'hourly_rate' => 'decimal:2',
    ];

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

    /**
     * This project's earnings in whole cents. Money is accumulated as integers
     * so the rollup (task -> project -> client) never drifts on floating point;
     * the euro accessors below divide by 100 only at the boundary. Rounding
     * happens once, on the summed seconds, so a project total is derived directly
     * from its worked time rather than from re-rounded per-task figures.
     */
    public function totalEarningsInCents(): int
    {
        $rateCents = (int) round(((float) $this->hourly_rate) * 100);

        return (int) round($this->tasks->sum('total_seconds') * $rateCents / 3600);
    }

    public function getTotalEarningsAttribute(): float
    {
        return $this->totalEarningsInCents() / 100;
    }
}
