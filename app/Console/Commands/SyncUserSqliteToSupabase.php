<?php

namespace App\Console\Commands;

use App\Services\SupabaseService;
use App\Models\User;
use Cache;
use DB;
use Illuminate\Console\Command;
use Log;

class SyncUserSqliteToSupabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:user-sqlite-supabase';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync user data from SQLite (local) to Supabase (Postgres)';

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
        $this->info('Start sync users with Supabase...');

        $localUsers = User::withTrashed()->get();
        $total = $localUsers->count();
        $count = 0;

        foreach ($localUsers as $lUser) {
            $count++;
            $progress = intval(($count / $total) * 100);
            Cache::put('sync_users_progress', $progress, 3600);
            $this->line("➡️ Processing {$lUser->email}");

            // payload lokal
            $payload = [
                'id'         => $lUser->id,
                'email'      => $lUser->email,
                'name'       => $lUser->name,
                'password'   => $lUser->password,
                'created_at' => $lUser->created_at,
                'updated_at' => $lUser->updated_at,
                'deleted_at' => $lUser->deleted_at,
            ];

            // cek user di Supabase
            $check = $this->supabase->getUserByEmail($lUser->email);

            if (! $check['success']) {
                $this->error("❌ Error check {$lUser->email} | Status: {$check['status']} | " . json_encode($check['error']));
                continue;
            }

            $remote = $check['data'][0] ?? null;

            if (! $remote) {
                // Supabase kosong → insert
                $insert = $this->supabase->addUser($payload);

                if ($insert['success']) {
                    $this->info("✅ User {$lUser->email} inserted to Supabase.");
                } else {
                    $this->error("❌ Failed insert {$lUser->email} | Status: {$insert['status']} | " . json_encode($insert['error']));
                }
            } else {
                // ada di Supabase → compare updated_at
                $remoteUpdated = \Carbon\Carbon::parse($remote['updated_at']);
                $localUpdated  = \Carbon\Carbon::parse($lUser->updated_at);

                if ($remoteUpdated->greaterThan($localUpdated)) {
                    // Supabase lebih baru → update lokal
                    $lUser->update([
                        'name'       => $remote['name'],
                        'password'   => $remote['password'],
                        'updated_at' => $remote['updated_at'],
                        'deleted_at' => $remote['deleted_at'],
                    ]);

                    $this->info("⬇️ User {$lUser->email} updated from Supabase to local.");
                } elseif ($localUpdated->greaterThan($remoteUpdated)) {
                    // Lokal lebih baru → update Supabase
                    $update = $this->supabase->updateUser($lUser->id, $payload);
                    Log::info('User payload: ' . json_encode($update));

                    if ($update['success']) {
                        $this->info("⬆️ User {$lUser->email} updated from local to Supabase.");
                    } else {
                        Log::error('User sync error: ' . json_encode($update['error']));
                        $this->error("❌ Failed update {$lUser->email} to Supabase | Status: {$update['status']} | " . json_encode($update['error']));
                    }
                } else {
                    $this->line("⏸️ User {$lUser->email} already up-to-date.");
                }
            }
        }

        Cache::put('sync_users_progress', 100, 3600);
        $this->info('Done.');
        return Command::SUCCESS;
    }

}
