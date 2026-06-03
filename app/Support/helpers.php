<?php

use App\Models\Currency;
use App\Services\CurrencyService;

if (! function_exists('store_money')) {
    function store_money(float|int|string|null $amount, Currency|string|null $to = null): string
    {
        return app(CurrencyService::class)->format((float) ($amount ?? 0), $to);
    }
}
