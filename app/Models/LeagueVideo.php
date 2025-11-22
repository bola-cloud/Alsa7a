<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeagueVideo extends Model
{
    use HasFactory;

    protected $table = 'league_videos';

    protected $fillable = ['league_id', 'title', 'url', 'duration_seconds', 'meta'];

    protected $casts = ['meta' => 'array'];

    public function league()
    {
        return $this->belongsTo(League::class);
    }

    // Ensure url returns full absolute URL when serialized (video file location)
    protected $appends = ['url'];

    public function getUrlAttribute()
    {
        $val = $this->attributes['url'] ?? null;
        if (! $val) return null;
        if (preg_match('#^https?://#i', $val)) return $val;
        return url(ltrim($val, '/'));
    }

    public function setUrlAttribute($value)
    {
        if (! $value) { $this->attributes['url'] = $value; return; }
        if (! preg_match('#^https?://#i', $value)) { $this->attributes['url'] = ltrim($value, '/'); return; }
        $appHost = parse_url(config('app.url') ?? url('/'), PHP_URL_HOST);
        $givenHost = parse_url($value, PHP_URL_HOST);
        if ($appHost && $givenHost && strtolower($appHost) === strtolower($givenHost)) {
            $this->attributes['url'] = ltrim(parse_url($value, PHP_URL_PATH) ?: '', '/');
            return;
        }
        $this->attributes['url'] = $value;
    }
}
