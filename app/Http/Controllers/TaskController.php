<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Client;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Client $client, Project $project): Response
    {
        $this->authorize('view', $project);

        // The serialized project runs its appended amount_paid/outstanding
        // accessors, which read the payment ledger — load it so they don't query.
        $project->loadMissing('payments');

        return Inertia::render('Tasks/Index', [
            'client' => $client,
            'project' => $project,
            'tasks' => $project->tasks,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Client $client, Project $project): Response
    {
        $this->authorize('create', [Task::class, $project]);

        $project->loadMissing('tasks', 'payments');

        return Inertia::render('Tasks/Create', [
            'client' => $client,
            'project' => $project,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTaskRequest $request, Client $client, Project $project): RedirectResponse
    {
        $project->tasks()->create($request->validated());

        return redirect()->route('clients.projects.tasks.index', [$client->id, $project->id]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Client $client, Project $project, Task $task): Response
    {
        $this->authorize('update', $task);

        $project->loadMissing('tasks', 'payments');

        return Inertia::render('Tasks/Edit', [
            'client' => $client,
            'project' => $project,
            'task' => $task,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTaskRequest $request, Client $client, Project $project, Task $task): RedirectResponse
    {
        $task->update($request->validated());

        return redirect()->route('clients.projects.tasks.index', [$client->id, $project->id]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Client $client, Project $project, Task $task): RedirectResponse
    {
        $this->authorize('delete', $task);

        $task->delete();

        return redirect()->route('clients.projects.tasks.index', [$client->id, $project->id]);
    }

    /**
     * Restore a trashed task (flat route, bound with ->withTrashed()).
     */
    public function restore(Task $task): RedirectResponse
    {
        $this->authorize('restore', $task);

        $task->restore();

        return redirect()->route('trash.index');
    }

    /**
     * Permanently delete a trashed task.
     */
    public function forceDelete(Task $task): RedirectResponse
    {
        $this->authorize('forceDelete', $task);

        $task->forceDelete();

        return redirect()->route('trash.index');
    }

    /**
     * Start (or switch to) this task's timer. A freelancer can only work one task
     * at a time, so this enforces a single running timer: any other running task of
     * the owner is stopped first — banking its elapsed seconds — in one transaction.
     * Also forgets the "last stopped" bar, since a timer is now running.
     */
    public function startTimer(Client $client, Project $project, Task $task): RedirectResponse
    {
        $this->authorize('update', $task);

        DB::transaction(function () use ($client, $task) {
            $this->stopRunningTimers($client->user_id, exceptTaskId: $task->id);

            $task->update([
                'is_running' => true,
                'timer_started_at' => now(),
            ]);
        });

        Cookie::queue(Cookie::forget('last_timer'));

        return redirect()->back();
    }

    /**
     * Stop this task's timer, banking the elapsed seconds, and remember it as the
     * "last stopped" task in a cookie so the header timer bar keeps showing what you
     * were working on (clickable, resumable) until you dismiss it — even across page
     * navigations.
     */
    public function stopTimer(Client $client, Project $project, Task $task): RedirectResponse
    {
        $this->authorize('update', $task);

        $newTotalSeconds = $this->bankTimer($task);

        Cookie::queue('last_timer', json_encode([
            'client_id' => $client->id,
            'project_id' => $project->id,
            'task_id' => $task->id,
            'task_description' => $task->description,
            'project_name' => $project->name,
            'total_seconds' => $newTotalSeconds,
        ]), 60 * 24 * 30);

        return redirect()->back();
    }

    /**
     * Dismiss the persistent "last stopped timer" bar by forgetting its cookie.
     */
    public function dismissLastTimer(): RedirectResponse
    {
        Cookie::queue(Cookie::forget('last_timer'));

        return redirect()->back();
    }

    /**
     * Stop every running timer owned by the given user (optionally excluding one
     * task), banking each one's elapsed seconds. Backs the single-timer invariant.
     * Eager-loads `project` so bankTimer's rate snapshot doesn't N+1 across the loop.
     */
    private function stopRunningTimers(int $userId, ?int $exceptTaskId = null): void
    {
        $running = Task::with('project')
            ->whereHas('project.client', fn ($query) => $query->where('user_id', $userId))
            ->where('is_running', true)
            ->when($exceptTaskId, fn ($query) => $query->where('id', '!=', $exceptTaskId))
            ->get();

        foreach ($running as $task) {
            $this->bankTimer($task);
        }
    }

    /**
     * Bank a running task's elapsed time: clear the running flag, add the seconds to
     * the cached `total_seconds`, and record the session as one dated `time_entries`
     * row with the project's rate snapshotted. The single place a session is ever
     * banked (stop / switch / complete all route through here), so exactly one entry
     * is written per session and `total_seconds` always equals the sum of entries
     * accrued since the feature shipped. Returns the new `total_seconds`.
     *
     * A zero-second session (start then immediate stop) is banked but writes no
     * entry — it is noise, and adding zero keeps the running total correct.
     */
    private function bankTimer(Task $task): int
    {
        // Carbon 3's diffInSeconds returns a float (fractional seconds); cast so
        // total_seconds stays a whole-second integer (the DB column is, and the
        // last_timer cookie is read back raw).
        $startedAt = $task->timer_started_at;
        $endedAt = now();
        $secondsToAdd = (int) $startedAt->diffInSeconds($endedAt);
        $newTotalSeconds = $task->total_seconds + $secondsToAdd;

        $task->update([
            'is_running' => false,
            'timer_started_at' => null,
            'total_seconds' => $newTotalSeconds,
        ]);

        if ($secondsToAdd > 0) {
            $task->timeEntries()->create([
                'started_at' => $startedAt,
                'ended_at' => $endedAt,
                'seconds' => $secondsToAdd,
                'hourly_rate' => $task->project->hourly_rate,
            ]);
        }

        return $newTotalSeconds;
    }

    /**
     * Mark a task done. A completed task can't still be accruing time, so a
     * running timer is stopped first (banking the elapsed seconds).
     */
    public function complete(Client $client, Project $project, Task $task): RedirectResponse
    {
        $this->authorize('update', $task);

        // A completed task can't still be accruing time: bank the running session
        // first (which also records its time entry), then stamp completion.
        if ($task->is_running) {
            $this->bankTimer($task);
        }

        $task->update(['completed_at' => now()]);

        return redirect()->back();
    }

    public function reopen(Client $client, Project $project, Task $task): RedirectResponse
    {
        $this->authorize('update', $task);

        $task->update(['completed_at' => null]);

        return redirect()->back();
    }
}
