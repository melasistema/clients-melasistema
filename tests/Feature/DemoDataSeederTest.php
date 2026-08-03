<?php

use App\Models\Attachment;
use App\Models\Client;
use App\Models\Payment;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Database\Seeders\DemoDataSeeder;
use Illuminate\Support\Facades\Storage;

// The seeder writes generated placeholder files to the attachments disk — fake
// it so seeding never touches real storage during tests.
beforeEach(fn () => Storage::fake(config('attachments.disk')));

test('it seeds the demo user, clients, projects, tasks and payments', function () {
    $this->seed(DemoDataSeeder::class);

    $this->assertDatabaseHas('users', ['email' => config('demo_data.user.email')]);
    expect(Client::count())->toBe(count(config('demo_data.clients')))
        ->and(Project::count())->toBeGreaterThan(0)
        ->and(Task::count())->toBeGreaterThan(0)
        ->and(Payment::count())->toBeGreaterThan(0)
        ->and(Attachment::count())->toBeGreaterThan(0);

    // Every generated file attachment actually landed on the disk.
    Attachment::where('kind', 'file')->get()->each(
        fn (Attachment $attachment) => Storage::disk($attachment->disk)->assertExists($attachment->path),
    );
});

test('re-running the seeder does not duplicate records (idempotent)', function () {
    $this->seed(DemoDataSeeder::class);

    $users = User::count();
    $clients = Client::count();
    $projects = Project::count();
    $tasks = Task::count();
    $payments = Payment::count();
    $attachments = Attachment::count();

    $this->seed(DemoDataSeeder::class);

    expect(User::count())->toBe($users)
        ->and(Client::count())->toBe($clients)
        ->and(Project::count())->toBe($projects)
        ->and(Task::count())->toBe($tasks)
        ->and(Payment::count())->toBe($payments)
        ->and(Attachment::count())->toBe($attachments);
});

test('it refuses to run in production', function () {
    $this->app->detectEnvironment(fn () => 'production');

    // Invoke the seeder directly so we exercise our own production guard,
    // not the db:seed command's separate confirmation prompt.
    (new DemoDataSeeder)->run();

    expect(User::count())->toBe(0)
        ->and(Client::count())->toBe(0);
});
