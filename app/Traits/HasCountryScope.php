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
        if (static::appliesCountryGlobalScope()) {
            static::addGlobalScope(new CountryScope);
        }

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

    /**
     * Whether the country filter should run automatically on every query.
     *
     * Models that are loaded while authenticating (the User model) must return
     * false and filter by country explicitly instead — a global scope there
     * makes the sanctum guard query itself recursively, and would also hide
     * users of other countries from relations such as a post author.
     *
     * @return bool
     */
    protected static function appliesCountryGlobalScope()
    {
        return true;
    }
}
