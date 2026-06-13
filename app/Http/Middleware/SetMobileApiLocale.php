<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SetMobileApiLocale
{
    public function handle(Request $request, Closure $next): mixed
    {
        $locale = strtolower(substr((string) $request->header('Accept-Language', config('app.locale', 'ar')), 0, 2));

        if (! in_array($locale, ['ar', 'en'], true)) {
            $locale = config('app.fallback_locale', 'en');
        }

        app()->setLocale($locale);

        if ($request->bearerToken() && ! $request->user()) {
            $user = Auth::guard('sanctum')->user();

            if ($user) {
                $request->setUserResolver(fn () => $user);
            }
        }

        return $next($request);
    }
}
