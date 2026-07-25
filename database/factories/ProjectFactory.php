<?php

namespace Database\Factories;

use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Project>
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
            'paid_at' => null,
        ];
    }

    /**
     * Mark the project as already paid.
     */
    public function paid(): static
    {
        return $this->state(fn () => ['paid_at' => now()]);
    }
}
