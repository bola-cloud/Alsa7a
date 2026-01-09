<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Translatable;

class Event extends Model
{
    use HasFactory, Translatable;

    protected $fillable = ['club_id', 'sport_id', 'title_en', 'title_ar', 'slug', 'description_en', 'description_ar', 'start_at', 'end_at', 'venue', 'price', 'capacity', 'tickets_sold', 'featured_image', 'is_featured', 'meta', 'ticket_types'];

    protected $casts = ['meta' => 'array', 'ticket_types' => 'array', 'is_featured' => 'boolean'];

    public function club()
    {
        return $this->belongsTo(Club::class);
    }

    public function sport()
    {
        return $this->belongsTo(Sport::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function media()
    {
        return $this->morphMany(Media::class, 'mediaable');
    }

    // Append full URL for featured_image
    protected $appends = ['featured_image'];

    public function getFeaturedImageAttribute()
    {
        $val = $this->attributes['featured_image'] ?? null;
        if (!$val) {
            return null;
        }
        if (preg_match('#^https?://#i', $val)) {
            return $val;
        }
        return asset($val);
    }

    public function setFeaturedImageAttribute($value)
    {
        $this->attributes['featured_image'] = $value;
    }
}
