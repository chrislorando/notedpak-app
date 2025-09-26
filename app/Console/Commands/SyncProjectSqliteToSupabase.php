<?php

namespace App\Console\Commands;

use App\Models\Project;
use App\Services\SupabaseService;
use App\Models\User;
use Cache;
use Carbon\Carbon;
use DB;
use Illuminate\Console\Command;
use Log;

class SyncProjectSqliteToSupabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:project-sqlite-supabase';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync project data from SQLite (local) to Supabase (Postgres)';

    protected SupabaseService $supabase;

    public function __construct(SupabaseService $supabase)
    {
        parent::__construct();
        $this->supabase = $supabase;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Start sync projects with Supabase...');

        // Inisialisasi progress
        Cache::put('sync_projects_progress', 0, 3600);

        // Ambil semua user lokal
        $localUsers = User::withTrashed()->get()->pluck('id')->toArray();

        // Ambil semua project lokal untuk user yang ada
        $localProjects = Project::withTrashed()->whereIn('user_id', $localUsers)->get()->keyBy('id');

        // Ambil semua project Supabase untuk user lokal
        $remoteProjects = collect();
        foreach ($localUsers as $userId) {
            $response = $this->supabase->getProjects($userId);
            if ($response['success']) {
                $remoteProjects = $remoteProjects->merge($response['data']);
            } else {
                $this->error("❌ Failed fetch projects for user $userId from Supabase.");
            }
        }
        $remoteProjects = $remoteProjects->keyBy('id');

        // Semua ID unik (gabungan local + remote)
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
                // compare updated_at
                $localUpdated  = Carbon::parse($local->updated_at);
                $remoteUpdated = Carbon::parse($remote['updated_at']);

                if ($remoteUpdated->greaterThan($localUpdated)) {
                    // update local
                    $local->update([
                        'name'        => $remote['name'],
                        'description' => $remote['description'],
                        'color'       => $remote['color'],
                        'user_id'     => $remote['user_id'],
                        'position'    => $remote['position'],
                        'updated_at'  => $remote['updated_at'],
                        'deleted_at'  => $remote['deleted_at'],
                    ]);
                    $this->info("⬇️ Project {$local->name} updated from Supabase to local.");
                } elseif ($localUpdated->greaterThan($remoteUpdated)) {
                    // update remote
                    $payload = $local->toArray();
                    $update = $this->supabase->updateProject($local->id, $payload);
                    if ($update['success']) {
                        // Project::onlyTrashed()->forceDelete();
                        $this->info("⬆️ Project {$local->name} updated from local to Supabase.");
                    } else {
                        Log::error('Project sync error: ' . json_encode($update['error']));
                        $this->error("❌ Failed update {$local->name} to Supabase.");
                    }
                } else {
                    $this->line("⏸️ Project {$local->name} already up-to-date.");
                }
            } elseif (! $local && $remote) {
                // insert ke local
                Project::create([
                    'id'          => $remote['id'],
                    'name'        => $remote['name'],
                    'description' => $remote['description'],
                    'color'       => $remote['color'],
                    'user_id'     => $remote['user_id'],
                    'position'    => $remote['position'],
                    'created_at'  => $remote['created_at'],
                    'updated_at'  => $remote['updated_at'],
                    'deleted_at'  => $remote['deleted_at'],
                ]);
                $this->info("⬇️ Project {$remote['name']} inserted from Supabase to local.");
            } elseif ($local && ! $remote) {
                // insert ke Supabase
                $payload = $local->toArray();
                $insert = $this->supabase->addProject($payload);
                if ($insert['success']) {
                    // Project::onlyTrashed()->forceDelete();
                    $this->info("⬆️ Project {$local->name} inserted from local to Supabase.");
                } else {
                    Log::error('Project insert to Supabase error: ' . json_encode($insert['error']));
                    $this->error("❌ Failed insert {$local->name} to Supabase.");
                }
            }
        }

        Cache::put('sync_projects_progress', 100, 3600);
        $this->info('Done.');
        return Command::SUCCESS;
    }

}
