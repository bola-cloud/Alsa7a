<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Translatable;

class ParentCategory extends Model
{
    use HasFactory, Translatable;

    protected $fillable = [
        'name_en',
        'name_ar',
        'image',
    ];

    public function categories()
    {
        return $this->hasMany(Category::class);
    }

    /**
     * Return `image` as full URL for API consumers.
     */
    public function getImageAttribute($value)
    {
        if (!$value) {
            return null;
        }
        if (preg_match('#^https?://#i', $value)) {
            return $value;
        }
        
        $path = ltrim($value, '/');
        if (!str_starts_with($path, 'storage/') && !str_starts_with($path, 'images-demo/')) {
            $path = 'storage/' . $path;
        }
        return url($path);
    }

    /**
     * Mutator: store relative path when possible
     */
    public function setImageAttribute($value)
    {
        if (!$value) {
            $this->attributes['image'] = $value;
            return;
        }

        if (!preg_match('#^https?://#i', $value)) {
            $this->attributes['image'] = ltrim($value, '/');
            return;
        }

        $appHost = parse_url(config('app.url') ?? url('/'), PHP_URL_HOST);
        $givenHost = parse_url($value, PHP_URL_HOST);

        if ($appHost && $givenHost && strtolower($appHost) === strtolower($givenHost)) {
            $path = parse_url($value, PHP_URL_PATH) ?: '';
            $this->attributes['image'] = ltrim($path, '/');
            return;
        }

        $this->attributes['image'] = $value;
    }
}
