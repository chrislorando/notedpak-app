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
    Route::patch('projects/copy/{id}', [ProjectController::class, 'copyList'])->name('projects.copy');
    Route::resource('projects', ProjectController::class);
    // Route::resource('tasks', TaskController::class);
    Route::get('tasks/search-list-options', [TaskController::class, 'searchListOptions'])->name('tasks.search-list-options');
    Route::get('tasks/{uuid}', [TaskController::class, 'index'])->name('tasks.show');
    Route::post('tasks', [TaskController::class, 'store']);
    Route::put('tasks/{id}', [TaskController::class, 'update'])->name('tasks.update');
    Route::patch('tasks/complete/{id}', [TaskController::class, 'complete'])->name('tasks.complete');
    Route::patch('tasks/bookmark/{id}', [TaskController::class, 'bookmark'])->name('tasks.bookmark');
    Route::patch('tasks/copy/{id}', [TaskController::class, 'copyTask'])->name('tasks.copy');
    Route::patch('tasks/move/{id}', [TaskController::class, 'moveTask'])->name('tasks.move');
    Route::delete('tasks/{id}', [TaskController::class, 'destroy'])->name('tasks.destroy');
    Route::post('tasks/upload-file/{id}', [TaskController::class, 'uploadFile'])->name('tasks.upload-file');
    Route::delete('tasks/delete-file/{id}', [TaskController::class, 'deleteFile'])->name('tasks.delete-file');
   
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
