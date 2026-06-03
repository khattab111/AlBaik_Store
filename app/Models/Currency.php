<?php

namespace App\Models;

use App\Models\Concerns\HasStoreTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Currency extends Model
{
    use HasFactory, HasStoreTranslations;

    public array $translatable = ['name'];

    protected $fillable = ['code', 'symbol', 'name', 'rate', 'is_default', 'status'];

    protected $casts = ['rate' => 'decimal:8', 'is_default' => 'boolean', 'status' => 'boolean'];

    protected static function booted(): void
    {
        static::saving(function (Currency $currency): void {
            if ($currency->is_default) {
                $currency->rate = 1;
                $currency->status = true;
            }
        });

        static::saved(function (Currency $currency): void {
            if ($currency->is_default) {
                static::whereKeyNot($currency->getKey())->update(['is_default' => false]);
            }

            static::forgetCurrencyCache($currency);
        });

        static::deleted(fn (Currency $currency) => static::forgetCurrencyCache($currency));
    }

    private static function forgetCurrencyCache(Currency $currency): void
    {
        Cache::forget('currencies.default');
        Cache::forget('currencies.active');
        Cache::forget("currencies.{$currency->code}");
    }
}
