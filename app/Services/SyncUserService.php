<?php

namespace App\Services;

use App\Models\User;
use Cache;
use Carbon\Carbon;
use App\Services\SupabaseService;
use Log;

class SyncUserService
{
    protected SupabaseService $supabase;

    public function __construct(SupabaseService $supabase)
    {
        $this->supabase = $supabase;
    }

    /**
     * Sync user data from SQLite (local) to Supabase (Postgres)
     * Progress disimpan di Cache 'sync_users_progress'
     */
    public function sync()
    {
        Cache::put('sync_users_progress', 0, 3600);

        $localUsers = User::withTrashed()->get();
        $total = $localUsers->count();
        $count = 0;

        foreach ($localUsers as $lUser) {
            $count++;
            $progress = intval(($count / $total) * 100);
            Cache::put('sync_users_progress', $progress, 3600);

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
                Log::error("Error check {$lUser->email} | Status: {$check['status']} | " . json_encode($check['error']));
                continue;
            }

            $remote = $check['data'][0] ?? null;

            if (! $remote) {
                // Supabase kosong → insert
                $insert = $this->supabase->addUser($payload);
                if (! $insert['success']) {
                    Log::error("Failed insert {$lUser->email} | Status: {$insert['status']} | " . json_encode($insert['error']));
                }
            } else {
                // ada di Supabase → compare updated_at
                $remoteUpdated = Carbon::parse($remote['updated_at']);
                $localUpdated  = Carbon::parse($lUser->updated_at);

                if ($remoteUpdated->greaterThan($localUpdated)) {
                    // Supabase lebih baru → update lokal
                    $lUser->update([
                        'name'       => $remote['name'],
                        'password'   => $remote['password'],
                        'updated_at' => $remote['updated_at'],
                        'deleted_at' => $remote['deleted_at'],
                    ]);
                } elseif ($localUpdated->greaterThan($remoteUpdated)) {
                    // Lokal lebih baru → update Supabase
                    $update = $this->supabase->updateUser($lUser->id, $payload);
                    if (! $update['success']) {
                        Log::error('User sync error: ' . json_encode($update['error']));
                    }
                }
            }
        }

        Cache::put('sync_users_progress', 100, 3600);
        return true;
    }
}
