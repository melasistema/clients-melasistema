<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    /**
     * A task belongs to whoever owns its project's client. Ownership walks the
     * chain task -> project -> client -> user. Route scoping guarantees the task
     * is the project's and the project is the client's; this checks the owner.
     */
    public function view(User $user, Task $task): bool
    {
        return $task->project->client->user_id === $user->id;
    }

    /**
     * Creating a task is authorized against the parent project, passed
     * explicitly since there is no task instance yet.
     */
    public function create(User $user, Project $project): bool
    {
        return $project->client->user_id === $user->id;
    }

    public function update(User $user, Task $task): bool
    {
        return $task->project->client->user_id === $user->id;
    }

    public function delete(User $user, Task $task): bool
    {
        return $task->project->client->user_id === $user->id;
    }

    /**
     * Trash recovery. Walks the chain with `withTrashed()` so ownership resolves
     * even when an ancestor is soft-deleted; `forceDelete` purges the task row.
     */
    public function restore(User $user, Task $task): bool
    {
        return $this->ownedBy($user, $task);
    }

    public function forceDelete(User $user, Task $task): bool
    {
        return $this->ownedBy($user, $task);
    }

    private function ownedBy(User $user, Task $task): bool
    {
        $userId = $task->project()->withTrashed()->first()?->client()->withTrashed()->value('user_id');

        return $userId === $user->id;
    }
}
