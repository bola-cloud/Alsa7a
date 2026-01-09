<?php

namespace App\Traits;

trait Translatable
{
    /**
     * Return translated attribute based on app locale.
     * Falls back to the english variant if not available.
     */
    public function getAttribute($key)
    {
        $locale = app()->getLocale() ?? 'en';

        // if attribute has localized variants like name_en / name_ar
        $localizedKey = "{$key}_{$locale}";

        if (array_key_exists($localizedKey, $this->attributes) && !is_null($this->attributes[$localizedKey]) && $this->attributes[$localizedKey] !== '') {
            return $this->attributes[$localizedKey];
        }

        // fallback to english
        $fallback = "{$key}_en";
        if (array_key_exists($fallback, $this->attributes)) {
            return $this->attributes[$fallback];
        }

        return parent::getAttribute($key);
    }

    /**
     * Get the translation for a specific attribute and locale.
     * 
     * @param string $key
     * @param string $locale
     * @return mixed
     */
    public function getTranslation($key, $locale)
    {
        $localizedKey = "{$key}_{$locale}";
        return $this->attributes[$localizedKey] ?? null;
    }
}
