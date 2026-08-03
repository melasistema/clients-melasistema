<?php

namespace App\Http\Requests;

use App\Models\Attachment;
use Illuminate\Foundation\Http\FormRequest;

class StoreAttachmentRequest extends FormRequest
{
    /**
     * Authorize against the parent task (from the route), so a cross-user upload
     * is a 403 before validation — same pattern as StoreTaskRequest.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', [Attachment::class, $this->route('task')]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $maxKb = (int) config('attachments.max_size_kb');
        $extensions = implode(',', config('attachments.allowed_extensions'));

        return [
            'kind' => 'required|in:file,link',
            // A file upload: validated by content-guessed MIME (via `mimes`), size
            // capped, extension allowlisted — all driven by config/attachments.php.
            'file' => "required_if:kind,file|file|max:$maxKb|mimes:$extensions",
            // A link: just a URL (Figma, staging, …).
            'url' => 'required_if:kind,link|nullable|url|max:2048',
            // Optional label / caption for either kind.
            'title' => 'nullable|string|max:255',
        ];
    }
}
