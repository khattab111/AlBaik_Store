<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AccessControlSeeder::class,
            DemoUsersSeeder::class,
            CurrencySeeder::class,
            PaymentMethodsSeeder::class,
            ShippingSeeder::class,
            ElectronicsCatalogSeeder::class,
            ElectronicsProductsSeeder::class,
            MarketingOffersSeeder::class,
            DemoCustomerDataSeeder::class,
            StoreSettingsAndBannersSeeder::class,
        ]);
    }
}
