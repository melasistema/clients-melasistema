<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\Client;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Client $client): Response
    {
        $this->authorize('view', $client);

        return Inertia::render('Projects/Index', [
            'client' => $client,
            'projects' => $client->projects()->with('tasks')->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Client $client): Response
    {
        $this->authorize('create', [Project::class, $client]);

        return Inertia::render('Projects/Create', [
            'client' => $client,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProjectRequest $request, Client $client): RedirectResponse
    {
        $client->projects()->create($request->validated());

        return redirect()->route('clients.projects.index', $client->id);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Client $client, Project $project): Response
    {
        $this->authorize('update', $project);

        return Inertia::render('Projects/Edit', [
            'client' => $client,
            'project' => $project,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProjectRequest $request, Client $client, Project $project): RedirectResponse
    {
        $project->update($request->validated());

        return redirect()->route('clients.projects.index', $client->id);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Client $client, Project $project): RedirectResponse
    {
        $this->authorize('delete', $project);

        $project->delete();

        return redirect()->route('clients.projects.index', $client->id);
    }

    /**
     * Restore a trashed project (flat route, bound with ->withTrashed()).
     */
    public function restore(Project $project): RedirectResponse
    {
        $this->authorize('restore', $project);

        $project->restore();

        return redirect()->route('trash.index');
    }

    /**
     * Permanently delete a trashed project; its tasks cascade at the DB level.
     */
    public function forceDelete(Project $project): RedirectResponse
    {
        $this->authorize('forceDelete', $project);

        $project->forceDelete();

        return redirect()->route('trash.index');
    }
}
