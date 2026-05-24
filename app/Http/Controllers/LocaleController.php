<?php

namespace App\Http\Controllers;

use App\Services\LanguageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    public function __invoke(Request $request, string $locale): RedirectResponse
    {
        abort_unless(array_key_exists($locale, app(LanguageService::class)->active()), 404);

        session(['locale' => $locale]);
        app()->setLocale($locale);

        return back()->withCookie(cookie()->forever('locale', $locale));
    }
}
