<?php

namespace App\Policies;

use App\Models\Client;
use App\Models\User;

class ClientPolicy
{
    /**
     * Ownership is the whole model: a user may only touch clients they own.
     * The index scopes its own query, so there is no viewAny gate here.
     */
    public function view(User $user, Client $client): bool
    {
        return $client->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Client $client): bool
    {
        return $client->user_id === $user->id;
    }

    public function delete(User $user, Client $client): bool
    {
        return $client->user_id === $user->id;
    }

    /**
     * Trash recovery. `restore` un-trashes; `forceDelete` purges for real (the DB
     * cascade then removes the client's projects and tasks). Same ownership gate.
     */
    public function restore(User $user, Client $client): bool
    {
        return $client->user_id === $user->id;
    }

    public function forceDelete(User $user, Client $client): bool
    {
        return $client->user_id === $user->id;
    }
}
