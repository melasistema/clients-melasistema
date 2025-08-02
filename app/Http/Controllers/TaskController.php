<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Project;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Client $client, Project $project): Response
    {
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
        return Inertia::render('Tasks/Create', [
            'client' => $client,
            'project' => $project,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Client $client, Project $project)
    {
        $request->validate([
            'description' => 'required|string',
            'minutes' => 'required|integer|min:0',
        ]);

        $project->tasks()->create([
            'description' => $request->description,
            'minutes' => $request->minutes,
        ]);

        return redirect()->route('clients.projects.tasks.index', [$client->id, $project->id]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Client $client, Project $project, Task $task): Response
    {
        return Inertia::render('Tasks/Edit', [
            'client' => $client,
            'project' => $project,
            'task' => $task,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Client $client, Project $project, Task $task)
    {
        $request->validate([
            'description' => 'required|string',
            'minutes' => 'required|integer|min:0',
        ]);

        $task->update([
            'description' => $request->description,
            'minutes' => $request->minutes,
        ]);

        return redirect()->route('clients.projects.tasks.index', [$client->id, $project->id]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Client $client, Project $project, Task $task)
    {
        $task->delete();

        return redirect()->route('clients.projects.tasks.index', [$client->id, $project->id]);
    }
}
