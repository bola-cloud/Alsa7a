<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    use HasFactory;

    protected $fillable = ['mediaable_type', 'mediaable_id', 'url', 'type', 'title', 'duration_seconds', 'meta', 'is_featured'];

    protected $casts = ['meta' => 'array', 'is_featured' => 'boolean'];

    public function mediaable()
    {
        return $this->morphTo();
    }
}
