<?php

namespace App\Console\Commands;

use App\Models\Project;
use App\Models\Task;
use App\Models\TaskFiles;
use App\Services\SupabaseService;
use App\Models\User;
use Cache;
use DB;
use Illuminate\Console\Command;
use Log;

class CleanseAll extends Command
{
    protected $signature = 'cleanse:all';
    protected $description = 'Permanently delete all soft deleted records (users, projects, tasks, file_tasks)';

    public function handle()
    {
        $this->info('Starting cleansing process...');

        $models = [
            User::class,
            Project::class,
            Task::class,
            TaskFiles::class,
        ];

        foreach ($models as $model) {
            $count = $model::onlyTrashed()->count();

            if ($count > 0) {
                $model::onlyTrashed()->forceDelete();
                $this->info("{$count} " . class_basename($model) . ' records permanently deleted.');
            } else {
                $this->line('No soft deleted ' . class_basename($model) . ' found.');
            }
        }

        $this->info('Cleansing completed.');
        return 0;
    }

}
