<?php

use App\Http\Controllers\ClientController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TrashController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// The domain hierarchy (Client -> Project -> Task). `scoped()` enforces the URL
// nesting at the routing layer: a project must belong to the client in the URL,
// a task to that project — mismatched IDs 404 before a controller runs.
// `show` is intentionally omitted; there are no detail pages (lists link to
// projects/tasks/edit directly). Ownership is enforced by the model policies.
Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('clients', ClientController::class)->except('show');
    Route::resource('clients.projects', ProjectController::class)->scoped()->except('show');
    Route::resource('clients.projects.tasks', TaskController::class)->scoped()->except('show');

    Route::post('clients/{client}/projects/{project}/tasks/{task}/start-timer', [TaskController::class, 'startTimer'])
        ->scopeBindings()
        ->name('clients.projects.tasks.startTimer');

    Route::post('clients/{client}/projects/{project}/tasks/{task}/stop-timer', [TaskController::class, 'stopTimer'])
        ->scopeBindings()
        ->name('clients.projects.tasks.stopTimer');

    // Trash / recovery. Soft-deleted records live here until restored or purged.
    // The recovery routes are flat (not nested) and bind with `withTrashed()` so a
    // trashed model resolves; each is gated by its policy's restore/forceDelete.
    Route::get('trash', [TrashController::class, 'index'])->name('trash.index');

    Route::put('trash/clients/{client}/restore', [ClientController::class, 'restore'])
        ->withTrashed()->name('clients.restore');
    Route::delete('trash/clients/{client}', [ClientController::class, 'forceDelete'])
        ->withTrashed()->name('clients.forceDelete');

    Route::put('trash/projects/{project}/restore', [ProjectController::class, 'restore'])
        ->withTrashed()->name('projects.restore');
    Route::delete('trash/projects/{project}', [ProjectController::class, 'forceDelete'])
        ->withTrashed()->name('projects.forceDelete');

    Route::put('trash/tasks/{task}/restore', [TaskController::class, 'restore'])
        ->withTrashed()->name('tasks.restore');
    Route::delete('trash/tasks/{task}', [TaskController::class, 'forceDelete'])
        ->withTrashed()->name('tasks.forceDelete');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
