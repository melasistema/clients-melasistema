<?php

namespace Database\Factories;

use App\Models\Payment;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'amount' => fake()->randomFloat(2, 100, 2000),
            'paid_at' => fake()->dateTimeBetween('-6 months', 'now')->format('Y-m-d'),
            'note' => fake()->randomElement(['Deposit', 'Milestone', 'Final balance', null]),
        ];
    }
}
