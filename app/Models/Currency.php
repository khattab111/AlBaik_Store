<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'symbol', 'name', 'rate', 'is_default', 'status'];

    protected $casts = ['rate' => 'decimal:8', 'is_default' => 'boolean'];

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
        });
    }
}
