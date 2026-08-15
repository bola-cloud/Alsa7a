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
     * Turn the country filter off for a query.
     *
     * The country rules are about *discovery* — the feed, search, listings.
     * When the caller asks for one specific thing by id (a shared post link,
     * a profile's posts, a club page), country must not hide it: otherwise a
     * deep link opens an empty screen, and every follower in another country
     * suddenly sees a blank profile the moment its owner picks a country.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDirectAccess($query)
    {
        return $query->withoutGlobalScope(CountryScope::class);
    }

    /**
     * Country filter for feed-style listings that also carry followed people.
     *
     * A listing like the feed, reels or the community wall mixes two things:
     * discovery (strangers — must stay inside the viewer's country) and the
     * people the viewer deliberately followed. Following works across
     * countries, so a followed author's content must come through no matter
     * where they are, exactly like their stories do.
     *
     * Passing a null country (V1, or a guest with no header) is a no-op.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  mixed  $countryId
     * @param  array<int>  $followingIds  authors the viewer follows (plus self)
     * @param  string  $ownerColumn
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCountryVisibleOrFollowed($query, $countryId, array $followingIds = [], $ownerColumn = 'user_id')
    {
        // The global scope would apply the plain country rule on top of this
        // one and cancel out the "followed" part, so drop it first.
        $query->withoutGlobalScope(CountryScope::class);

        if (! $countryId) {
            return $query;
        }

        $table = $this->getTable();

        return $query->where(function ($q) use ($table, $countryId, $followingIds, $ownerColumn) {
            $q->where($table . '.country_id', $countryId)
              ->orWhereNull($table . '.country_id');

            if (! empty($followingIds)) {
                $q->orWhereIn($table . '.' . $ownerColumn, $followingIds);
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
