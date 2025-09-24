<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Str;

class TaskFiles extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    public $fillable = [
        'task_id',
        'name',
        'size',
        'url',
    ];

    protected $appends = ['extension','file_url'];

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->id = Str::uuid();
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
