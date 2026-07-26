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
