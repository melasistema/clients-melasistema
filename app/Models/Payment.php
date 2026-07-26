<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'amount',
        'paid_at',
        'note',
    ];

    protected $casts = [
        // Canonical 2-decimal money in every environment (see Project::hourly_rate).
        'amount' => 'decimal:2',
        'paid_at' => 'date',
    ];

    /**
     * Never serialize the parent project back down when a payment is nested
     * under it — same defensive guard as Task/Project.
     */
    protected $hidden = ['project'];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * This payment's value in whole cents, for the integer-cents rollup that
     * keeps "amount paid" / "outstanding" free of floating-point drift.
     */
    public function amountInCents(): int
    {
        return (int) round(((float) $this->amount) * 100);
    }
}
