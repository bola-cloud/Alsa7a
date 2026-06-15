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
            // If the user's token has the view-only ability and the request is not GET
            if ($user->currentAccessToken() && $user->currentAccessToken()->can('view-only')) {
                if (!in_array($request->method(), ['GET', 'HEAD', 'OPTIONS'])) {
                    // Also allow logout
                    if (!$request->is('api/v1/auth/logout')) {
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
