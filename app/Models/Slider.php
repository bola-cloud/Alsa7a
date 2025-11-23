<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Translatable;

class Slider extends Model
{
    use HasFactory, Translatable;

    protected $table = 'sliders';

    protected $fillable = ['title', 'image'];

    // Append computed full image URL
    protected $appends = ['image_url'];

    // Hide the raw stored image path from API responses; clients should use `image_url`.
    protected $hidden = ['image'];

    /**
     * Accessor for full image URL
     */
    public function getImageUrlAttribute()
    {
        $val = $this->attributes['image'] ?? null;
        if (! $val) {
            return null;
        }
        if (preg_match('#^https?://#i', $val)) {
            return $val;
        }
        return url(ltrim($val, '/'));
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
