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

        // Get completed tasks
        $completed = Task::selectRaw('
                DATE(completed_at) as date,
                COUNT(*) as completed
            ')
            ->whereNotNull('completed_at')
            ->whereBetween('completed_at', [$startDate, $endDate])
            ->groupBy('date')
            ->pluck('completed', 'date')
            ->toArray();

        // Get pending tasks
        $pending = Task::selectRaw('
                DATE(created_at) as date,
                COUNT(*) as pending
            ')
            ->whereNull('completed_at')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('date')
            ->pluck('pending', 'date')
            ->toArray();

        // 3. Build full date list
        $dates = [];
        $currentDate = $startDate->copy();
        while ($currentDate <= $endDate) {
            $dates[] = $currentDate->format('Y-m-d');
            $currentDate->addDay();
        }

         // 4. Build chartData
        $chartData = [];
        foreach ($dates as $date) {
            $chartData[] = [
                'date' => Carbon::parse($date)->format('d/m/Y'),
                'pending' => isset($pending[$date]) ? (int) $pending[$date] : 0,
                'completed' => isset($completed[$date]) ? (int) $completed[$date] : 0,
            ];
        }

    
        
        return Inertia::render('dashboard', [
            'totalProject' => auth()->user()->projects()->count(),
            'totalTask' => auth()->user()->tasks()->count(),
            'totalCompletedTask' => auth()->user()->tasks()->completed()->count(),
            'totalDraftTask' => auth()->user()->tasks()->draft()->count(),
            'chartData' => $chartData,
   
           
        ]);
    }
}
