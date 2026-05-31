<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Supplier;
use App\Models\Tag;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class ElectronicsCatalogSeeder extends Seeder
{
    use SeedsTranslations;

    public function run(): void
    {
        $brands = [
            ['slug' => 'apple', 'en' => 'Apple', 'ar' => 'آبل', 'desc' => 'iPhone, iPad and original accessories.'],
            ['slug' => 'samsung', 'en' => 'Samsung', 'ar' => 'سامسونج', 'desc' => 'Android phones, tablets and wearables.'],
            ['slug' => 'xiaomi', 'en' => 'Xiaomi', 'ar' => 'شاومي', 'desc' => 'Value smartphones and smart devices.'],
            ['slug' => 'anker', 'en' => 'Anker', 'ar' => 'أنكر', 'desc' => 'Chargers, power banks and cables.'],
            ['slug' => 'baseus', 'en' => 'Baseus', 'ar' => 'بيسوس', 'desc' => 'Mobile accessories and charging solutions.'],
            ['slug' => 'sony', 'en' => 'Sony', 'ar' => 'سوني', 'desc' => 'Audio devices and electronics.'],
        ];

        foreach ($brands as $brand) {
            Brand::updateOrCreate(['slug' => $brand['slug']], [
                'name' => $this->tr($brand['en'], $brand['ar']),
                'description' => $this->tr($brand['desc'], 'منتجات إلكترونية أصلية مع ضمان.'),
                'logo' => 'demo/brands/'.$brand['slug'].'.png',
                'status' => true,
            ]);
        }

        Supplier::updateOrCreate(['slug' => 'albaik-electronics-main'], [
            'name' => 'AlBaik Electronics Main Supplier',
            'email' => 'supply@albaik-electronics.local',
            'phone' => '+963111111111',
            'address' => 'Damascus electronics logistics hub',
            'is_active' => true,
        ]);

        Supplier::updateOrCreate(['slug' => 'smart-goods-supplier'], [
            'name' => 'Smart Goods Supplier',
            'email' => 'sales@smartgoods.example',
            'phone' => '+905551112233',
            'address' => 'Istanbul trade zone',
            'is_active' => true,
        ]);

        $electronics = Category::updateOrCreate(['slug' => 'electronics'], [
            'name' => $this->tr('Electronics', 'الإلكترونيات'),
            'description' => $this->tr('Smart devices and accessories.', 'أجهزة ذكية وإكسسوارات.'),
            'parent_id' => null,
            'status' => true,
        ]);

        $categories = [
            ['slug' => 'smartphones', 'en' => 'Smartphones', 'ar' => 'الهواتف الذكية'],
            ['slug' => 'tablets', 'en' => 'Tablets', 'ar' => 'الأجهزة اللوحية'],
            ['slug' => 'smart-watches', 'en' => 'Smart Watches', 'ar' => 'الساعات الذكية'],
            ['slug' => 'headphones', 'en' => 'Headphones & Earbuds', 'ar' => 'السماعات'],
            ['slug' => 'chargers-cables', 'en' => 'Chargers & Cables', 'ar' => 'الشواحن والكابلات'],
            ['slug' => 'power-banks', 'en' => 'Power Banks', 'ar' => 'باور بانك'],
            ['slug' => 'phone-accessories', 'en' => 'Phone Accessories', 'ar' => 'إكسسوارات الهواتف'],
            ['slug' => 'computer-parts', 'en' => 'Computer Parts', 'ar' => 'قطع الكمبيوتر'],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(['slug' => $category['slug']], [
                'name' => $this->tr($category['en'], $category['ar']),
                'description' => $this->tr('Original products with warranty.', 'منتجات أصلية مع ضمان.'),
                'parent_id' => $electronics->id,
                'status' => true,
            ]);
        }

        $tags = [
            ['slug' => 'popular', 'en' => 'Popular', 'ar' => 'الأكثر طلباً'],
            ['slug' => 'wholesale', 'en' => 'Wholesale', 'ar' => 'الجملة'],
            ['slug' => 'new-arrival', 'en' => 'New Arrival', 'ar' => 'وصل حديثاً'],
            ['slug' => 'featured', 'en' => 'Featured', 'ar' => 'مميز'],
            ['slug' => 'phones', 'en' => 'Phones', 'ar' => 'هواتف'],
            ['slug' => 'accessories', 'en' => 'Accessories', 'ar' => 'إكسسوارات'],
            ['slug' => 'fast-charging', 'en' => 'Fast Charging', 'ar' => 'شحن سريع'],
            ['slug' => 'warranty', 'en' => 'Warranty', 'ar' => 'ضمان'],
        ];

        foreach ($tags as $tag) {
            Tag::updateOrCreate(['slug' => $tag['slug']], [
                'name' => $this->tr($tag['en'], $tag['ar']),
                'status' => true,
            ]);
        }

        Warehouse::updateOrCreate(['code' => 'MAIN'], [
            'name' => 'Main Electronics Warehouse',
            'address' => 'Industrial Zone, Damascus',
            'city' => 'Damascus',
            'country' => 'Syria',
            'is_active' => true,
        ]);

        Warehouse::updateOrCreate(['code' => 'NORTH'], [
            'name' => 'North Electronics Warehouse',
            'address' => 'Aleppo Road',
            'city' => 'Aleppo',
            'country' => 'Syria',
            'is_active' => true,
        ]);
    }
}
