<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Models\Client;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        return Inertia::render('Clients/Index', [
            // `projects.payments` is eager-loaded so each project's appended
            // amount_paid/outstanding accessors don't fire a query per project.
            'clients' => auth()->user()->clients()->with('projects.tasks', 'projects.payments')->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        $this->authorize('create', Client::class);

        return Inertia::render('Clients/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreClientRequest $request): RedirectResponse
    {
        auth()->user()->clients()->create($request->validated());

        return redirect()->route('clients.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Client $client): Response
    {
        $this->authorize('update', $client);

        return Inertia::render('Clients/Edit', [
            'client' => $client,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateClientRequest $request, Client $client): RedirectResponse
    {
        $client->update($request->validated());

        return redirect()->route('clients.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Client $client): RedirectResponse
    {
        $this->authorize('delete', $client);

        $client->delete();

        return redirect()->route('clients.index');
    }

    /**
     * Restore a trashed client (route model bound with ->withTrashed()).
     */
    public function restore(Client $client): RedirectResponse
    {
        $this->authorize('restore', $client);

        $client->restore();

        return redirect()->route('trash.index');
    }

    /**
     * Permanently delete a trashed client; the DB cascade purges its projects
     * and tasks in the same operation.
     */
    public function forceDelete(Client $client): RedirectResponse
    {
        $this->authorize('forceDelete', $client);

        $client->forceDelete();

        return redirect()->route('trash.index');
    }
}
