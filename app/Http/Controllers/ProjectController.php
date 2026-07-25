<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Project;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Client $client): Response
    {
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
        return Inertia::render('Projects/Create', [
            'client' => $client,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Client $client)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'hourly_rate' => 'required|numeric|min:0',
        ]);

        $client->projects()->create([
            'name' => $request->name,
            'description' => $request->description,
            'hourly_rate' => $request->hourly_rate,
        ]);

        return redirect()->route('clients.projects.index', $client->id);
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
    public function edit(Client $client, Project $project): Response
    {
        return Inertia::render('Projects/Edit', [
            'client' => $client,
            'project' => $project,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Client $client, Project $project)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'hourly_rate' => 'required|numeric|min:0',
            'paid_at' => 'nullable|date',
        ]);

        $project->update([
            'name' => $request->name,
            'description' => $request->description,
            'hourly_rate' => $request->hourly_rate,
            'paid_at' => $request->paid_at,
        ]);

        return redirect()->route('clients.projects.index', $client->id);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Client $client, Project $project)
    {
        $project->delete();

        return redirect()->route('clients.projects.index', $client->id);
    }
}
