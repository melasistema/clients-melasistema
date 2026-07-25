<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Client>
 */
class ClientFactory extends Factory
{
    public function definition(): array
    {
        return [
            'company_name' => fake()->company(),
            'contact_name' => fake()->name(),
            'contact_email' => fake()->unique()->companyEmail(),
            'contact_phone' => fake()->phoneNumber(),
            'address' => fake()->address(),
            'vat_number' => strtoupper(fake()->bothify('??#########')),
            'unique_code' => strtoupper(fake()->bothify('???-###')),
            'user_id' => User::factory(),
        ];
    }
}
