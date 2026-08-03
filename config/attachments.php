<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Attachment storage & upload limits
    |--------------------------------------------------------------------------
    |
    | Files attached to tasks (screenshots, PDFs, HTML exports, …) are stored on
    | a PRIVATE disk and served only through the authorized `attachments.show`
    | route — never a public URL. `disk` defaults to Laravel's `local` disk,
    | whose root (storage/app/private) is not web-accessible.
    |
    | `max_size_kb` caps a single upload. `allowed_extensions` is the allowlist
    | the store request validates against (by content-guessed MIME, not just the
    | filename). HTML/SVG are allowed to be *stored* but are always force-
    | downloaded, never served inline (that is decided in the controller), so
    | user-uploaded markup can't run same-origin.
    |
    | Self-hosters tune all three from .env without touching code.
    |
    */

    'disk' => env('ATTACHMENTS_DISK', 'local'),

    'max_size_kb' => (int) env('ATTACHMENTS_MAX_SIZE_KB', 10240), // 10 MB

    'allowed_extensions' => [
        // Images
        'jpg', 'jpeg', 'png', 'gif', 'webp', 'svg',
        // Documents
        'pdf', 'txt', 'md', 'csv',
        'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx',
        // Web exports & bundles
        'html', 'htm', 'zip',
    ],

];
