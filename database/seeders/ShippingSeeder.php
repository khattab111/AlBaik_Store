<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Setting;
use App\Models\ShippingCarrier;
use App\Models\ShippingRate;
use Illuminate\Database\Seeder;

class ShippingSeeder extends Seeder
{
    use SeedsTranslations;

    public function run(): void
    {
        Setting::updateOrCreate(['key' => 'shipping.default_product_weight'], [
            'group' => 'shipping',
            'value' => ['value' => '0.5'],
            'type' => 'number',
            'is_public' => false,
        ]);
        Setting::updateOrCreate(['key' => 'shipping.enable_free_shipping'], [
            'group' => 'shipping',
            'value' => ['value' => false],
            'type' => 'boolean',
            'is_public' => false,
        ]);
        Setting::updateOrCreate(['key' => 'shipping.enable_rate_free_shipping'], [
            'group' => 'shipping',
            'value' => ['value' => false],
            'type' => 'boolean',
            'is_public' => false,
        ]);
        Setting::updateOrCreate(['key' => 'shipping.global_free_shipping_threshold'], [
            'group' => 'shipping',
            'value' => ['value' => '250'],
            'type' => 'number',
            'is_public' => false,
        ]);

        $cities = [
            ['slug' => 'damascus', 'en' => 'Damascus', 'ar' => 'دمشق', 'code' => 'DAM', 'sort' => 1],
            ['slug' => 'aleppo', 'en' => 'Aleppo', 'ar' => 'حلب', 'code' => 'ALP', 'sort' => 2],
            ['slug' => 'idlib', 'en' => 'Idlib', 'ar' => 'إدلب', 'code' => 'IDL', 'sort' => 3],
            ['slug' => 'latakia', 'en' => 'Latakia', 'ar' => 'اللاذقية', 'code' => 'LAT', 'sort' => 4],
        ];

        foreach ($cities as $city) {
            City::updateOrCreate(['slug' => $city['slug']], [
                'name' => $this->tr($city['en'], $city['ar']),
                'country' => 'Syria',
                'code' => $city['code'],
                'is_active' => true,
                'sort_order' => $city['sort'],
            ]);
        }

        $carriers = [
            ['slug' => 'alharam', 'en' => 'Al Haram Shipping', 'ar' => 'الهرم للشحن', 'sort' => 1],
            ['slug' => 'express-courier', 'en' => 'Express Courier', 'ar' => 'الشحن السريع', 'sort' => 2],
            ['slug' => 'electronics-secure', 'en' => 'Electronics Secure Delivery', 'ar' => 'الشحن الآمن للإلكترونيات', 'sort' => 3],
        ];

        foreach ($carriers as $carrier) {
            ShippingCarrier::updateOrCreate(['slug' => $carrier['slug']], [
                'name' => $this->tr($carrier['en'], $carrier['ar']),
                'tracking_url' => 'https://tracking.example/'.$carrier['slug'].'/{tracking}',
                'status' => 'active',
                'sort_order' => $carrier['sort'],
            ]);
        }

        $rates = [
            ['carrier' => 'alharam', 'city' => 'damascus', 'base' => 3.00, 'kg' => 0.50, 'max' => null, 'threshold' => null, 'delivery_en' => '24-48h', 'delivery_ar' => '24-48 ساعة', 'remote' => 0],
            ['carrier' => 'alharam', 'city' => 'aleppo', 'base' => 4.00, 'kg' => 0.65, 'max' => null, 'threshold' => null, 'delivery_en' => '2-4 days', 'delivery_ar' => '2-4 أيام', 'remote' => 0],
            ['carrier' => 'alharam', 'city' => 'idlib', 'base' => 3.00, 'kg' => 0.50, 'max' => null, 'threshold' => null, 'delivery_en' => '2-3 days', 'delivery_ar' => '2-3 أيام', 'remote' => 0],
            ['carrier' => 'express-courier', 'city' => 'damascus', 'base' => 6.00, 'kg' => 1.25, 'max' => 20, 'threshold' => null, 'delivery_en' => 'Same day', 'delivery_ar' => 'نفس اليوم', 'remote' => 0],
            ['carrier' => 'express-courier', 'city' => 'aleppo', 'base' => 7.00, 'kg' => 1.50, 'max' => 15, 'threshold' => null, 'delivery_en' => '24-48h', 'delivery_ar' => '24-48 ساعة', 'remote' => 1.50],
            ['carrier' => 'electronics-secure', 'city' => 'damascus', 'base' => 8.00, 'kg' => 1.10, 'max' => 10, 'threshold' => null, 'delivery_en' => '24h', 'delivery_ar' => '24 ساعة', 'remote' => 0],
            ['carrier' => 'electronics-secure', 'city' => 'latakia', 'base' => 9.00, 'kg' => 1.35, 'max' => 10, 'threshold' => null, 'delivery_en' => '2-3 days', 'delivery_ar' => '2-3 أيام', 'remote' => 2.00],
        ];

        foreach ($rates as $rate) {
            $carrier = ShippingCarrier::where('slug', $rate['carrier'])->firstOrFail();
            $city = City::where('slug', $rate['city'])->firstOrFail();

            ShippingRate::updateOrCreate([
                'shipping_carrier_id' => $carrier->id,
                'city_id' => $city->id,
            ], [
                'is_active' => true,
                'base_cost' => $rate['base'],
                'cost_per_kg' => $rate['kg'],
                'min_weight' => null,
                'max_weight' => $rate['max'],
                'free_shipping_threshold' => $rate['threshold'],
                'estimated_delivery_time' => $this->tr($rate['delivery_en'], $rate['delivery_ar']),
                'remote_area_fee' => $rate['remote'],
                'sort_order' => 1,
            ]);
        }
    }
}
