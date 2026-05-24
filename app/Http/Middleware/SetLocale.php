<?php

namespace App\Http\Middleware;

use App\Services\LanguageService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $languages = app(LanguageService::class)->active();
        $supportedLocales = array_keys($languages);
        $fallbackLocale = config('locales.fallback', config('app.fallback_locale', 'en'));
        $defaultLocale = app(LanguageService::class)->defaultCode();
        $locale = session('locale', $request->cookie('locale', $defaultLocale));

        if (! in_array($locale, $supportedLocales, true)) {
            $locale = in_array($fallbackLocale, $supportedLocales, true)
                ? $fallbackLocale
                : ($supportedLocales[0] ?? 'en');
        }

        App::setLocale($locale);
        App::setFallbackLocale($fallbackLocale);

        $localeConfig = $languages[$locale] ?? [];
        $direction = $localeConfig['direction'] ?? 'ltr';

        View::share([
            'currentLocale' => $locale,
            'supportedLocales' => $languages,
            'textDirection' => $direction,
            'isRtl' => $direction === 'rtl',
        ]);

        return $next($request);
    }
}
