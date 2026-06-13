<?php

namespace App\Services\Providers\Services;

use App\Models\ElectronicServiceProvider;

class PricingEngine
{
    public function calculate(float $cost, string $profitType, float $profitValue): float
    {
        $price = $profitType === ElectronicServiceProvider::PROFIT_FIXED
            ? $cost + $profitValue
            : $cost + ($cost * ($profitValue / 100));

        return round(max(0, $price), 2);
    }
}
