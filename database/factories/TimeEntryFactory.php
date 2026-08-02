<?php

namespace Database\Factories;

use App\Models\Task;
use App\Models\TimeEntry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TimeEntry>
 */
class TimeEntryFactory extends Factory
{
    public function definition(): array
    {
        $seconds = fake()->numberBetween(60, 7200);

        return [
            'task_id' => Task::factory(),
            'started_at' => now()->subSeconds($seconds),
            'ended_at' => now(),
            'seconds' => $seconds,
            'hourly_rate' => fake()->randomFloat(2, 0, 150),
        ];
    }
}
