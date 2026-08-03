<?php

use App\Models\Attachment;
use App\Models\Client;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Attachments are files (on a private disk, streamed through an authorized route)
 * and links, attached polymorphically to a task. These pin the upload/link flow,
 * the tenancy boundary, the streaming disposition rules (HTML/SVG never inline),
 * and the file-purge-on-force-delete guarantee.
 */
function ownedTask(): array
{
    $owner = User::factory()->create();
    $client = Client::factory()->for($owner)->create();
    $project = Project::factory()->for($client)->create();
    $task = Task::factory()->for($project)->create();

    return [$owner, $client, $project, $task];
}

function storeRoute(Client $client, Project $project, Task $task): string
{
    return route('clients.projects.tasks.attachments.store', [$client, $project, $task]);
}

beforeEach(function () {
    Storage::fake(config('attachments.disk'));
});

test('the owner can upload a file, and it lands on the private disk with metadata', function () {
    [$owner, $client, $project, $task] = ownedTask();

    $this->actingAs($owner)
        ->post(storeRoute($client, $project, $task), [
            'kind' => 'file',
            'file' => UploadedFile::fake()->image('shot.png', 20, 20),
        ])
        ->assertRedirect();

    $attachment = $task->attachments()->sole();

    expect($attachment->kind)->toBe('file')
        ->and($attachment->original_filename)->toBe('shot.png')
        ->and($attachment->mime_type)->toBe('image/png')
        ->and($attachment->size_bytes)->toBeGreaterThan(0)
        ->and($attachment->sha256)->not->toBeNull()
        ->and($attachment->user_id)->toBe($owner->id);

    Storage::disk(config('attachments.disk'))->assertExists($attachment->path);
});

test('the owner can attach a link with no file', function () {
    [$owner, $client, $project, $task] = ownedTask();

    $this->actingAs($owner)
        ->post(storeRoute($client, $project, $task), [
            'kind' => 'link',
            'url' => 'https://figma.com/file/abc',
            'title' => 'Figma design',
        ])
        ->assertRedirect();

    $attachment = $task->attachments()->sole();

    expect($attachment->kind)->toBe('link')
        ->and($attachment->url)->toBe('https://figma.com/file/abc')
        ->and($attachment->title)->toBe('Figma design')
        ->and($attachment->path)->toBeNull();
});

test('an image streams inline with a nosniff header', function () {
    [$owner, $client, $project, $task] = ownedTask();

    $this->actingAs($owner)->post(storeRoute($client, $project, $task), [
        'kind' => 'file',
        'file' => UploadedFile::fake()->image('shot.png', 20, 20),
    ]);

    $attachment = $task->attachments()->sole();

    $response = $this->actingAs($owner)->get(route('attachments.show', $attachment));

    $response->assertOk();
    expect($response->headers->get('X-Content-Type-Options'))->toBe('nosniff')
        ->and($response->headers->get('Content-Disposition'))->toContain('inline');
});

test('an uploaded HTML file is force-downloaded, never served inline', function () {
    [$owner, $client, $project, $task] = ownedTask();

    $this->actingAs($owner)->post(storeRoute($client, $project, $task), [
        'kind' => 'file',
        'file' => UploadedFile::fake()->create('export.html', 4, 'text/html'),
    ]);

    $attachment = $task->attachments()->sole();

    $response = $this->actingAs($owner)->get(route('attachments.show', $attachment));

    $response->assertOk();
    expect($response->headers->get('Content-Disposition'))->toContain('attachment')
        ->and($response->headers->get('X-Content-Type-Options'))->toBe('nosniff');
});

test('a link row has no stream and 404s the streaming route', function () {
    [$owner, $client, $project, $task] = ownedTask();
    $link = $task->attachments()->create([
        'user_id' => $owner->id,
        'kind' => 'link',
        'url' => 'https://example.com',
    ]);

    $this->actingAs($owner)->get(route('attachments.show', $link))->assertNotFound();
});

test('another user cannot upload to, stream, or delete an attachment', function () {
    [$owner, $client, $project, $task] = ownedTask();
    $attacker = User::factory()->create();

    $file = $task->attachments()->create([
        'user_id' => $owner->id,
        'kind' => 'file',
        'disk' => config('attachments.disk'),
        'path' => 'attachments/secret.png',
        'original_filename' => 'secret.png',
        'mime_type' => 'image/png',
        'size_bytes' => 10,
    ]);

    $this->actingAs($attacker)->post(storeRoute($client, $project, $task), [
        'kind' => 'link',
        'url' => 'https://evil.test',
    ])->assertForbidden();

    $this->actingAs($attacker)->get(route('attachments.show', $file))->assertForbidden();
    $this->actingAs($attacker)->delete(route('attachments.destroy', $file))->assertForbidden();

    $this->assertModelExists($file);
});

