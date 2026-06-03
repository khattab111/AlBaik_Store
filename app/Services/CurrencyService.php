<?php

namespace App\Services;

use App\Models\Currency;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class CurrencyService
{
    public function activeCurrencies(): Collection
    {
        return Cache::remember('currencies.active', 3600, fn () => Currency::where('status', true)->orderByDesc('is_default')->orderBy('code')->get());
    }

    public function defaultCurrency(): Currency
    {
        return Cache::remember('currencies.default', 3600, function () {
            return Currency::where('is_default', true)->where('status', true)->first()
                ?? Currency::where('status', true)->firstOrFail();
        });
    }

    public function currentCurrency(): Currency
    {
        $code = session('currency', request()->cookie('currency'));

        return $this->findCurrency(is_string($code) ? strtoupper($code) : null) ?? $this->defaultCurrency();
    }

    public function convert(float $amount, Currency|string|null $to = null, Currency|string|null $from = null): float
    {
        $fromCurrency = $from instanceof Currency ? $from : $this->findCurrency($from) ?? $this->defaultCurrency();
        $toCurrency = $to instanceof Currency ? $to : $this->findCurrency($to) ?? $this->defaultCurrency();

        $baseAmount = $amount / max((float) $fromCurrency->rate, 0.000001);

        return round($baseAmount * (float) $toCurrency->rate, 2);
    }

    public function format(float $amount, Currency|string|null $to = null, Currency|string|null $from = null): string
    {
        $currency = $to instanceof Currency ? $to : $this->findCurrency($to) ?? $this->currentCurrency();
        $converted = $this->convert($amount, $currency, $from);
        $decimals = (float) $currency->rate >= 1000 ? 0 : 2;

        return trim($currency->symbol.' '.number_format($converted, $decimals).' '.$currency->code);
    }

    public function forgetCache(): void
    {
        Cache::forget('currencies.default');
        Cache::forget('currencies.active');

        $this->activeCurrencies()->each(fn (Currency $currency) => Cache::forget("currencies.{$currency->code}"));
    }

    public function findCurrency(Currency|string|null $currency): ?Currency
    {
        if (! is_string($currency) || $currency === '') {
            return null;
        }

        $code = strtoupper($currency);

        return Cache::remember("currencies.{$code}", 3600, fn () => Currency::where('code', $code)->where('status', true)->first());
    }
}
