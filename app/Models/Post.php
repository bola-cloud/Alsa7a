<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'content',
        'image',
        'video_thumbnail',
        'is_hidden',
        'type'
    ];

    protected $casts = [
        'is_hidden' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function likes()
    {
        return $this->morphMany(Like::class, 'likeable');
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function views()
    {
        return $this->hasMany(PostView::class);
    }

    public function getImageAttribute($value)
    {
        if (!$value)
            return null;
        if (preg_match('#^https?://#i', $value))
            return $value;
        return asset('storage/' . $value);
    }

    public function getVideoThumbnailAttribute($value)
    {
        if (!$value)
            return null;
        if (preg_match('#^https?://#i', $value))
            return $value;
        return asset($value); // Thumbnails already include 'storage/' prefix in DB
    }
}
