<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAttachmentRequest;
use App\Models\Attachment;
use App\Models\Client;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttachmentController extends Controller
{
    /**
     * MIME types safe to render inline in the browser. Everything else — most
     * notably user-uploaded HTML and SVG — is force-downloaded, so stored markup
     * can't execute same-origin (stored XSS). See `show`.
     */
    private const INLINE_SAFE_MIMES = [
        'image/png',
        'image/jpeg',
        'image/gif',
        'image/webp',
        'application/pdf',
    ];

    /**
     * Attach a file or a link to a task. Authorization runs in the FormRequest
     * (AttachmentPolicy@create against the route's task), so a cross-user write
     * is a 403 before validation. Files land on the private disk; only relative
     * metadata is persisted (never an absolute path or a public URL).
     */
    public function store(StoreAttachmentRequest $request, Client $client, Project $project, Task $task): RedirectResponse
    {
        $data = $request->validated();

        // Append after the current last item so ordering is stable.
        $position = (int) ($task->attachments()->max('position') ?? -1) + 1;

        if ($data['kind'] === 'file') {
            $file = $request->file('file');
            $disk = config('attachments.disk');
            $path = $file->store('attachments', $disk);

            $task->attachments()->create([
                'user_id' => $client->user_id,
                'kind' => 'file',
                'disk' => $disk,
                'path' => $path,
                'original_filename' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'size_bytes' => $file->getSize(),
                'sha256' => hash_file('sha256', $file->getRealPath()),
                'title' => $data['title'] ?? null,
                'position' => $position,
            ]);
        } else {
            $task->attachments()->create([
                'user_id' => $client->user_id,
                'kind' => 'link',
                'url' => $data['url'],
                'title' => $data['title'] ?? null,
                'position' => $position,
            ]);
        }

        return redirect()->back();
    }

    /**
     * Stream a file attachment from its private disk. Images and PDFs render
     * inline; anything else (HTML, SVG, office docs, archives) is force-
     * downloaded. `nosniff` is always sent so the browser honours our declared
     * type rather than guessing.
     */
    public function show(Attachment $attachment): StreamedResponse
    {
        $this->authorize('view', $attachment);

        abort_if($attachment->kind !== 'file', 404);

        $disk = Storage::disk($attachment->disk);

        abort_unless($disk->exists($attachment->path), 404);

        $disposition = in_array($attachment->mime_type, self::INLINE_SAFE_MIMES, true) ? 'inline' : 'attachment';

        return $disk->response($attachment->path, $attachment->original_filename, [
            'Content-Type' => $attachment->mime_type,
            'X-Content-Type-Options' => 'nosniff',
        ], $disposition);
    }

    /**
     * Remove an attachment. There are no soft deletes here, so this is permanent
     * and the model's `deleting` hook purges the underlying file immediately.
     */
    public function destroy(Attachment $attachment): RedirectResponse
    {
        $this->authorize('delete', $attachment);

        $attachment->delete();

        return redirect()->back();
    }
}
