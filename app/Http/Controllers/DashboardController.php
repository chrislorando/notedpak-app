<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use Artisan;
use Cache;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $startDate = Carbon::now()->subDays(7); // or Carbon::now()->subDays(6);
        $endDate = Carbon::now();

        $totalProject = Project::where('user_id', auth()->user()->id)
            // ->whereBetween('created_at', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->count();

        $totalTask = Task::where('owner_id', auth()->user()->id)
            // ->whereBetween('created_at', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->count();

        $totalDraftTask = Task::where('owner_id', auth()->user()->id)
            ->where('is_completed', false)
            ->count();

        $totalDoneTask = Task::where('owner_id', auth()->user()->id)
            ->where('is_completed', true)
            ->count();

        $projects = Project::withCount([
            'tasks as total' => fn ($q) => $q,
            'tasks as draft' => fn ($q) => $q->where('is_completed', false),
            'tasks as complete' => fn ($q) => $q->where('is_completed', true),
        ])
        ->where('user_id', auth()->user()->id)
        ->whereRaw('(select count(*) from tasks where projects.id = tasks.project_id and is_completed = false) > 0')
        ->orderBy('created_at', 'asc')
        ->limit(5)
        ->get();

        $chartData = $projects->map(function ($project) {
            return [
                'name' => $project->name,
                'total' => $project->total,
                'draft' => $project->draft,
                'complete' => $project->complete,
                // 'color' => $project->color,
            ];
        });

        $dueDates = Task::with('project')->where('owner_id', auth()->user()->id)
            ->where('is_completed', false)
            ->whereNotNull('due_date')
            ->limit(6)
            ->orderBy('due_date', 'asc')
            ->get();

        
        return Inertia::render('Dashboard', [
            'totalProject' => $totalProject,
            'totalTask' => $totalTask,
            'totalDraftTask' => $totalDraftTask,
            'totalDoneTask' => $totalDoneTask,
            'chartData' => $chartData,
            'dueDates' => $dueDates,
        ]);
    }

    public function sync(Request $request)
    {
        // Cache::put('sync_status', ['status' => 'running', 'progress' => 0], 3600);

        $commandUser = 'sync:user-sqlite-supabase';
        $commandProject = 'sync:project-sqlite-supabase';
        $commandTask = 'sync:task-sqlite-supabase';
        $cleansing = 'cleanse:all';

        if (strncasecmp(PHP_OS, 'WIN', 3) === 0) {
            pclose(popen("start /B php \"" . base_path('artisan') . "\" $commandUser", "r"));
            pclose(popen("start /B php \"" . base_path('artisan') . "\" $commandProject ", "r"));
            pclose(popen("start /B php \"" . base_path('artisan') . "\" $commandTask ", "r"));
            // pclose(popen("start /B php \"" . base_path('artisan') . "\" $cleansing ", "r"));
        } else {
            exec('php ' . base_path('artisan') . ' $commandUser > /dev/null 2>&1 &');
            exec('php ' . base_path('artisan') . ' $commandProject > /dev/null 2>&1 &');
            exec('php ' . base_path('artisan') . ' $commandTask > /dev/null 2>&1 &');
            // exec('php ' . base_path('artisan') . ' $cleansing > /dev/null 2>&1 &');
        }


        return back();
    }

    public function syncStatus(Request $request)
    {
        $users = Cache::get('sync_users_progress', 0);
        $projects = Cache::get('sync_projects_progress', 0);
        $tasks = Cache::get('sync_tasks_progress', 0);

        $progress = intval(($users + $projects + $tasks) / 3);

        $status = $progress < 100 ? 'running' : 'done';

        // Cache::put('sync_status', ['status' => $status, 'progress' => $progress], 3600);
        // $status = Cache::get('sync_status', ['status' => 'idle', 'progress' => 0]);

        if ($request->header('X-Inertia')) {
            return back(); // atau Inertia::render(...) sesuai kebutuhan
        }
        // Cache::put('sync_tasks_progress', 100, 3600);
        
        return response()->json([
            'status' => $status,
            'progress' => $progress,
            'users_progress' => $users,
            'projects_progress' => $projects,
            'tasks_progress' => $tasks,
        ]);
    }
}
