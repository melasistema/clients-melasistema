<?php

namespace App\Policies;

use App\Models\Client;
use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    /**
     * A project belongs to whoever owns its client. Ownership walks the chain
     * project -> client -> user. Route scoping already guarantees the project
     * is the client's; this guarantees the client is the user's.
     */
    public function view(User $user, Project $project): bool
    {
        return $project->client->user_id === $user->id;
    }

    /**
     * Creating a project is authorized against the parent client, passed
     * explicitly since there is no project instance yet.
     */
    public function create(User $user, Client $client): bool
    {
        return $client->user_id === $user->id;
    }

    public function update(User $user, Project $project): bool
    {
        return $project->client->user_id === $user->id;
    }

    public function delete(User $user, Project $project): bool
    {
        return $project->client->user_id === $user->id;
    }
}
