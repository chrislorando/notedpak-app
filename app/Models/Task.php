<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Str;

class Task extends Model
{
    use HasFactory;
    public $fillable = [
        'uuid',
        'name',
        'description',
        'note',
        'due_date',
        'project_id',
        'owner_id',
        'assignee_id',
        'is_completed',
        'is_important',
        'completed_at',
        'categories'
    ];

    protected $casts = [
        'created_at' => 'datetime:d/m/Y H:i:s',
        'updated_at' => 'datetime:d/m/Y H:i:s',
        'completed_at' => 'datetime:d/m/Y H:i:s',
        'categories' => 'array',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->uuid = Str::uuid();
            $model->owner_id = auth()->user()->id ?? 1;
        });

        static::updating(function ($model) {
            $model->owner_id = auth()->user()->id ?? 1;
            if($model->is_completed) {
                $model->completed_at = now();
            }else{
                $model->completed_at = null;
            }
        });

    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
    
    public function user()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function scopeDraft(Builder $query): void
    {
        $query->where('is_completed', false);
    }
    
    public function scopeCompleted(Builder $query): void
    {
        $query->where('is_completed', true);
    }

    public function scopeImportant(Builder $query): void
    {
        $query->where('is_important', true);
    }

    public function scopeImportantDraft(Builder $query): void
    {
        $query->where('is_important', true)->where('is_completed', false);
    }

    public function attachments()
    {
        return $this->hasMany(TaskFiles::class);
    }
}
