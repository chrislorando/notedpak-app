<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\SyncController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Illuminate\Http\Request;

// Route::get('/', function () {
//     return Inertia::render('Welcome');
// })->name('home');

Route::get('/', [AuthenticatedSessionController::class, 'create'])->name('home');

// Route::get('dashboard', function () {
//     return Inertia::render('Dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('home');
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('dashboard/sync', [DashboardController::class, 'sync'])->name('dashboard.sync');
    Route::get('dashboard/sync-status', [DashboardController::class, 'syncStatus'])->name('dashboard.sync-status');

    // Route::resource('projects', ProjectController::class);

    Route::patch('projects/copy/{id}', [ProjectController::class, 'copyList'])->name('projects.copy');
    Route::patch('projects/reorder', [ProjectController::class, 'reorder'])->name('projects.reorder');
    Route::resource('projects', ProjectController::class);
    // Route::resource('tasks', TaskController::class);

    Route::get('tasks', [TaskController::class, 'search'])->name('tasks.search');
    Route::get('tasks/download-file', function(Request $request){
        $fullUrl = $request->file; 

        $parsedPath = parse_url($fullUrl, PHP_URL_PATH); 
        $relativePath = ltrim($parsedPath, '/todo/');

        $originalName = basename($relativePath);
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);

        $customName = 'Todo_file_'. now()->format('YmdHis').'.' . $extension;

        $stream = Storage::disk('s3')->readStream($relativePath);

        return response()->stream(function() use ($stream) {
            fpassthru($stream);
        }, 200, [
            'Content-Type' => Storage::disk('s3')->mimeType($relativePath),
            'Content-Length' => Storage::disk('s3')->size($relativePath),
            'Content-Disposition' => 'attachment; filename="'.$customName.'"',
        ]);
    })->name('tasks.download-file');
    Route::get('tasks/search-list-options', [TaskController::class, 'searchListOptions'])->name('tasks.search-list-options');
    Route::get('tasks/{id}', [TaskController::class, 'index'])->name('tasks.show');
    Route::post('tasks', [TaskController::class, 'store']);
    Route::put('tasks/{id}', [TaskController::class, 'update'])->name('tasks.update');
    Route::patch('tasks/complete/{id}', [TaskController::class, 'complete'])->name('tasks.complete');
    Route::patch('tasks/bookmark/{id}', [TaskController::class, 'bookmark'])->name('tasks.bookmark');
    Route::patch('tasks/copy/{id}', [TaskController::class, 'copyTask'])->name('tasks.copy');
    Route::patch('tasks/move/{id}', [TaskController::class, 'moveTask'])->name('tasks.move');
    Route::delete('tasks/{id}', [TaskController::class, 'destroy'])->name('tasks.destroy');
    Route::post('tasks/upload-file/{id}', [TaskController::class, 'uploadFile'])->name('tasks.upload-file');
    Route::delete('tasks/delete-file/{id}', [TaskController::class, 'deleteFile'])->name('tasks.delete-file');
    Route::patch( 'tasks/reorder', [TaskController::class, 'reorder'])->name('tasks.reorder');

    Route::get('syncs', [SyncController::class, 'index'])->name('syncs.index');
    Route::post('syncs/start', [SyncController::class, 'start'])->name('syncs.start');
    Route::get('syncs/get-status', [SyncController::class, 'getStatus'])->name('syncs.get-status');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
