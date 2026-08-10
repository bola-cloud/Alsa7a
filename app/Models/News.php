<?php

namespace App\Models;

use App\Traits\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasCountryScope;

class News extends Model
{
    use HasFactory, Translatable, HasCountryScope;

    protected $fillable = [
        'title_en',
        'title_ar',
        'content_en',
        'content_ar',
        'image',
        'sport_id',
        'is_active',
        'country_id'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function sport()
    {
        return $this->belongsTo(Sport::class);
    }

    public function media()
    {
        return $this->morphMany(Media::class, 'mediaable');
    }

    public function getImagesAttribute()
    {
        return $this->media()->where('type', 'image')->get();
    }

    public function getVideoAttribute()
    {
        return $this->media()->where('type', 'video')->first();
    }

    public function getFeaturedImageAttribute()
    {
        if (!$this->image) {
            return null;
        }
        if (preg_match('#^https?://#i', $this->image)) {
            return $this->image;
        }
        return asset($this->image);
    }

    public function likes()
    {
        return $this->morphMany(Like::class, 'likeable');
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }
}
