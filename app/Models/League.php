<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Translatable;

class League extends Model
{
    use HasFactory, Translatable;

    protected $fillable = ['sport_id', 'name', 'slug', 'description', 'season', 'start_date', 'end_date', 'is_active', 'meta', 'image'];

    protected $casts = ['meta' => 'array', 'is_active' => 'boolean'];

    public function sport()
    {
        return $this->belongsTo(Sport::class);
    }

    public function teams()
    {
        return $this->belongsToMany(Team::class, 'league_team');
    }

    public function videos()
    {
        return $this->hasMany(LeagueVideo::class);
    }

    public function media()
    {
        return $this->morphMany(Media::class, 'mediaable');
    }

    /**
     * Return `image` as full URL for API consumers.
     */
    public function getImageAttribute($value)
    {
        if (! $value) {
            return null;
        }
        if (preg_match('#^https?://#i', $value)) {
            return $value;
        }
        return url(ltrim($value, '/'));
    }

    /**
     * Mutator: store relative path when possible, keep external URLs as-is
     */
    public function setImageAttribute($value)
    {
        if (! $value) {
            $this->attributes['image'] = $value;
            return;
        }

        // If already relative, normalize
        if (! preg_match('#^https?://#i', $value)) {
            $this->attributes['image'] = ltrim($value, '/');
            return;
        }

        // If absolute and same host as app.url, store only the path
        $appHost = parse_url(config('app.url') ?? url('/'), PHP_URL_HOST);
        $givenHost = parse_url($value, PHP_URL_HOST);

        if ($appHost && $givenHost && strtolower($appHost) === strtolower($givenHost)) {
            $path = parse_url($value, PHP_URL_PATH) ?: '';
            $this->attributes['image'] = ltrim($path, '/');
            return;
        }

        // External URL: store as-is
        $this->attributes['image'] = $value;
    }
}
