<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Task>
 */
class TaskFactory extends Factory
{
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'title' => fake()->sentence(4),
            'description' => fake()->optional()->paragraph(),
            'total_seconds' => fake()->numberBetween(0, 8 * 3600),
            'is_running' => false,
            'timer_started_at' => null,
        ];
    }

    /**
     * A task whose timer is currently running.
     */
    public function running(): static
    {
        return $this->state(fn () => [
            'is_running' => true,
            'timer_started_at' => now(),
        ]);
    }
}
