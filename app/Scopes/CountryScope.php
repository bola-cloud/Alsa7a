<?php

namespace App\Scopes;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Schema;

class CountryScope implements Scope
{
    /**
     * True while we are inside a `request()->user()` call made by this scope.
     *
     * Sanctum resolves the token owner with an Eloquent query, so without this
     * guard the scope would call the guard, the guard would run a query, the
     * query would call the scope again... until PHP dies with
     * "Maximum call stack size reached. Infinite recursion?" (HTTP 500).
     *
     * @var bool
     */
    protected static $resolvingUser = false;

    /**
     * Cached `table => has country_id column` lookups (one query each otherwise).
     *
     * @var array<string, bool>
     */
    protected static $hasCountryColumn = [];

    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        $request = request();

        if (! $request) {
            return;
        }

        // Only apply this scope if the request is coming from the V2 API
        // This ensures absolute backward compatibility with V1.
        //
        // Authenticatable models are never filtered here: the sanctum guard
        // loads the token owner through this very query, and relations such as
        // a post author or a followers list must stay visible across countries.
        if ($request->is('api/v2/*') && ! $model instanceof Authenticatable) {
            $this->filterBy($builder, $model, $this->resolveApiCountryId($request));
        }

        // Admin Panel Global Filter.
        //
        // Strict on purpose — unlike the API branch above, the admin switcher
        // is meant to isolate one country's data for management, not blend in
        // every unassigned (NULL) record by default. That blend is exactly
        // what made picking a country in the panel look like it did nothing:
        // almost all existing rows are still NULL, so an OR-NULL filter let
        // them all through regardless of which country was selected.
        // Selecting "بدون دولة" (session value 'none') shows only the
        // unassigned rows themselves.
        if ($request->is('*/admin/*') || $request->is('admin/*') || $request->is('*/admin') || $request->is('admin')) {
            $adminCountryId = session('admin_country_id');

            if ($adminCountryId && $adminCountryId !== 'all') {
                $this->filterBy($builder, $model, $adminCountryId, strict: true);
            }
        }
    }

    /**
     * Country requested by the mobile client, falling back to the logged-in user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    protected function resolveApiCountryId($request)
    {
        $countryId = $request->header('Country-Id');

        if ($countryId) {
            return $countryId;
        }

        // Fallback to authenticated user's country if header is not present.
        // Skipped while the guard itself is running, otherwise we recurse.
        if (static::$resolvingUser) {
            return null;
        }

        static::$resolvingUser = true;

        try {
            $user = $request->user('sanctum');
        } finally {
            static::$resolvingUser = false;
        }

        return $user && $user->country_id ? $user->country_id : null;
    }

    /**
     * Restrict the query to one country.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  mixed  $countryId  a country id, or the sentinel 'none' for
     *                            unassigned (NULL) rows
     * @param  bool  $strict  false (API default): also include NULL rows, so
     *                        legacy content stays visible everywhere until it
     *                        is backfilled. true (admin): isolate exactly the
     *                        requested country, no NULL blending.
     * @return void
     */
    protected function filterBy(Builder $builder, Model $model, $countryId, bool $strict = false)
    {
        // Check if the model has the 'country_id' column to avoid SQL errors
        if (! $this->tableHasCountryColumn($model->getTable())) {
            return;
        }

        $table = $model->getTable();

        if ($countryId === 'none') {
            $builder->whereNull($table . '.country_id');
            return;
        }

        if (! $countryId) {
            return;
        }

        if ($strict) {
            $builder->where($table . '.country_id', $countryId);
            return;
        }

        $builder->where(function ($q) use ($table, $countryId) {
            $q->where($table . '.country_id', $countryId)
              ->orWhereNull($table . '.country_id');
        });
    }

    /**
     * @param  string  $table
     * @return bool
     */
    protected function tableHasCountryColumn($table)
    {
        if (! array_key_exists($table, static::$hasCountryColumn)) {
            static::$hasCountryColumn[$table] = Schema::hasColumn($table, 'country_id');
        }

        return static::$hasCountryColumn[$table];
    }
}
