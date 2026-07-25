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

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function getTotalEarningsAttribute(): float
    {
        return ($this->tasks->sum('total_seconds') / 3600) * $this->hourly_rate;
    }
}
