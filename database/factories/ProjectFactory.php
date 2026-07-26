<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    public function definition(): array
    {
        return [
            'client_id' => Client::factory(),
            'name' => fake()->catchPhrase(),
            'description' => fake()->sentence(),
            'hourly_rate' => fake()->randomFloat(2, 40, 120),
            'agreed_fee' => null,
            'completed_at' => null,
        ];
    }

    /**
     * A fixed-price project: an agreed fee makes it bill the fee, not the hours.
     */
    public function fixed(float $fee = 5000): static
    {
        return $this->state(fn () => ['agreed_fee' => $fee]);
    }

    /**
     * Non-billable (personal) work: no fee, zero rate. Still time-tracked.
     */
    public function nonBillable(): static
    {
        return $this->state(fn () => ['hourly_rate' => 0, 'agreed_fee' => null]);
    }

    /**
     * Mark the project completed (work delivered).
     */
    public function completed(): static
    {
        return $this->state(fn () => ['completed_at' => now()]);
    }
}
