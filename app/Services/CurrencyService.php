<?php

namespace App\Services;

use App\Models\Currency;
use Illuminate\Support\Facades\Cache;

class CurrencyService
{
    public function defaultCurrency(): Currency
    {
        return Cache::remember('currencies.default', 3600, function () {
            return Currency::where('is_default', true)->where('status', true)->first()
                ?? Currency::where('status', true)->firstOrFail();
        });
    }

    public function convert(float $amount, Currency|string|null $to = null, Currency|string|null $from = null): float
    {
        $fromCurrency = $from instanceof Currency ? $from : $this->findCurrency($from) ?? $this->defaultCurrency();
        $toCurrency = $to instanceof Currency ? $to : $this->findCurrency($to) ?? $this->defaultCurrency();

        $baseAmount = $amount / max((float) $fromCurrency->rate, 0.000001);

        return round($baseAmount * (float) $toCurrency->rate, 2);
    }

    private function findCurrency(Currency|string|null $currency): ?Currency
    {
        if (! is_string($currency) || $currency === '') {
            return null;
        }

        return Cache::remember("currencies.{$currency}", 3600, fn () => Currency::where('code', $currency)->where('status', true)->first());
    }
}
