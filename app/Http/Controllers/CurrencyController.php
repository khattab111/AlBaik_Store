<?php

namespace App\Http\Controllers;

use App\Services\CurrencyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CurrencyController extends Controller
{
    public function __invoke(Request $request, string $currency): RedirectResponse
    {
        $currency = strtoupper($currency);

        abort_unless(app(CurrencyService::class)->findCurrency($currency), 404);

        session(['currency' => $currency]);

        return back()->withCookie(cookie()->forever('currency', $currency));
    }
}
