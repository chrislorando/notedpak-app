<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\SoftDeletes;
use Str;

class Task extends Model
{
    use HasFactory, SoftDeletes;

    protected $keyType = 'string';
    public $incrementing = false;

    public $fillable = [
        'id',
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
        'categories',
        'position',
        'deleted_at',
        'created_at',
        'updated_at',
    ];

    protected $appends = [
        'created_at_formatted',
        'updated_at_formatted',
        'completed_at_formatted',
    ];

    protected $casts = [
        // 'created_at' => 'datetime:d/m/Y H:i:s',
        // 'updated_at' => 'datetime:d/m/Y H:i:s',
        // 'completed_at' => 'datetime:d/m/Y H:i:s',
        'categories' => 'array',
    ];

    protected function createdAtFormatted(): Attribute
    {
    return Attribute::get(fn () => $this->created_at ? $this->created_at->format('d/m/Y H:i:s') : null);
    }

    protected function updatedAtFormatted(): Attribute
    {
    return Attribute::get(fn () => $this->updated_at ? $this->updated_at->format('d/m/Y H:i:s') : null);
    }

    protected function completedAtFormatted(): Attribute
    {
        return Attribute::get(fn () => $this->completed_at ? Carbon::parse($this->completed_at)->format('d/m/Y H:i:s') : null);

    }

    protected static function booted()
    {
        static::creating(function ($model) {
            if (! $model->id) {
                $model->id = Str::uuid();
            }
            $model->owner_id = auth()->user()->id ?? User::first()->id;
        });

        static::updating(function ($model) {
            $model->owner_id = auth()->user()->id ?? User::first()->id;
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
