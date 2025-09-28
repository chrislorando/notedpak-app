<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class SupabaseService
{
    protected string $url;
    protected string $anonKey;

    public function __construct()
    {
        $this->url     = rtrim(config('services.supabase.url'), '/');
        $this->anonKey = config('services.supabase.anon_key');
    }

    protected function headers(): array
    {
        return [
            'apikey'        => $this->anonKey,
            'Authorization' => 'Bearer ' . $this->anonKey,
            'Content-Type'  => 'application/json',
        ];
    }

    public function getUserById(string $id): array
    {
        $response = Http::withHeaders($this->headers())
            ->get("{$this->url}/rest/v1/users", [
                'id' => "eq.$id",
                // 'deleted_at' => "is.null",
            ]);

        if ($response->successful()) {
            return ['success' => true, 'data' => $response->json()];
        }

        return ['success' => false, 'status' => $response->status(), 'error' => $response->json()];
    }

    public function getUserByEmail(string $email): array
    {
        $response = Http::withHeaders($this->headers())
            ->get("{$this->url}/rest/v1/users", [
                'email' => "eq.$email",
                // 'deleted_at' => "is.null",
            ]);

        if ($response->successful()) {
            return ['success' => true, 'data' => $response->json()];
        }

        return ['success' => false, 'status' => $response->status(), 'error' => $response->json()];
    }

    public function getUsers()
    {
        $response = Http::withHeaders($this->headers())
            ->get("{$this->url}/rest/v1/users", [
                'select' => '*',
                // 'deleted_at' => "is.null",
            ]);

        return $response->json();
    }

    public function addUser(array $data)
    {
        $response = Http::withHeaders($this->headers())
            ->post("{$this->url}/rest/v1/users", $data);

        if ($response->successful()) {
            return [
                'success' => true,
                'data'    => $response->json(),
            ];
        }

        return [
            'success' => false,
            'status'  => $response->status(),
            'error'   => $response->json(),
        ];
    }

    public function updateUser(string $id, array $data)
    {
        $response = Http::withHeaders($this->headers())
            ->patch("{$this->url}/rest/v1/users?id=eq.{$id}", $data);

        if ($response->successful()) {
            return [
                'success' => true,
                'data'    => $response->json(),
            ];
        }

        return [
            'success' => false,
            'status'  => $response->status(),
            'error'   => $response->json(),
        ];
    }


    public function getProjectById(string $id): array
    {
        $response = Http::withHeaders($this->headers())
            ->get("{$this->url}/rest/v1/projects", [
                'id' => "eq.$id",
                // 'deleted_at' => "is.null",
            ]);

        if ($response->successful()) {
            return ['success' => true, 'data' => $response->json()];
        }

        return ['success' => false, 'status' => $response->status(), 'error' => $response->json()];
    }

    public function getProjects(string $userId): array
    {
        try {
            $response = Http::withHeaders($this->headers())
            ->get($this->url . '/rest/v1/projects', [
                'user_id' => 'eq.' . $userId, 
                'select'  => '*',
                // 'deleted_at' => "is.null",
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                    'error' => null,
                ];
            }

            return [
                'success' => false,
                'data' => [],
                'error' => $response->body(),
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'data' => [],
                'error' => $e->getMessage(),
            ];
        }
    }

    public function addProject(array $data)
    {
        $response = Http::withHeaders($this->headers())
            ->post("{$this->url}/rest/v1/projects", $data);

        if ($response->successful()) {
            return [
                'success' => true,
                'data'    => $response->json(),
            ];
        }

        return [
            'success' => false,
            'status'  => $response->status(),
            'error'   => $response->json(),
        ];
    }

    public function updateProject(string $id, array $data)
    {
        $response = Http::withHeaders($this->headers())
            ->patch("{$this->url}/rest/v1/projects?id=eq.{$id}", $data);

        if ($response->successful()) {
            return [
                'success' => true,
                'data'    => $response->json(),
            ];
        }

        return [
            'success' => false,
            'status'  => $response->status(),
            'error'   => $response->json(),
        ];
    }

    public function getTaskById(string $id): array
    {
        $response = Http::withHeaders($this->headers())
            ->get("{$this->url}/rest/v1/tasks", [
                'id' => "eq.$id",
                // 'deleted_at' => "is.null",
            ]);

        if ($response->successful()) {
            return ['success' => true, 'data' => $response->json()];
        }

        return ['success' => false, 'status' => $response->status(), 'error' => $response->json()];
    }

    public function getTasks(string $userId): array
    {
        try {
            $response = Http::withHeaders($this->headers())
            ->get($this->url . '/rest/v1/tasks', [
                'owner_id' => 'eq.' . $userId, 
                'select'  => '*',
                // 'deleted_at' => "is.null",
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                    'error' => null,
                ];
            }

            return [
                'success' => false,
                'data' => [],
                'error' => $response->body(),
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'data' => [],
                'error' => $e->getMessage(),
            ];
        }
    }

    public function addTask(array $data)
    {
        $response = Http::withHeaders($this->headers())
            ->post("{$this->url}/rest/v1/tasks", $data);

        if ($response->successful()) {
            return [
                'success' => true,
                'data'    => $response->json(),
            ];
        }

        return [
            'success' => false,
            'status'  => $response->status(),
            'error'   => $response->json(),
        ];
    }

    public function updateTask(string $id, array $data)
    {
        $response = Http::withHeaders($this->headers())
            ->patch("{$this->url}/rest/v1/tasks?id=eq.{$id}", $data);

        if ($response->successful()) {
            return [
                'success' => true,
                'data'    => $response->json(),
            ];
        }

        return [
            'success' => false,
            'status'  => $response->status(),
            'error'   => $response->json(),
        ];
    }


}
