<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = session('locale', config('app.locale', 'ar'));

        if (! in_array($locale, ['ar', 'en'], true)) {
            $locale = 'ar';
        }

        App::setLocale($locale);
        View::share('isRtl', $locale === 'ar');

        return $next($request);
    }
}
