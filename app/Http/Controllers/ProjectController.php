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
            // `payments` is eager-loaded alongside `tasks` because the projects'
            // appended amount_paid/outstanding accessors read it — otherwise the
            // listing fires one payments query per project (N+1).
            'projects' => $client->projects()->with('tasks', 'payments')->get(),
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

        // Edit doubles as the project's detail surface (there are no show pages):
        // load tasks + the payment ledger so the appended totals and the payments
        // panel render without extra queries.
        $project->load('tasks', 'payments');

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
     * Mark the project completed (work delivered). Completion is independent of
     * payment — a completed project may still be fully or partially unpaid.
     */
    public function complete(Client $client, Project $project): RedirectResponse
    {
        $this->authorize('update', $project);

        $project->update(['completed_at' => now()]);

        return redirect()->back();
    }

    /**
     * Reopen a completed project. Blocked once it is fully paid: a settled
     * project can't be un-completed (the frontend also hides the action).
     */
    public function reopen(Client $client, Project $project): RedirectResponse
    {
        $this->authorize('update', $project);

        abort_if($project->is_fully_paid, 403, 'A fully paid project cannot be reopened.');

        $project->update(['completed_at' => null]);

        return redirect()->back();
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
