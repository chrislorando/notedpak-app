<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Cache;
use Str;

class Project extends Model
{
    use HasFactory;

    public $fillable = [
        'uuid',
        'name',
        'description',
        'user_id',
        'color'
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->uuid = Str::uuid();
            $model->user_id = auth()->user()->id ?? 1;
            // $model->user_id = 1;
        });

        static::updating(function ($model) {
            $model->user_id = auth()->user()->id ?? 1;
            // $model->user_id = 1;
        });

        static::saved(function ($model) {
            Cache::forget('user_projects_tasks_' . $model->user_id);
        });

        static::deleted(function ($model) {
            Cache::forget('user_projects_tasks_' . $model->user_id);
        });
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function draftTasks()
    {
        return $this->hasMany(Task::class)->draft();
    }

    public function completedTasks()
    {
        return $this->hasMany(Task::class)->completed();
    }

    public function importantTasks()
    {
        return $this->hasMany(Task::class)->important();
    }

    public function importantDraftTasks()
    {
        return $this->hasMany(Task::class)->important()->draft();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
