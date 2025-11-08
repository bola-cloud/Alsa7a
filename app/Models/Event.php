<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = ['club_id', 'sport_id', 'title', 'slug', 'description', 'start_at', 'end_at', 'venue', 'price', 'capacity', 'tickets_sold', 'featured_image', 'is_featured', 'meta'];

    protected $casts = ['meta' => 'array', 'is_featured' => 'boolean'];

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
}
