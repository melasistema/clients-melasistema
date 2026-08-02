<?php

use App\Http\Controllers\ClientController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TrashController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// The time report: tracked hours + billable value windowed over a period,
// broken down by day and by project. Reads the dated time_entries ledger.
Route::get('report', [ReportController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('report');

// The domain hierarchy (Client -> Project -> Task). `scoped()` enforces the URL
// nesting at the routing layer: a project must belong to the client in the URL,
// a task to that project — mismatched IDs 404 before a controller runs.
// `show` is intentionally omitted; there are no detail pages (lists link to
// projects/tasks/edit directly). Ownership is enforced by the model policies.
Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('clients', ClientController::class)->except('show');
    Route::resource('clients.projects', ProjectController::class)->scoped()->except('show');
    Route::resource('clients.projects.tasks', TaskController::class)->scoped()->except('show');

    // The payment ledger. Only store/destroy — there is no listing (payments are
    // rendered inline on the project) and no detail/edit page. `scoped()` enforces
    // the payment belongs to the project belongs to the client.
    Route::resource('clients.projects.payments', PaymentController::class)
        ->scoped()
        ->only(['store', 'destroy']);

    // Completion toggles. Explicit POSTs (like the timers below) with scoped
    // bindings so the URL nesting must be real.
    Route::post('clients/{client}/projects/{project}/complete', [ProjectController::class, 'complete'])
        ->scopeBindings()
        ->name('clients.projects.complete');

    Route::post('clients/{client}/projects/{project}/reopen', [ProjectController::class, 'reopen'])
        ->scopeBindings()
        ->name('clients.projects.reopen');

    Route::post('clients/{client}/projects/{project}/tasks/{task}/complete', [TaskController::class, 'complete'])
        ->scopeBindings()
        ->name('clients.projects.tasks.complete');

    Route::post('clients/{client}/projects/{project}/tasks/{task}/reopen', [TaskController::class, 'reopen'])
        ->scopeBindings()
        ->name('clients.projects.tasks.reopen');

    Route::post('clients/{client}/projects/{project}/tasks/{task}/start-timer', [TaskController::class, 'startTimer'])
        ->scopeBindings()
        ->name('clients.projects.tasks.startTimer');

    Route::post('clients/{client}/projects/{project}/tasks/{task}/stop-timer', [TaskController::class, 'stopTimer'])
        ->scopeBindings()
        ->name('clients.projects.tasks.stopTimer');

    // Dismiss the persistent "last stopped timer" bar (forgets the last_timer cookie).
    Route::post('timer/dismiss', [TaskController::class, 'dismissLastTimer'])
        ->name('timer.dismiss');

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
