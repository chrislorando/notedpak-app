<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskFiles extends Model
{
    public $fillable = [
        'task_id',
        'name',
        'size',
        'url',
    ];

    protected $appends = ['extension','file_url'];

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
