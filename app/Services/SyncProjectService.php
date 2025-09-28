<?php

namespace App\Services;

use App\Models\Project;
use Cache;
use Carbon\Carbon;
use App\Services\SupabaseService;
use Log;

class SyncProjectService
{
    protected SupabaseService $supabase;

    public function __construct(SupabaseService $supabase)
    {
        $this->supabase = $supabase;
    }

    /**
     * Sync project data from SQLite (local) to Supabase (Postgres)
     * Progress disimpan di Cache 'sync_projects_progress'
     */
    public function sync()
    {
        Cache::put('sync_projects_progress', 0, 3600);

        // Ambil semua user lokal
        $localUsers = \App\Models\User::withTrashed()->get()->pluck('id')->toArray();

        // Ambil semua project lokal untuk user yang ada
        $localProjects = Project::withTrashed()->whereIn('user_id', $localUsers)->get()->keyBy('id');

        // Ambil semua project Supabase untuk user lokal
        $remoteProjects = collect();
        foreach ($localUsers as $userId) {
            $response = $this->supabase->getProjects($userId);
            if ($response['success']) {
                $remoteProjects = $remoteProjects->merge($response['data']);
            } else {
                Log::error("Failed fetch projects for user $userId from Supabase: " . json_encode($response['error']));
            }
        }
        $remoteProjects = $remoteProjects->keyBy('id');

        $allIds = $localProjects->keys()->merge($remoteProjects->keys())->unique();
        $total = $allIds->count();
        $count = 0;

        foreach ($allIds as $projectId) {
            $count++;
            $progress = intval(($count / $total) * 100);
            Cache::put('sync_projects_progress', $progress, 3600);

            $local = $localProjects->get($projectId);
            $remote = $remoteProjects->get($projectId);

            if ($local && $remote) {
                $localUpdated  = Carbon::parse($local->updated_at);
                $remoteUpdated = Carbon::parse($remote['updated_at']);

                if ($remoteUpdated->greaterThan($localUpdated)) {
                    // update local
                    $local->update([
                        'name' => $remote['name'],
                        'color' => $remote['color'],
                        'user_id' => $remote['user_id'],
                        'updated_at' => $remote['updated_at'],
                        'deleted_at' => $remote['deleted_at'],
                    ]);
                } elseif ($localUpdated->greaterThan($remoteUpdated)) {
                    // update remote
                    $payload = $local->toArray();
                    $update = $this->supabase->updateProject($local->id, $payload);
                    if (! $update['success']) {
                        Log::error('Project sync error: ' . json_encode($update['error']));
                    }
                }
            } elseif (! $local && $remote) {
                // insert ke local
                Project::create([
                    'id' => $remote['id'],
                    'name' => $remote['name'],
                    'color' => $remote['color'],
                    'user_id' => $remote['user_id'],
                    'created_at' => $remote['created_at'],
                    'updated_at' => $remote['updated_at'],
                    'deleted_at' => $remote['deleted_at'],
                ]);
            } elseif ($local && ! $remote) {
                // insert ke Supabase
                $payload = $local->toArray();
                $insert = $this->supabase->addProject($payload);
                if (! $insert['success']) {
                    Log::error('Project insert to Supabase error: ' . json_encode($insert['error']));
                }
            }
        }

        Cache::put('sync_projects_progress', 100, 3600);
        return true;
    }
}
