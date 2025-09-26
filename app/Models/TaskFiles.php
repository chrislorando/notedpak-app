<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Str;

class TaskFiles extends Model
{
    use SoftDeletes;

    protected $keyType = 'string';
    public $incrementing = false;

    public $fillable = [
        'id',
        'owner_id',
        'task_id',
        'name',
        'size',
        'url',
    ];

    protected $appends = ['extension','file_url'];

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
        });

    }

    public function getExtensionAttribute(): ?string
    {
        if (! $this->url) {
            return null;
        }

        return pathinfo($this->url, PATHINFO_EXTENSION);
    }

    public function getFileUrlAttribute()
    {
        return $this->url ? asset($this->url) : null;
    }
}
