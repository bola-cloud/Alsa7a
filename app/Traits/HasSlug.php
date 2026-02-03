<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait HasSlug
{
    protected static function bootHasSlug()
    {
        static::creating(function ($model) {
            if (!$model->slug) {
                $model->slug = $model->generateUniqueSlug();
            }
        });

        static::updating(function ($model) {
            $source = $model->getSlugSource();
            if ($model->isDirty($source) && !$model->isDirty('slug')) {
                $model->slug = $model->generateUniqueSlug();
            }
        });
    }

    protected function getSlugSource()
    {
        return $this->slugSource ?? 'name';
    }

    public function generateUniqueSlug()
    {
        $sourceField = $this->getSlugSource();
        $sourceValue = $this->{$sourceField};

        // Handle cases where the source might be a translatable attribute
        if (!$sourceValue && method_exists($this, 'getAttribute')) {
            $sourceValue = $this->getAttribute($sourceField);
        }

        if (!$sourceValue) {
            $sourceValue = $this->name ?? $this->title ?? 'item';
        }

        $slug = Str::slug($sourceValue);

        if (!$slug) {
            // Fallback for non-latin characters if Str::slug yields empty string
            $slug = 'item-' . Str::random(5);
        }

        $originalSlug = $slug;
        $count = 1;

        while (static::where('slug', $slug)->where('id', '!=', $this->id)->exists()) {
            $slug = "{$originalSlug}-{$count}";
            $count++;
        }

        return $slug;
    }
}
