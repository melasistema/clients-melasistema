<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
                    $attachments = $taskData['attachments'] ?? [];
                    unset($taskData['attachments']);

                    $task = $project->tasks()->updateOrCreate(
                        ['title' => $taskData['title']],
                        $taskData,
                    );

                    $this->seedAttachments($task, $attachments, $user->id);
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
     * Attach a task's demo files + links. Files are placeholders generated on the
     * fly (see the generators below) and written to the attachments disk on a
     * DETERMINISTIC path, so re-seeding overwrites the same bytes and matches the
     * same row (idempotent, never orphaning files). Links carry no file.
     */
    private function seedAttachments(Task $task, array $attachments, int $userId): void
    {
        $disk = config('attachments.disk');
        $position = 0;

        foreach ($attachments as $attachment) {
            if (($attachment['kind'] ?? null) === 'link') {
                $task->attachments()->updateOrCreate(
                    ['url' => $attachment['url']],
                    [
                        'user_id' => $userId,
                        'kind' => 'link',
                        'title' => $attachment['title'] ?? null,
                        'position' => $position++,
                    ],
                );

                continue;
            }

            [$bytes, $mime, $extension] = $this->generatePlaceholder($attachment['source'], $attachment['title']);
            $filename = Str::slug($attachment['title']).'.'.$extension;
            $path = "attachments/demo/{$task->id}-{$filename}";

            Storage::disk($disk)->put($path, $bytes);

            $task->attachments()->updateOrCreate(
                ['path' => $path],
                [
                    'user_id' => $userId,
                    'kind' => 'file',
                    'disk' => $disk,
                    'original_filename' => $filename,
                    'mime_type' => $mime,
                    'size_bytes' => strlen($bytes),
                    'sha256' => hash('sha256', $bytes),
                    'title' => $attachment['title'],
                    'position' => $position++,
                ],
            );
        }
    }

    /**
     * Generate a small placeholder file for a demo source, fully offline (no
     * network, no bundled binaries). Returns [bytes, mime, extension].
     */
    private function generatePlaceholder(string $source, string $label): array
    {
        return match ($source) {
            'pdf' => [$this->placeholderPdf($label), 'application/pdf', 'pdf'],
            'html' => [$this->placeholderHtml($label), 'text/html', 'html'],
            default => [$this->placeholderImage($label), 'image/png', 'png'],
        };
    }

    /**
     * A labelled PNG. Uses GD when available (a captioned coloured card);
     * otherwise falls back to a tiny hard-coded valid PNG so seeding never fails
     * on a GD-less PHP build.
     */
    private function placeholderImage(string $label): string
    {
        if (! function_exists('imagecreatetruecolor')) {
            // 1x1 opaque PNG — a valid image so the gallery still renders a thumbnail.
            return base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');
        }

        $width = 800;
        $height = 500;
        $image = imagecreatetruecolor($width, $height);

        // A deterministic colour per label, so each placeholder looks distinct.
        $seed = crc32($label);
        $background = imagecolorallocate($image, 60 + ($seed % 120), 60 + (($seed >> 8) % 120), 60 + (($seed >> 16) % 120));
        $foreground = imagecolorallocate($image, 245, 245, 245);
        imagefilledrectangle($image, 0, 0, $width, $height, $background);

        $font = 5;
        $textWidth = imagefontwidth($font) * strlen($label);
        imagestring($image, $font, (int) (($width - $textWidth) / 2), (int) ($height / 2) - 8, $label, $foreground);

        ob_start();
        imagepng($image);
        $bytes = (string) ob_get_clean();
        imagedestroy($image);

        return $bytes;
    }

    /**
     * A minimal but valid single-page PDF (correct object offsets + xref), built
     * by hand so it needs no PDF library and opens inline in the browser.
     */
    private function placeholderPdf(string $label): string
    {
        $text = str_replace(['(', ')', '\\'], '', $label);
        $stream = "BT /F1 24 Tf 60 380 Td ({$text}) Tj ET";

        $objects = [
            1 => '<< /Type /Catalog /Pages 2 0 R >>',
            2 => '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
            3 => '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 450] /Resources << /Font << /F1 5 0 R >> >> /Contents 4 0 R >>',
            4 => '<< /Length '.strlen($stream)." >>\nstream\n".$stream."\nendstream",
            5 => '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
        ];

        $pdf = "%PDF-1.4\n";
        $offsets = [];
        foreach ($objects as $number => $body) {
            $offsets[$number] = strlen($pdf);
            $pdf .= "{$number} 0 obj\n{$body}\nendobj\n";
        }

        $xrefOffset = strlen($pdf);
        $size = count($objects) + 1;
        $pdf .= "xref\n0 {$size}\n0000000000 65535 f \n";
        foreach ($offsets as $offset) {
            $pdf .= sprintf("%010d 00000 n \n", $offset);
        }
        $pdf .= "trailer\n<< /Size {$size} /Root 1 0 R >>\nstartxref\n{$xrefOffset}\n%%EOF";

        return $pdf;
    }

    /**
     * A tiny self-contained HTML document. Served as a download (never inline)
     * by the streaming route, so it's a safe demo of the "documents" case.
     */
    private function placeholderHtml(string $label): string
    {
        $safe = htmlspecialchars($label, ENT_QUOTES);

        return "<!doctype html>\n<html lang=\"en\">\n<head><meta charset=\"utf-8\"><title>{$safe}</title></head>\n"
            ."<body><h1>{$safe}</h1><p>Demo attachment generated by DemoDataSeeder.</p></body>\n</html>\n";
    }

    /**
     * Turn a relative string like "-15 days" (or null) into a concrete date.
     */
    private function resolveDate(?string $value): ?Carbon
    {
        return $value === null ? null : Carbon::parse($value);
    }
}
