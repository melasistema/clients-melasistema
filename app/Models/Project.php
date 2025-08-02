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

    protected $appends = ['total_minutes', 'total_earnings'];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function getTotalMinutesAttribute(): int
    {
        return $this->tasks->sum('minutes');
    }

    public function getTotalEarningsAttribute(): float
    {
        return ($this->total_minutes / 60) * $this->hourly_rate;
    }
}
