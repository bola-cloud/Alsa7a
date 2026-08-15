<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BlockParentAction
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($user = $request->user('sanctum')) {
            // In Sanctum, can() returns true if the token has the '*' ability. 
            // So we must check if the abilities array strictly contains 'view-only' 
            // and does NOT contain '*' to identify a true parent token.
            $token = $user->currentAccessToken();
            
            if ($token && in_array('view-only', $token->abilities ?? []) && !in_array('*', $token->abilities ?? [])) {
                if (!in_array($request->method(), ['GET', 'HEAD', 'OPTIONS'])) {
                    
                    // Allow harmless POST requests for parent accounts.
                    // The version segment is a wildcard: V2 is a superset of V1,
                    // so the same calls arrive on api/v2/* from the new app.
                    $allowedPaths = [
                        'api/*/auth/logout',
                        'api/*/notifications/read-all',
                        'api/*/notifications/*/read',
                        'api/*/posts/*/view',
                        'api/*/users/onesignal-subscription',
                    ];

                    if (!$request->is($allowedPaths)) {
                        return response()->json([
                            'status' => false,
                            'message' => 'This action is not allowed for parent view-only accounts.'
                        ], 403);
                    }
                }
            }
        }

        return $next($request);
    }
}