test('route scoping rejects an upload under a task that is not the project\'s', function () {
    [$owner, $client, $project] = ownedTask();
    $otherProject = Project::factory()->for($client)->create();
    $strayTask = Task::factory()->for($otherProject)->create();

    $this->actingAs($owner)
        ->post(storeRoute($client, $project, $strayTask), [
            'kind' => 'link',
            'url' => 'https://example.com',
        ])
        ->assertNotFound();
});

test('validation rejects an oversize file and a disallowed type', function () {
    [$owner, $client, $project, $task] = ownedTask();
    $maxKb = (int) config('attachments.max_size_kb');

    $this->actingAs($owner)->post(storeRoute($client, $project, $task), [
        'kind' => 'file',
        'file' => UploadedFile::fake()->create('huge.png', $maxKb + 1),
    ])->assertSessionHasErrors('file');

    $this->actingAs($owner)->post(storeRoute($client, $project, $task), [
        'kind' => 'file',
        'file' => UploadedFile::fake()->create('malware.exe', 4),
    ])->assertSessionHasErrors('file');

    expect($task->attachments()->count())->toBe(0);
});

test('deleting an attachment purges its file from disk', function () {
    [$owner, $client, $project, $task] = ownedTask();

    $this->actingAs($owner)->post(storeRoute($client, $project, $task), [
        'kind' => 'file',
        'file' => UploadedFile::fake()->image('shot.png', 20, 20),
    ]);

    $attachment = $task->attachments()->sole();
    $disk = Storage::disk(config('attachments.disk'));
    $disk->assertExists($attachment->path);

    $this->actingAs($owner)->delete(route('attachments.destroy', $attachment))->assertRedirect();

    $disk->assertMissing($attachment->path);
    expect(Attachment::find($attachment->id))->toBeNull();
});

test('soft-deleting the task keeps the file; force-deleting purges it', function () {
    [, $client, $project, $task] = ownedTask();
    $path = 'attachments/kept.png';
    Storage::disk(config('attachments.disk'))->put($path, 'bytes');

    $attachment = $task->attachments()->create([
        'user_id' => $client->user_id,
        'kind' => 'file',
        'disk' => config('attachments.disk'),
        'path' => $path,
        'original_filename' => 'kept.png',
        'mime_type' => 'image/png',
        'size_bytes' => 5,
    ]);

    $disk = Storage::disk(config('attachments.disk'));

    // Soft delete leaves both the row and the file intact.
    $task->delete();
    $disk->assertExists($path);
    expect(Attachment::find($attachment->id))->not->toBeNull();

    // Force delete purges the file and the row.
    $task->forceDelete();
    $disk->assertMissing($path);
    expect(Attachment::find($attachment->id))->toBeNull();
});

test('force-deleting a client purges its whole subtree of attachment files', function () {
    [, $client, $project, $task] = ownedTask();
    $disk = Storage::disk(config('attachments.disk'));

    $taskPath = 'attachments/task.png';
    $projectPath = 'attachments/project.png';
    $disk->put($taskPath, 'a');
    $disk->put($projectPath, 'b');

    $task->attachments()->create([
        'user_id' => $client->user_id, 'kind' => 'file', 'disk' => config('attachments.disk'),
        'path' => $taskPath, 'original_filename' => 'task.png', 'mime_type' => 'image/png', 'size_bytes' => 1,
    ]);
    $project->attachments()->create([
        'user_id' => $client->user_id, 'kind' => 'file', 'disk' => config('attachments.disk'),
        'path' => $projectPath, 'original_filename' => 'project.png', 'mime_type' => 'image/png', 'size_bytes' => 1,
    ]);

    $client->forceDelete();

    $disk->assertMissing($taskPath);
    $disk->assertMissing($projectPath);
    expect(Attachment::count())->toBe(0);
});

test('the task detail page serializes its attachments', function () {
    [$owner, $client, $project, $task] = ownedTask();
    $task->attachments()->create([
        'user_id' => $owner->id,
        'kind' => 'link',
        'url' => 'https://figma.com/x',
        'title' => 'Design',
    ]);

    $this->actingAs($owner)
        ->get(route('clients.projects.tasks.show', [$client, $project, $task]))
        ->assertInertia(fn ($page) => $page
            ->component('Tasks/Show')
            ->has('task.attachments', 1)
            ->where('task.attachments.0.kind', 'link')
            ->where('task.attachments.0.url', 'https://figma.com/x')
        );
});
