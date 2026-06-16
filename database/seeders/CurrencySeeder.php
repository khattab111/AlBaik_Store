<?php

namespace Database\Seeders;

use App\Models\Currency;
use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder
{
    use SeedsTranslations;

    public function run(): void
    {
        $currencies = [
            [
                'code' => 'USD',
                'name' => $this->tr('US Dollar', 'الدولار الأمريكي'),
                'symbol' => '$',
                'rate' => 1,
                'is_default' => true,
                'status' => true,

                
            ],
            [
                'code' => 'TRY',
                'name' => $this->tr('Turkish Lira', 'الليرة التركية'),
                'symbol' => '₺',
                'rate' => 32,
                'is_default' => false,
                'status' => true,
            ],
            [
                'code' => 'SYP',
                'name' => $this->tr('Syrian Pound', 'الليرة السورية'),
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
