<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Translatable;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory, Translatable;

    /**
     * Stable identity, never shown to anyone.
     *
     * Nine places across the backend and the app used to answer "is this the
     * club category?" by comparing display text, which meant re-wording a
     * category for users could silently take its permissions away. These are
     * what that question is asked with now.
     */
    public const SLUG_CLUB = 'club';
    public const SLUG_FOOTBALL_PLAYER = 'football_player';

    /** Categories the app depends on existing; they cannot be deleted. */
    public const PROTECTED_SLUGS = [self::SLUG_CLUB, self::SLUG_FOOTBALL_PLAYER];

    protected $fillable = [
        'name',
        'name_en',
        'name_ar',
        'display_name_en',
        'display_name_ar',
        'parent_category_id',
        'image',
        'description',
        'description_en',
        'description_ar',
        'is_service_provider',
        'is_marketplace',
        'requires_verification',
        'mandatory_service_verification',
        'verification_requirements_en',
        'verification_requirements_ar',
        'verification_fields',
    ];

    protected $casts = [
        'is_service_provider' => 'boolean',
        'is_marketplace' => 'boolean',
        'requires_verification' => 'boolean',
        'mandatory_service_verification' => 'boolean',
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
        
        $path = ltrim($value, '/');
        if (!str_starts_with($path, 'storage/') && !str_starts_with($path, 'images-demo/')) {
            $path = 'storage/' . $path;
        }
        return url($path);
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
     * A category created from the panel gets a slug of its own, so the column
     * stays meaningful instead of only covering the rows that existed when it
     * was introduced. `slug` is deliberately not fillable — it is derived once
     * here and then left alone, which is the whole point of it.
     */
    protected static function booted(): void
    {
        static::creating(function (self $category) {
            if (! empty($category->slug)) {
                return;
            }

            $base = Str::slug($category->name_en ?: $category->name ?: 'category', '_') ?: 'category';
            $slug = $base;
            $suffix = 2;

            while (static::where('slug', $slug)->exists()) {
                $slug = $base . '_' . $suffix++;
            }

            $category->slug = $slug;
        });
    }

    /**
     * Look a category up by what it *is*, not by what it is called.
     *
     *     Category::slug(Category::SLUG_CLUB)->first()
     */
    public function scopeSlug($query, string $slug)
    {
        return $query->where('slug', $slug);
    }

    /**
     * The wording to show a user, falling back to the plain name.
     *
     * Only categories that have been given a deliberate display name carry
     * one, so every other category keeps rendering exactly as before.
     */
    public function displayName(?string $locale = null): ?string
    {
        $locale = $locale ?: (app()->getLocale() ?: 'en');

        if ($locale === 'ar') {
            return $this->attributes['display_name_ar']
                ?? $this->attributes['display_name_en']
                ?? $this->attributes['name_ar']
                ?? $this->attributes['name'] ?? null;
        }

        return $this->attributes['display_name_en']
            ?? $this->attributes['display_name_ar']
            ?? $this->attributes['name_en']
            ?? $this->attributes['name'] ?? null;
    }

    /**
     * Check if the category is protected from edit/delete.
     *
     * Keyed on the slug now. The old name list stays as a fallback so a row
     * that somehow has no slug is still protected rather than silently
     * becoming deletable — this check must never get *less* strict.
     */
    public function isProtected()
    {
        if (in_array($this->attributes['slug'] ?? null, self::PROTECTED_SLUGS, true)) {
            return true;
        }

        $protectedNames = ['Club', 'نادي', 'Football player', 'لاعب كرة القدم'];
        return in_array($this->name_en, $protectedNames) || in_array($this->name_ar, $protectedNames);
    }
}
