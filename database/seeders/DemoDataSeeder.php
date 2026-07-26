<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class DemoDataSeeder extends Seeder
{
    /**
     * Seed realistic demo clients, projects and tasks for development.
     *
     * Data lives in config/demo_data.php (single source of truth). This
     * seeder is idempotent — every record is matched on a natural key and
     * updated in place, so it is safe to re-run. It refuses to run in
     * production to protect real users' data.
     */
    public function run(): void
    {
        if (app()->isProduction()) {
            $this->command?->warn('DemoDataSeeder skipped: refusing to seed demo data in production.');

            return;
        }

        $config = config('demo_data');

        $user = User::firstOrCreate(
            ['email' => $config['user']['email']],
            [
                'name' => $config['user']['name'],
                'password' => $config['user']['password'],
                'email_verified_at' => now(),
            ],
        );

        foreach ($config['clients'] as $clientData) {
            $projects = $clientData['projects'] ?? [];
            unset($clientData['projects']);

            $client = Client::updateOrCreate(
                ['contact_email' => $clientData['contact_email']],
                [...$clientData, 'user_id' => $user->id],
            );

            foreach ($projects as $projectData) {
                $tasks = $projectData['tasks'] ?? [];
                $payments = $projectData['payments'] ?? [];
                unset($projectData['tasks'], $projectData['payments']);

                $projectData['completed_at'] = $this->resolveDate($projectData['completed_at'] ?? null);

                $project = $client->projects()->updateOrCreate(
                    ['name' => $projectData['name']],
                    $projectData,
                );

                foreach ($tasks as $taskData) {
                    $project->tasks()->updateOrCreate(
                        ['description' => $taskData['description']],
                        $taskData,
                    );
                }

                // Payments are matched on (project, note) so re-seeding is idempotent —
                // every demo payment carries a distinct note within its project.
                foreach ($payments as $paymentData) {
                    $paymentData['paid_at'] = $this->resolveDate($paymentData['paid_at']);

                    $project->payments()->updateOrCreate(
                        ['note' => $paymentData['note']],
                        $paymentData,
                    );
                }
            }
        }

        $this->command?->info('Demo data seeded for '.$user->email.'.');
    }

    /**
     * Turn a relative string like "-15 days" (or null) into a concrete date.
     */
    private function resolveDate(?string $value): ?Carbon
    {
        return $value === null ? null : Carbon::parse($value);
    }
}
