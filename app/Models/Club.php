<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasCountryScope;
use App\Traits\Translatable;

use App\Traits\HasSlug;

class Club extends Model
{
    use HasFactory, HasCountryScope, Translatable, HasSlug;

    protected static function booted(): void
    {
        static::observe(\App\Observers\ClubObserver::class);
    }

    protected $fillable = [
        'name',
        'name_en',
        'name_ar',
        'slug',
        'description',
        'description_en',
        'description_ar',
        'logo_url',
        'banner_url',
        'city',
        'country',
        'founded_year',
        'website',
        'rating',
        'is_featured',
        'is_featured',
        'meta',
        'user_id', // Owner
    ];

    protected $casts = [
        'meta' => 'array',
        'is_featured' => 'boolean',
    ];

    public function sports()
    {
        return $this->belongsToMany(Sport::class, 'club_sport');
    }

    public function leagues()
    {
        return $this->belongsToMany(League::class, 'club_league');
    }

    public function teams()
    {
        return $this->hasMany(Team::class);
    }

    public function players()
    {
        return $this->hasMany(Player::class);
    }

    public function events()
    {
        return $this->hasMany(Event::class);
    }

    public function media()
    {
        return $this->morphMany(Media::class, 'mediaable');
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function requests()
    {
        return $this->hasMany(ClubRequest::class);
    }

    public function services()
    {
        return $this->hasMany(Service::class);
    }

    // Return full URLs for logo and banner
    protected $appends = ['logo_url', 'banner_url'];

    public function getLogoUrlAttribute()
    {
        $val = $this->attributes['logo_url'] ?? null;
        if (!$val)
            return null;
        if (preg_match('#^https?://#i', $val))
            return $val;
        return asset(ltrim($val, '/'));
    }

    public function setLogoUrlAttribute($value)
    {
        if (!$value) {
            $this->attributes['logo_url'] = $value;
            return;
        }
        if (!preg_match('#^https?://#i', $value)) {
            $this->attributes['logo_url'] = ltrim($value, '/');
            return;
        }
        $appHost = parse_url(config('app.url') ?? url('/'), PHP_URL_HOST);
        $givenHost = parse_url($value, PHP_URL_HOST);
        if ($appHost && $givenHost && strtolower($appHost) === strtolower($givenHost)) {
            $this->attributes['logo_url'] = ltrim(parse_url($value, PHP_URL_PATH) ?: '', '/');
            return;
        }
        $this->attributes['logo_url'] = $value;
    }

    public function getBannerUrlAttribute()
    {
        $val = $this->attributes['banner_url'] ?? null;
        if (!$val)
            return null;
        if (preg_match('#^https?://#i', $val))
            return $val;
        return asset(ltrim($val, '/'));
    }

    public function setBannerUrlAttribute($value)
    {
        if (!$value) {
            $this->attributes['banner_url'] = $value;
            return;
        }
        if (!preg_match('#^https?://#i', $value)) {
            $this->attributes['banner_url'] = ltrim($value, '/');
            return;
        }
        $appHost = parse_url(config('app.url') ?? url('/'), PHP_URL_HOST);
        $givenHost = parse_url($value, PHP_URL_HOST);
        if ($appHost && $givenHost && strtolower($appHost) === strtolower($givenHost)) {
            $this->attributes['banner_url'] = ltrim(parse_url($value, PHP_URL_PATH) ?: '', '/');
            return;
        }
        $this->attributes['banner_url'] = $value;
    }
}
