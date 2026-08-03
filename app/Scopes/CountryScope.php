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
            $user = request()->user('sanctum'); // Use Sanctum guard to get the authenticated user
            
            // If user is authenticated and has a country_id, filter the model by it
            if ($user && $user->country_id) {
                // Check if the model has the 'country_id' column to avoid SQL errors
                if (\Illuminate\Support\Facades\Schema::hasColumn($model->getTable(), 'country_id')) {
                    $builder->where($model->getTable() . '.country_id', $user->country_id);
                }
            }
        }
    }
}
