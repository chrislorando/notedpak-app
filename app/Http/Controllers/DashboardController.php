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
        ->orderBy('total', 'desc')
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
}
