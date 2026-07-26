<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Client;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\RedirectResponse;
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

    public function startTimer(Client $client, Project $project, Task $task): RedirectResponse
    {
        $this->authorize('update', $task);

        $task->update([
            'is_running' => true,
            'timer_started_at' => now(),
        ]);

        return redirect()->back();
    }

    public function stopTimer(Client $client, Project $project, Task $task): RedirectResponse
    {
        $this->authorize('update', $task);

        $secondsToAdd = $task->timer_started_at->diffInSeconds(now());
        $newTotalSeconds = $task->total_seconds + $secondsToAdd;

        $task->update([
            'is_running' => false,
            'timer_started_at' => null,
            'total_seconds' => $newTotalSeconds,
        ]);

        return redirect()->back();
    }

    /**
     * Mark a task done. A completed task can't still be accruing time, so a
     * running timer is stopped first (banking the elapsed seconds).
     */
    public function complete(Client $client, Project $project, Task $task): RedirectResponse
    {
        $this->authorize('update', $task);

        $attributes = ['completed_at' => now()];

        if ($task->is_running) {
            $attributes['is_running'] = false;
            $attributes['total_seconds'] = $task->total_seconds + $task->timer_started_at->diffInSeconds(now());
            $attributes['timer_started_at'] = null;
        }

        $task->update($attributes);

        return redirect()->back();
    }

    public function reopen(Client $client, Project $project, Task $task): RedirectResponse
    {
        $this->authorize('update', $task);

        $task->update(['completed_at' => null]);

        return redirect()->back();
    }
}
