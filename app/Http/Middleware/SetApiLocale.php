<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetApiLocale
{
    /**
     * Handle an incoming request, set application locale from header `X-Lang` or `lang`.
     */
    public function handle(Request $request, Closure $next)
    {
        $lang = $request->header('X-Lang') ?: $request->header('lang') ?: $request->getPreferredLanguage(['en','ar']);

        if (! in_array($lang, ['en','ar'])) {
            $lang = 'en';
        }

        app()->setLocale($lang);

        return $next($request);
    }
}
