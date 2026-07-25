<?php

use App\Http\Controllers\ClientController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::resource('clients', ClientController::class)
    ->middleware(['auth', 'verified']);

Route::resource('clients.projects', ProjectController::class)
    ->middleware(['auth', 'verified']);

Route::resource('clients.projects.tasks', TaskController::class)
    ->middleware(['auth', 'verified']);

Route::post('clients/{client}/projects/{project}/tasks/{task}/start-timer', [TaskController::class, 'startTimer'])
    ->middleware(['auth', 'verified'])
    ->name('clients.projects.tasks.startTimer');

Route::post('clients/{client}/projects/{project}/tasks/{task}/stop-timer', [TaskController::class, 'stopTimer'])
    ->middleware(['auth', 'verified'])
    ->name('clients.projects.tasks.stopTimer');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
