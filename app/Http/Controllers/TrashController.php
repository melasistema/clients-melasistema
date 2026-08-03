<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use Inertia\Inertia;
use Inertia\Response;

class TrashController extends Controller
{
    /**
     * The user's trash, one section per level. Each level lists only items trashed
     * *at that level*: a project appears here only if the project itself was
     * deleted (its client is still active), because `whereHas` respects the
     * soft-delete scope on the ancestor relations. Items are mapped to plain
     * view-models so the models' hidden relations and appended earnings accessors
     * don't run — the trash view only needs identity and context.
     */
    public function index(): Response
    {
        $userId = auth()->id();

        $clients = auth()->user()->clients()->onlyTrashed()->latest('deleted_at')->get()
            ->map(fn ($client) => [
                'id' => $client->id,
                'company_name' => $client->company_name,
                'contact_email' => $client->contact_email,
                'deleted_at' => $client->deleted_at,
            ]);

        $projects = Project::onlyTrashed()
            ->whereHas('client', fn ($query) => $query->where('user_id', $userId))
            ->with('client')
            ->latest('deleted_at')
            ->get()
            ->map(fn ($project) => [
                'id' => $project->id,
                'name' => $project->name,
                'client_name' => $project->client->company_name,
                'deleted_at' => $project->deleted_at,
            ]);

        $tasks = Task::onlyTrashed()
            ->whereHas('project.client', fn ($query) => $query->where('user_id', $userId))
            ->with('project.client')
            ->latest('deleted_at')
            ->get()
            ->map(fn ($task) => [
                'id' => $task->id,
                'title' => $task->title,
                'project_name' => $task->project->name,
                'client_name' => $task->project->client->company_name,
                'deleted_at' => $task->deleted_at,
            ]);

        return Inertia::render('Trash/Index', [
            'clients' => $clients,
            'projects' => $projects,
            'tasks' => $tasks,
        ]);
    }
}
