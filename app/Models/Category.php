<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Translatable;

class Category extends Model
{
    use HasFactory, Translatable;

    protected $fillable = [
        'name',
        'image',
        'description',
    ];

    // Expose full image URL and keep compatibility key
    protected $appends = ['image_url'];

    /**
     * Return `image` as full URL for API consumers.
     * Eloquent will pass the raw stored value as $value.
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
     * Accessor for full image URL (alias) kept for other consumers.
     */
    public function getImageUrlAttribute()
    {
        // Reuse the image accessor logic by reading raw attribute and converting
        $raw = $this->attributes['image'] ?? null;
        if (! $raw) {
            return null;
        }
        if (preg_match('#^https?://#i', $raw)) {
            return $raw;
        }
        return url(ltrim($raw, '/'));
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

    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
