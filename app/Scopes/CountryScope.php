<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class CountryScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        // Only apply this scope if the request is coming from the V2 API
        // This ensures absolute backward compatibility with V1.
        if (request() && request()->is('api/v2/*')) {
            $countryId = request()->header('Country-Id');

            // Fallback to authenticated user's country if header is not present
            if (!$countryId) {
                $user = request()->user('sanctum');
                if ($user && $user->country_id) {
                    $countryId = $user->country_id;
                }
            }
            
            if ($countryId) {
                // Check if the model has the 'country_id' column to avoid SQL errors
                if (\Illuminate\Support\Facades\Schema::hasColumn($model->getTable(), 'country_id')) {
                    $builder->where(function ($q) use ($model, $countryId) {
                        $q->where($model->getTable() . '.country_id', $countryId)
                          ->orWhereNull($model->getTable() . '.country_id');
                    });
                }
            }
        }

        // Admin Panel Global Filter
        if (request() && (request()->is('*/admin/*') || request()->is('admin/*') || request()->is('*/admin') || request()->is('admin'))) {
            $adminCountryId = session('admin_country_id');
            
            if ($adminCountryId && $adminCountryId !== 'all') {
                if (\Illuminate\Support\Facades\Schema::hasColumn($model->getTable(), 'country_id')) {
                    $builder->where(function ($q) use ($model, $adminCountryId) {
                        $q->where($model->getTable() . '.country_id', $adminCountryId)
                          ->orWhereNull($model->getTable() . '.country_id');
                    });
                }
            }
        }
    }
}
