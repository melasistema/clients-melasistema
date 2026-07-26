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
}
