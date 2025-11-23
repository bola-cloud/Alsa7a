<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Translatable;

class Sport extends Model
{
    use HasFactory, Translatable;

    protected $fillable = ['name', 'slug', 'description', 'icon_url', 'active'];

    // Ensure icon_url returns full absolute URL in API responses
    protected $appends = ['icon_url'];

    public function getIconUrlAttribute()
    {
        $val = $this->attributes['icon_url'] ?? null;
        if (! $val) {
            return null;
        }
        if (preg_match('#^https?://#i', $val)) {
            return $val;
        }
        return url(ltrim($val, '/'));
    }

    public function setIconUrlAttribute($value)
    {
        if (! $value) {
            $this->attributes['icon_url'] = $value;
            return;
        }
        if (! preg_match('#^https?://#i', $value)) {
            $this->attributes['icon_url'] = ltrim($value, '/');
            return;
        }
        $appHost = parse_url(config('app.url') ?? url('/'), PHP_URL_HOST);
        $givenHost = parse_url($value, PHP_URL_HOST);
        if ($appHost && $givenHost && strtolower($appHost) === strtolower($givenHost)) {
            $path = parse_url($value, PHP_URL_PATH) ?: '';
            $this->attributes['icon_url'] = ltrim($path, '/');
            return;
        }
        $this->attributes['icon_url'] = $value;
    }

    public function clubs()
    {
        return $this->belongsToMany(Club::class, 'club_sport');
    }

    public function teams()
    {
        return $this->hasMany(Team::class);
    }

    public function leagues()
    {
        return $this->hasMany(League::class);
    }
}
