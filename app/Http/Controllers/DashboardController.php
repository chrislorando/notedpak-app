<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
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

    
        
        return Inertia::render('Dashboard', [
            
            'totalProject' => $totalProject,
            'totalTask' => $totalTask,
            'totalDraftTask' => $totalDraftTask,
            'totalDoneTask' => $totalDoneTask,
   
        ]);
    }
}
