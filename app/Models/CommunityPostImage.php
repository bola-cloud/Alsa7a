<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommunityPostImage extends Model
{
    protected $fillable = ['community_post_id', 'image_path'];
    protected $appends = ['url'];

    public function post()
    {
        return $this->belongsTo(CommunityPost::class, 'community_post_id');
    }

    public function getUrlAttribute()
    {
        if (!$this->image_path) return null;
        if (preg_match('#^https?://#i', $this->image_path)) return $this->image_path;
        return asset('storage/' . $this->image_path);
    }
}
