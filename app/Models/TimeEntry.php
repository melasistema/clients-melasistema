<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimeEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'started_at',
        'ended_at',
        'seconds',
        'hourly_rate',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'seconds' => 'integer',
        // Canonical 2-decimal money in every environment (see Project::hourly_rate).
        'hourly_rate' => 'decimal:2',
    ];

    /**
     * Never serialize the parent task back down when an entry is nested under it —
     * same defensive guard as Task / Payment.
     */
    protected $hidden = ['task'];

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    /**
     * This session's billable value in whole cents, computed from the rate
     * snapshotted when it was banked — so a later rate change never re-prices past
     * work. The report layer reads this; project earnings still roll up from
     * `total_seconds` at the current rate (unchanged by this feature).
     */
    public function valueInCents(): int
    {
        $rateCents = (int) round(((float) $this->hourly_rate) * 100);

        return (int) round($this->seconds * $rateCents / 3600);
    }
}
