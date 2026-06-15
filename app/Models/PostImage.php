<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostImage extends Model
{
    protected $fillable = ['post_id', 'image_path'];
    protected $appends = ['url'];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function getUrlAttribute()
    {
        if (!$this->image_path) return null;
        if (preg_match('#^https?://#i', $this->image_path)) return $this->image_path;
        return asset('storage/' . $this->image_path);
    }
}
