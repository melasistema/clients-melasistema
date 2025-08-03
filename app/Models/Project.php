<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    protected $fillable = [
        'name',
        'description',
        'hourly_rate',
        'paid_at',
    ];

    protected $appends = ['total_seconds', 'total_earnings'];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function getTotalSecondsAttribute(): int
    {
        return $this->tasks->sum('total_seconds');
    }

    public function getTotalEarningsAttribute(): float
    {
        return ($this->total_seconds / 3600) * $this->hourly_rate;
    }
}
