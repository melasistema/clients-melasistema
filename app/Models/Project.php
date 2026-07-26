<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'hourly_rate',
        'agreed_fee',
        'completed_at',
    ];

    protected $appends = [
        'billing_mode',
        'total_earnings',
        'total_tracked_seconds',
        'amount_paid',
        'outstanding',
        'is_completed',
        'is_fully_paid',
    ];

    /**
     * Cast the money columns so they are canonical 2-decimal values in every
     * environment. Without this, MySQL (production) returns them as strings
     * while SQLite (tests) returns floats — a silent dev/prod divergence.
     */
    protected $casts = [
        'hourly_rate' => 'decimal:2',
        'agreed_fee' => 'decimal:2',
        'completed_at' => 'datetime',
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
     * The payment ledger (deposit, milestones, final balance). The appended
     * `amount_paid` / `outstanding` accessors read this, so any listing that
     * serializes projects must eager-load `payments` to avoid an N+1 —
     * EarningsQueryTest pins that.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * A project's billing mode is *derived*, not stored — one less field to keep
     * in sync. An agreed fee makes it fixed-price; otherwise a positive rate makes
     * it hourly; a zero rate with no fee is non-billable (personal work you still
     * want to time-track, e.g. a "Myself" client at 0.00/h).
     */
    public function getBillingModeAttribute(): string
    {
        if ($this->agreed_fee !== null) {
            return 'fixed';
        }

        return ((float) $this->hourly_rate) > 0 ? 'hourly' : 'non_billable';
    }

    /**
     * Total seconds tracked across this project's tasks. For non-billable work
     * this — not money — is the headline figure.
     */
    public function getTotalTrackedSecondsAttribute(): int
    {
        return (int) $this->tasks->sum('total_seconds');
    }

    /**
     * The reference value of the tracked time (seconds x rate), always computed
     * from hours regardless of billing mode. On a fixed-price project this is the
     * *effort* behind the fee (drives an effective-rate display); it never changes
     * what the client owes.
     */
    public function trackedValueInCents(): int
    {
        $rateCents = (int) round(((float) $this->hourly_rate) * 100);

        return (int) round($this->tasks->sum('total_seconds') * $rateCents / 3600);
    }

    /**
     * What the client owes for this project, in whole cents: the agreed fee when
     * fixed-price, the tracked time value when hourly, nothing when non-billable.
     * This is the single source of truth for the project's earnings — the euro
     * accessors and the client rollup all divide it by 100 only at the boundary.
     */
    public function owedInCents(): int
    {
        return match ($this->billing_mode) {
            'fixed' => (int) round(((float) $this->agreed_fee) * 100),
            'hourly' => $this->trackedValueInCents(),
            default => 0, // non_billable
        };
    }

    /**
     * Earnings == what is owed. Kept as the canonical name the client rollup and
     * frontend already read; hourly projects behave exactly as before this change.
     */
    public function totalEarningsInCents(): int
    {
        return $this->owedInCents();
    }

    public function getTotalEarningsAttribute(): float
    {
        return $this->totalEarningsInCents() / 100;
    }

    /**
     * Sum of the payment ledger in whole cents.
     */
    public function amountPaidInCents(): int
    {
        return (int) $this->payments->sum(fn (Payment $payment): int => $payment->amountInCents());
    }

    public function getAmountPaidAttribute(): float
    {
        return $this->amountPaidInCents() / 100;
    }

    /**
     * Owed minus paid, in cents. Negative means the client has overpaid.
     */
    public function outstandingInCents(): int
    {
        return $this->owedInCents() - $this->amountPaidInCents();
    }

    public function getOutstandingAttribute(): float
    {
        return $this->outstandingInCents() / 100;
    }

    public function getIsCompletedAttribute(): bool
    {
        return $this->completed_at !== null;
    }

    /**
     * Fully paid only makes sense when something is owed: a billable project whose
     * ledger covers (or exceeds) the owed amount. Non-billable projects owe nothing
     * and are never "fully paid" — nothing locks their completion.
     */
    public function getIsFullyPaidAttribute(): bool
    {
        return $this->owedInCents() > 0 && $this->amountPaidInCents() >= $this->owedInCents();
    }
}
