<?php

namespace App\Console\Commands;

use App\Models\Task;
use App\Services\SupabaseService;
use App\Models\User;
use Cache;
use Carbon\Carbon;
use DB;
use Illuminate\Console\Command;
use Log;

class SyncTaskSqliteToSupabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:task-sqlite-supabase';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync task data from SQLite (local) to Supabase (Postgres)';

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
        $this->info('Start sync tasks with Supabase...');

        // Inisialisasi progress
        Cache::put('sync_tasks_progress', 0, 3600);

        // Ambil semua user lokal
        $localUsers = User::withTrashed()->get()->pluck('id')->toArray();

        // Ambil semua task lokal untuk user yang ada
        $localTasks = Task::withTrashed()->whereIn('owner_id', $localUsers)->get()->keyBy('id');

        // Ambil semua task Supabase untuk user lokal
        $remoteTasks = collect();
        foreach ($localUsers as $userId) {
            $response = $this->supabase->getTasks($userId);
            if ($response['success']) {
                $remoteTasks = $remoteTasks->merge($response['data']);
            } else {
                $this->error("❌ Failed fetch tasks for user $userId from Supabase.");
            }
        }
        $remoteTasks = $remoteTasks->keyBy('id');

        // Semua ID unik (gabungan local + remote)
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
                // compare updated_at
                $localUpdated  = Carbon::parse($local->updated_at);
                $remoteUpdated = Carbon::parse($remote['updated_at']);

                if ($remoteUpdated->greaterThan($localUpdated)) {
                    // update local
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
                    $this->info("⬇️ Task {$local->description} updated from Supabase to local.");
                } elseif ($localUpdated->greaterThan($remoteUpdated)) {
                    // update remote
                    $payload = $local->makeHidden(['created_at_formatted', 'updated_at_formatted', 'completed_at_formatted'])->toArray();
                    $update = $this->supabase->updateTask($local->id, $payload);
                    if ($update['success']) {
                        // Task::onlyTrashed()->forceDelete();
                        $this->info("⬆️ Task {$local->description} updated from local to Supabase.");
                    } else {
                        Log::error('Task sync error: ' . json_encode($update['error']));
                        $this->error("❌ Failed update {$local->description} to Supabase.");
                    }
                } else {
                    $this->line("⏸️ Task {$local->description} already up-to-date.");
                }
            } elseif (! $local && $remote) {
                // insert ke local
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
                $this->info("⬇️ Task {$remote['description']} inserted from Supabase to local.");
            } elseif ($local && ! $remote) {
                // insert ke Supabase
                $payload = $local->makeHidden(['created_at_formatted', 'updated_at_formatted', 'completed_at_formatted'])->toArray();
                $insert = $this->supabase->addTask($payload);
                if ($insert['success']) {
                    // Task::onlyTrashed()->forceDelete();
                    $this->info("⬆️ Task {$local->description} inserted from local to Supabase.");
                } else {
                    Log::error('Task insert to Supabase error: ' . json_encode($insert['error']));
                    $this->error("❌ Failed insert task {$local->id} to Supabase.");
                }
            }
        }

        Cache::put('sync_tasks_progress', 100, 3600);
        $this->info('Done.');
        return Command::SUCCESS;
    }

}
