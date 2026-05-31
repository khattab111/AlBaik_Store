<?php

namespace Database\Seeders;

use App\Models\Currency;
use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder
{
    public function run(): void
    {
        $currencies = [
            [
                'code' => 'USD',
                'name' => 'US Dollar',
                'symbol' => '$',
                'rate' => 1,
                'is_default' => true,
                'status' => true,
            ],
            [
                'code' => 'TRY',
                'name' => 'Turkish Lira',
                'symbol' => '₺',
                'rate' => 32,
                'is_default' => false,
                'status' => true,
            ],
            [
                'code' => 'SYP',
                'name' => 'Syrian Pound',
                'symbol' => 'ل.س',
                'rate' => 14000,
                'is_default' => false,
                'status' => true,
            ],
        ];

        foreach ($currencies as $currency) {
            Currency::updateOrCreate(
                ['code' => $currency['code']],
                $currency
            );
        }
    }
}
