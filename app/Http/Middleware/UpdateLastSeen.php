<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
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
        if (auth()->check()) {
            $user = auth()->user();
            
            // Only update if last_seen_at is null or older than 5 minutes to avoid excessive DB writes
            if (!$user->last_seen_at || now()->diffInMinutes($user->last_seen_at) >= 5) {
                // Use a direct DB query to avoid triggering model events (like updated_at) if preferred, 
                // but updating updated_at is fine here too.
                $user->update(['last_seen_at' => now()]);
            }
        }

        return $next($request);
    }
}
