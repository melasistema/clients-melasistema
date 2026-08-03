<?php

namespace Database\Factories;

use App\Models\Attachment;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Attachment>
 */
class AttachmentFactory extends Factory
{
    public function definition(): array
    {
        // A link by default (no real bytes to fake); use the `file()` state for a
        // file row. Attach to a fresh task unless the caller supplies a parent.
        $task = Task::factory();

        return [
            'attachable_type' => (new Task)->getMorphClass(),
            'attachable_id' => $task,
            'user_id' => User::factory(),
            'kind' => 'link',
            'url' => fake()->url(),
            'title' => fake()->words(3, true),
            'position' => 0,
        ];
    }

    /**
     * A file row with plausible image metadata. Pair with `Storage::fake()` and a
     * real stored file in tests when the bytes matter.
     */
    public function file(?string $path = 'attachments/example.png', string $mime = 'image/png'): static
    {
        return $this->state(fn () => [
            'kind' => 'file',
            'disk' => config('attachments.disk'),
            'path' => $path,
            'original_filename' => 'example.png',
            'mime_type' => $mime,
            'size_bytes' => 12345,
            'sha256' => hash('sha256', 'example'),
            'url' => null,
        ]);
    }
}
