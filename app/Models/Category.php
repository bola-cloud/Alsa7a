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
        'name_en',
        'name_ar',
        'parent_category_id',
        'image',
        'description',
        'description_en',
        'description_ar',
        'is_service_provider',
        'requires_verification',
        'verification_requirements_en',
        'verification_requirements_ar',
        'verification_fields',
    ];

    protected $casts = [
        'is_service_provider' => 'boolean',
        'requires_verification' => 'boolean',
        'verification_fields' => 'array',
    ];

    /**
     * Return `image` as full URL for API consumers.
     * Eloquent will pass the raw stored value as $value.
     */
    public function getImageAttribute($value)
    {
        if (!$value) {
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
        if (!$value) {
            $this->attributes['image'] = $value;
            return;
        }

        // If already relative, normalize
        if (!preg_match('#^https?://#i', $value)) {
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

    public function parentCategory()
    {
        return $this->belongsTo(ParentCategory::class);
    }

    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Check if the category is protected from edit/delete
     */
    public function isProtected()
    {
        // Protected categories by name (English or Arabic)
        $protectedNames = ['Club', 'نادي'];
        return in_array($this->name_en, $protectedNames) || in_array($this->name_ar, $protectedNames);
    }
}
