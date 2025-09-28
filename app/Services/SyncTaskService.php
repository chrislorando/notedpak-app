<?php

namespace App\Services;

use App\Models\Task;
use App\Models\User;
use Cache;
use Carbon\Carbon;
use App\Services\SupabaseService;
use Log;

class SyncTaskService
{
    protected SupabaseService $supabase;

    public function __construct(SupabaseService $supabase)
    {
        $this->supabase = $supabase;
    }

    /**
     * Sync task data from SQLite (local) to Supabase (Postgres)
     * Progress disimpan di Cache 'sync_tasks_progress'
     */
    public function sync()
    {
        Cache::put('sync_tasks_progress', 0, 3600);

        $localUsers = User::withTrashed()->get()->pluck('id')->toArray();
        $localTasks = Task::withTrashed()->whereIn('owner_id', $localUsers)->get()->keyBy('id');

        $remoteTasks = collect();
        foreach ($localUsers as $userId) {
            $response = $this->supabase->getTasks($userId);
            if ($response['success']) {
                $remoteTasks = $remoteTasks->merge($response['data']);
            } else {
                Log::error("Failed fetch tasks for user $userId from Supabase: " . json_encode($response['error']));
            }
        }
        $remoteTasks = $remoteTasks->keyBy('id');

        $allIds = $localTasks->keys()->merge($remoteTasks->keys())->unique();
        $total = $allIds->count();
        $count = 0;

        foreach ($allIds as $taskId) {
            $count++;
            $progress = intval(($count / $total) * 100);
            Cache::put('sync_tasks_progress', $progress, 3600);

            $local = $localTasks->get($taskId);
            $remote = $remoteTasks->get($taskId);

            if ($local && $remote) {
                $localUpdated  = Carbon::parse($local->updated_at);
                $remoteUpdated = Carbon::parse($remote['updated_at']);

                if ($remoteUpdated->greaterThan($localUpdated)) {
                    $local->update([
                        'description' => $remote['description'],
                        'note'       => $remote['note'],
                        'due_date'     => $remote['due_date'],
                        'position'    => $remote['position'],
                        'is_completed' => $remote['is_completed'],
                        'is_important' => $remote['is_important'],
                        'reminder_at' => $remote['reminder_at'],
                        'completed_at' => $remote['completed_at'],
                        'categories'  => $remote['categories'],
                        'updated_at'  => $remote['updated_at'],
                        'deleted_at'  => $remote['deleted_at'],
                    ]);
                } elseif ($localUpdated->greaterThan($remoteUpdated)) {
                    $payload = $local->makeHidden(['created_at_formatted', 'updated_at_formatted', 'completed_at_formatted'])->toArray();
                    $update = $this->supabase->updateTask($local->id, $payload);
                    if (! $update['success']) {
                        Log::error('Task sync error: ' . json_encode($update['error']));
                    }
                }
            } elseif (! $local && $remote) {
                Task::create([
                    'id'          => $remote['id'],
                    'project_id'  => $remote['project_id'],
                    'owner_id'    => $remote['owner_id'],
                    'description' => $remote['description'],
                    'note'       => $remote['note'],
                    'due_date'     => $remote['due_date'],
                    'position'    => $remote['position'],
                    'is_completed' => $remote['is_completed'],
                    'is_important' => $remote['is_important'],
                    'reminder_at' => $remote['reminder_at'],
                    'completed_at' => $remote['completed_at'],
                    'categories'  => $remote['categories'],
                    'created_at'  => $remote['created_at'],
                    'updated_at'  => $remote['updated_at'],
                    'deleted_at'  => $remote['deleted_at'],
                ]);
            } elseif ($local && ! $remote) {
                $payload = $local->makeHidden(['created_at_formatted', 'updated_at_formatted', 'completed_at_formatted'])->toArray();
                $insert = $this->supabase->addTask($payload);
                if (! $insert['success']) {
                    Log::error('Task insert to Supabase error: ' . json_encode($insert['error']));
                }
            }
        }

        Cache::put('sync_tasks_progress', 100, 3600);
        return true;
    }
}
