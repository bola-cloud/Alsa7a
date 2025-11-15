<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    use HasFactory;

    protected $fillable = ['mediaable_type', 'mediaable_id', 'url', 'type', 'title', 'duration_seconds', 'meta', 'is_featured'];

    protected $casts = ['meta' => 'array', 'is_featured' => 'boolean'];

    // Append computed full URL to JSON representation
    protected $appends = ['full_url'];

    /**
     * Accessor: return the full URL for the stored path or return external URL as-is.
     */
    public function getFullUrlAttribute()
    {
        $val = $this->attributes['url'] ?? null;
        if (! $val) {
            return null;
        }

        // If already an absolute URL, return it
        if (preg_match('#^https?://#i', $val)) {
            return $val;
        }

        // Otherwise treat as a project-relative path and build absolute URL
        return url(ltrim($val, '/'));
    }

    /**
     * Mutator: when setting url, try to store a project-relative path when the value
     * points to the same app host. If it's an external URL we keep it as-is.
     */
    public function setUrlAttribute($value)
    {
        if (! $value) {
            $this->attributes['url'] = $value;
            return;
        }

        // If value already looks like a relative path, normalize it
        if (! preg_match('#^https?://#i', $value)) {
            $this->attributes['url'] = ltrim($value, '/');
            return;
        }

        // It's an absolute URL. If it's the same host as app.url, store only the path.
        $appHost = parse_url(config('app.url') ?? url('/'), PHP_URL_HOST);
        $givenHost = parse_url($value, PHP_URL_HOST);

        if ($appHost && $givenHost && strtolower($appHost) === strtolower($givenHost)) {
            $path = parse_url($value, PHP_URL_PATH) ?: '';
            $this->attributes['url'] = ltrim($path, '/');
            return;
        }

        // External host: keep the full URL so the API can still return it.
        $this->attributes['url'] = $value;
    }

    public function mediaable()
    {
        return $this->morphTo();
    }
}
