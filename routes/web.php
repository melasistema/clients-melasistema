<?php

use App\Http\Controllers\ClientController;
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

Route::resource('clients.projects', App\Http\Controllers\ProjectController::class)
    ->middleware(['auth', 'verified']);

Route::resource('clients.projects.tasks', App\Http\Controllers\TaskController::class)
    ->middleware(['auth', 'verified']);

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
