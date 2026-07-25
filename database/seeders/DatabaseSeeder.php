<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Only demo/development data is registered here, and DemoDataSeeder
     * guards itself against production. Keep production-only seeders (if any
     * are ever added) explicitly outside that guard.
     */
    public function run(): void
    {
        $this->call(DemoDataSeeder::class);
    }
}
