<?php

namespace App\Http\Middleware;

use App\Models\Task;
use Illuminate\Foundation\Inspiring;
use Illuminate\Http\Request;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        [$message, $author] = str(Inspiring::quotes()->random())->explode('-');

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'registrationEnabled' => (bool) config('features.registration_enabled'),
            // Currency + locale for the frontend's Intl money formatter. Set once
            // in config/money.php (via .env) so self-hosters can switch currency
            // without touching any Vue. See resources/js/composables/useFormatters.
            'money' => [
                'currency' => config('money.currency'),
                'locale' => config('money.locale'),
            ],
            // UI language. `locale` is the active app locale (APP_LOCALE);
            // `translations` are that locale's lang/{locale}/*.php messages,
            // consumed on the frontend by the useTranslations() composable's __().
            'locale' => app()->getLocale(),
            'translations' => $this->translations(),
            'quote' => ['message' => trim($message), 'author' => trim($author)],
            'auth' => [
                'user' => $request->user(),
            ],
            // The single running timer (or null), shared on every request so the
            // persistent LiveTimer lives in the app chrome on every page rather
            // than only the dashboard. This is also the shape a NativePHP
            // menu-bar timer will read.
            'activeTimer' => $this->activeTimer($request),
            // The last stopped task (or null), from the `last_timer` cookie. Lets the
            // timer bar keep showing what you were working on — clickable + resumable
            // — after you stop, until you dismiss it. Shown only when nothing runs.
            'lastTimer' => $this->lastTimer($request),
            'ziggy' => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];
    }

    /**
     * The single running timer for the current user (if any), scoped through the
     * ownership chain. `whereHas` honours the soft-delete scope, so a timer under a
     * trashed project/client doesn't surface. Null for guests and when nothing runs.
     *
     * @return array<string, mixed>|null
     */
    protected function activeTimer(Request $request): ?array
    {
        $user = $request->user();

        if ($user === null) {
            return null;
        }

        $task = Task::whereHas('project.client', fn ($query) => $query->where('user_id', $user->id))
            ->where('is_running', true)
            ->with('project.client')
            ->latest('timer_started_at')
            ->first();

        if ($task === null) {
            return null;
        }

        return [
            'client_id' => $task->project->client->id,
            'project_id' => $task->project->id,
            'task_id' => $task->id,
            'task_description' => $task->description,
            'project_name' => $task->project->name,
            'client_name' => $task->project->client->company_name,
            'timer_started_at' => $task->timer_started_at,
        ];
    }

    /**
     * The last stopped task, decoded from the plaintext `last_timer` cookie
     * (set by TaskController@stopTimer). Null when absent or malformed.
     *
     * @return array<string, mixed>|null
     */
    protected function lastTimer(Request $request): ?array
    {
        $raw = $request->cookie('last_timer');

        if (! is_string($raw) || $raw === '') {
            return null;
        }

        $data = json_decode($raw, true);

        return is_array($data) ? $data : null;
    }

    /**
     * Load the active locale's translation messages for the frontend, keyed by
     * file (common, clients, …) so Vue can read them as `common.edit`. Merged
     * over the fallback locale so a missing key falls back to the fallback
     * language's text rather than surfacing the raw key — mirroring __() in PHP.
     *
     * @return array<string, mixed>
     */
    protected function translations(): array
    {
        $locale = app()->getLocale();
        $fallback = config('app.fallback_locale');

        $messages = $this->loadLocaleMessages($fallback);

        if ($locale !== $fallback) {
            $messages = array_replace_recursive($messages, $this->loadLocaleMessages($locale));
        }

        return $messages;
    }

    /**
     * Read every lang/{locale}/*.php file into a [filename => messages] array.
     *
     * @return array<string, mixed>
     */
    protected function loadLocaleMessages(string $locale): array
    {
        $path = lang_path($locale);

        if (! is_dir($path)) {
            return [];
        }

        $messages = [];

        foreach (glob($path.'/*.php') as $file) {
            $messages[pathinfo($file, PATHINFO_FILENAME)] = require $file;
        }

        return $messages;
    }
}
