<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Route::get('/', function () {
//     return Inertia::render('Welcome');
// })->name('home');

Route::get('/', [AuthenticatedSessionController::class, 'create'])
        ->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('projects', ProjectController::class);
    // Route::resource('tasks', TaskController::class);
    Route::get('tasks/{uuid}', [TaskController::class, 'index'])->name('tasks.show');
    Route::post('tasks', [TaskController::class, 'store']);
    Route::patch('tasks/complete/{id}', [TaskController::class, 'complete'])->name('tasks.complete');
    Route::patch('tasks/bookmark/{id}', [TaskController::class, 'bookmark'])->name('tasks.bookmark');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
