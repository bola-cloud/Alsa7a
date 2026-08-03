<?php

namespace App\Traits;

use App\Scopes\CountryScope;

trait HasCountryScope
{
    /**
     * Boot the trait and apply the global scope.
     *
     * @return void
     */
    protected static function bootHasCountryScope()
    {
        static::addGlobalScope(new CountryScope);

        static::creating(function ($model) {
            // Automatically assign the authenticated user's country_id when creating a new record
            if (auth('sanctum')->check()) {
                $user = auth('sanctum')->user();
                if ($user && $user->country_id && empty($model->country_id)) {
                    if (\Illuminate\Support\Facades\Schema::hasColumn($model->getTable(), 'country_id')) {
                        $model->country_id = $user->country_id;
                    }
                }
            }
        });
    }
}
