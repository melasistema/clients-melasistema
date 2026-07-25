<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    use HasFactory;

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
        return $this->projects->sum('total_earnings');
    }
}
