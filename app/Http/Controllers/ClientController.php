<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
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
            'clients' => Client::all(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('Clients/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'company_name' => 'required|string|max:255',
            'contact_name' => 'required|string|max:255',
            'contact_email' => 'required|string|email|max:255|unique:clients',
            'contact_phone' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'vat_number' => 'nullable|string|max:255',
            'unique_code' => 'nullable|string|max:255',
        ]);

        Client::create([
            'company_name' => $request->company_name,
            'contact_name' => $request->contact_name,
            'contact_email' => $request->contact_email,
            'contact_phone' => $request->contact_phone,
            'address' => $request->address,
            'vat_number' => $request->vat_number,
            'unique_code' => $request->unique_code,
        ]);

        return redirect()->route('clients.index');
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
    public function edit(Client $client): Response
    {
        return Inertia::render('Clients/Edit', [
            'client' => $client,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Client $client)
    {
        $request->validate([
            'company_name' => 'required|string|max:255',
            'contact_name' => 'required|string|max:255',
            'contact_email' => 'required|string|email|max:255|unique:clients,contact_email,'.$client->id,
            'contact_phone' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'vat_number' => 'nullable|string|max:255',
            'unique_code' => 'nullable|string|max:255',
        ]);

        $client->update([
            'company_name' => $request->company_name,
            'contact_name' => $request->contact_name,
            'contact_email' => $request->contact_email,
            'contact_phone' => $request->contact_phone,
            'address' => $request->address,
            'vat_number' => $request->vat_number,
            'unique_code' => $request->unique_code,
        ]);

        return redirect()->route('clients.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Client $client)
    {
        $client->delete();

        return redirect()->route('clients.index');
    }
}
