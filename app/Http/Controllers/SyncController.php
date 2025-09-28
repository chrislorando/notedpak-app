<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Cache;
use Inertia\Inertia;

class SyncController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('Sync');
    }

    public function start(Request $request)
    {
        app(\App\Services\SyncUserService::class)->sync();
        app(\App\Services\SyncProjectService::class)->sync();
        app(\App\Services\SyncTaskService::class)->sync();

        if ($request->header('X-Inertia')) {
            return back(); 
        }

        return response()->json(['status' => 'started']);
    }

    public function getStatus(Request $request)
    {
        $users = Cache::get('sync_users_progress', 0);
        $projects = Cache::get('sync_projects_progress', 0);
        $tasks = Cache::get('sync_tasks_progress', 0);


        $progress = intval(($users + $projects + $tasks) / 3);
        $status = $progress < 100 ? 'running' : 'done';

        if ($request->header('X-Inertia')) {
            return back(); 
        }

        return response()->json([
            'status' => $status,
            'progress' => $progress,
            'users_progress' => $users,
            'projects_progress' => $projects,
            'tasks_progress' => $tasks,
        ]);
    }
}
