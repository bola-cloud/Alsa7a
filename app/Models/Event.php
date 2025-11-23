<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Translatable;

class Event extends Model
{
    use HasFactory, Translatable;

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

    // Append full URL for featured_image
    protected $appends = ['featured_image'];

    public function getFeaturedImageAttribute()
    {
        $val = $this->attributes['featured_image'] ?? null;
        if (! $val) return null;
        if (preg_match('#^https?://#i', $val)) return $val;
        return url(ltrim($val, '/'));
    }

    public function setFeaturedImageAttribute($value)
    {
        if (! $value) { $this->attributes['featured_image'] = $value; return; }
        if (! preg_match('#^https?://#i', $value)) { $this->attributes['featured_image'] = ltrim($value, '/'); return; }
        $appHost = parse_url(config('app.url') ?? url('/'), PHP_URL_HOST);
        $givenHost = parse_url($value, PHP_URL_HOST);
        if ($appHost && $givenHost && strtolower($appHost) === strtolower($givenHost)) {
            $this->attributes['featured_image'] = ltrim(parse_url($value, PHP_URL_PATH) ?: '', '/');
            return;
        }
        $this->attributes['featured_image'] = $value;
    }
}
