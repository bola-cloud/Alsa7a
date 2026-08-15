<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Keeps a user's own content in the same country as the user.
 *
 * All content created before the country feature carries country_id = NULL.
 * The moment a user picks (or changes) their country, everything they own
 * moves with them, so their profile and listings stop looking empty to
 * people browsing that country.
 *
 * Plain query-builder updates on purpose: no model events, no global scopes,
 * and updated_at is left untouched so feed ordering does not jump around.
 */
class UserCountrySync
{
    /**
     * Tables owned directly by a user, keyed by their owner column.
     *
     * @var array<string, string>
     */
    protected const OWNED_BY_USER = [
        'posts' => 'user_id',
        'stories' => 'user_id',
        'community_posts' => 'user_id',
        'user_events' => 'user_id',
        'services' => 'provider_id',
        'clubs' => 'user_id',
    ];

    /**
     * Tables that belong to a club, and therefore to whoever owns that club.
     *
     * @var array<string, string>
     */
    protected const OWNED_BY_CLUB = [
        'events' => 'club_id',
        'market_requests' => 'club_id',
        'services' => 'club_id',
    ];

    /**
     * Move every record this user owns to the user's current country.
     *
     * @param  \App\Models\User  $user
     * @return array<string, int>  rows updated per table
     */
    public static function sync(User $user)
    {
        $countryId = $user->country_id;
        $updated = [];

        foreach (self::OWNED_BY_USER as $table => $column) {
            $updated[$table] = self::move($table, fn ($q) => $q->where($column, $user->id), $countryId);
        }

        $clubIds = DB::table('clubs')->where('user_id', $user->id)->pluck('id');

        if ($clubIds->isNotEmpty()) {
            foreach (self::OWNED_BY_CLUB as $table => $column) {
                $updated[$table] = ($updated[$table] ?? 0)
                    + self::move($table, fn ($q) => $q->whereIn($column, $clubIds), $countryId);
            }
        }

        if (array_sum($updated) > 0) {
            Log::info('UserCountrySync', [
                'user_id' => $user->id,
                'country_id' => $countryId,
                'updated' => array_filter($updated),
            ]);
        }

        return $updated;
    }

    /**
     * Apply the country change to one table, skipping rows that already
     * carry the target value so the update stays cheap on repeat calls.
     *
     * @param  string  $table
     * @param  callable  $owner  narrows the query to this user's rows
     * @param  int|null  $countryId
     * @return int
     */
    protected static function move($table, callable $owner, $countryId)
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'country_id')) {
            return 0;
        }

        $query = DB::table($table);
        $owner($query);

        $countryId === null
            ? $query->whereNotNull('country_id')
            : $query->where(function ($q) use ($countryId) {
                $q->where('country_id', '!=', $countryId)->orWhereNull('country_id');
            });

        return $query->update(['country_id' => $countryId]);
    }
}
