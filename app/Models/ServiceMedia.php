<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceMedia extends Model
{
    use HasFactory;

    protected $table = 'service_media';

    protected $fillable = ['service_id', 'url', 'type', 'title', 'meta'];

    protected $casts = ['meta' => 'array'];

    // Append computed full URL to JSON
    protected $appends = ['full_url'];

    public function getFullUrlAttribute()
    {
        $val = $this->attributes['url'] ?? null;
        if (! $val) {
            return null;
        }
        if (preg_match('#^https?://#i', $val)) {
            return $val;
        }
        return url(ltrim($val, '/'));
    }

    public function setUrlAttribute($value)
    {
        if (! $value) {
            $this->attributes['url'] = $value;
            return;
        }
        if (! preg_match('#^https?://#i', $value)) {
            $this->attributes['url'] = ltrim($value, '/');
            return;
        }
        $appHost = parse_url(config('app.url') ?? url('/'), PHP_URL_HOST);
        $givenHost = parse_url($value, PHP_URL_HOST);
        if ($appHost && $givenHost && strtolower($appHost) === strtolower($givenHost)) {
            $path = parse_url($value, PHP_URL_PATH) ?: '';
            $this->attributes['url'] = ltrim($path, '/');
            return;
        }
        $this->attributes['url'] = $value;
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
