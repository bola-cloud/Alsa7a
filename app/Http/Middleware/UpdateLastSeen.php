<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class UpdateLastSeen
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // This runs on the 'api' middleware group, where every request from
        // the mobile app is authenticated via the 'sanctum' guard, not the
        // default 'web' (session) guard. auth()->check()/auth()->user() read
        // the default guard, so they were always false here and last_seen_at
        // never got set for a single one of the app's users.
        $user = $request->user('sanctum');

        if ($user) {
            // Only update if last_seen_at is null or older than 5 minutes to avoid excessive DB writes
            if (!$user->last_seen_at || now()->diffInMinutes($user->last_seen_at) >= 5) {
                // A plain query update: no model events, and it deliberately
                // does not bump updated_at (would otherwise reorder anything
                // sorted by "recently updated" on every single request).
                // DB::table (plain query builder), not $user->newQuery() —
                // an Eloquent builder still auto-touches updated_at even when
                // it's not in the update array.
                DB::table('users')->where('id', $user->id)->update(['last_seen_at' => now()]);
            }
        }

        return $next($request);
    }
}
