<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductPriceTier;
use App\Models\ProductVariant;
use App\Models\Supplier;
use App\Models\Tag;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class ElectronicsProductsSeeder extends Seeder
{
    use SeedsTranslations;

    public function run(): void
    {
        $products = [
            [
                'key' => 'iphone-15-pro-max',
                'category' => 'smartphones',
                'brand' => 'apple',
                'supplier' => 'smart-goods-supplier',
                'tags' => ['phones', 'featured', 'warranty'],
                'sku' => 'APL-IP15PM-256-TTN',
                'name' => 'Apple iPhone 15 Pro Max 256GB',
                'name_ar' => 'آيفون 15 برو ماكس 256GB',
                'slug' => 'apple-iphone-15-pro-max-256gb',
                'retail_price' => 1399.00,
                'wholesale_price' => 1320.00,
                'wholesale_minimum_quantity' => 3,
                'stock_quantity' => 18,
                'weight' => 0.221,
                'is_featured' => true,
                'specs' => ['Storage' => '256GB', 'RAM' => '8GB', 'Screen' => '6.7 inch', 'Camera' => '48MP', 'Warranty' => '12 months'],
                'variants' => [
                    ['sku' => 'APL-IP15PM-256-TTN-BLK', 'attributes' => ['color' => 'Black Titanium', 'storage' => '256GB'], 'stock' => 8, 'price' => 1399.00],
                    ['sku' => 'APL-IP15PM-256-TTN-NAT', 'attributes' => ['color' => 'Natural Titanium', 'storage' => '256GB'], 'stock' => 10, 'price' => 1399.00],
                ],
            ],
            [
                'key' => 'galaxy-s24-ultra',
                'category' => 'smartphones',
                'brand' => 'samsung',
                'supplier' => 'smart-goods-supplier',
                'tags' => ['phones', 'new-arrival', 'featured'],
                'sku' => 'SMS-S24U-256-BLK',
                'name' => 'Samsung Galaxy S24 Ultra 256GB',
                'name_ar' => 'سامسونج جالكسي S24 ألترا 256GB',
                'slug' => 'samsung-galaxy-s24-ultra-256gb',
                'retail_price' => 1199.00,
                'wholesale_price' => 1135.00,
                'wholesale_minimum_quantity' => 3,
                'stock_quantity' => 24,
                'weight' => 0.232,
                'is_featured' => true,
                'specs' => ['Storage' => '256GB', 'RAM' => '12GB', 'Screen' => '6.8 inch', 'Camera' => '200MP', 'Battery' => '5000mAh'],
                'variants' => [
                    ['sku' => 'SMS-S24U-256-BLK-01', 'attributes' => ['color' => 'Black', 'storage' => '256GB'], 'stock' => 12, 'price' => 1199.00],
                    ['sku' => 'SMS-S24U-512-GRY-01', 'attributes' => ['color' => 'Gray', 'storage' => '512GB'], 'stock' => 8, 'price' => 1299.00],
                ],
            ],
            [
                'key' => 'xiaomi-14-ultra',
                'category' => 'smartphones',
                'brand' => 'xiaomi',
                'supplier' => 'smart-goods-supplier',
                'tags' => ['phones', 'popular'],
                'sku' => 'XMI-14U-512-BLK',
                'name' => 'Xiaomi 14 Ultra 512GB',
                'name_ar' => 'شاومي 14 ألترا 512GB',
                'slug' => 'xiaomi-14-ultra-512gb',
                'retail_price' => 899.00,
                'wholesale_price' => 850.00,
                'wholesale_minimum_quantity' => 4,
                'stock_quantity' => 30,
                'weight' => 0.229,
                'is_featured' => true,
                'specs' => ['Storage' => '512GB', 'RAM' => '16GB', 'Screen' => '6.73 inch', 'Camera' => 'Leica 50MP', 'Battery' => '5000mAh'],
                'variants' => [
                    ['sku' => 'XMI-14U-512-BLK-01', 'attributes' => ['color' => 'Black', 'storage' => '512GB'], 'stock' => 16, 'price' => 899.00],
                    ['sku' => 'XMI-14U-512-WHT-01', 'attributes' => ['color' => 'White', 'storage' => '512GB'], 'stock' => 14, 'price' => 899.00],
                ],
            ],
            [
                'key' => 'airpods-pro-2',
                'category' => 'headphones',
                'brand' => 'apple',
                'supplier' => 'smart-goods-supplier',
                'tags' => ['accessories', 'popular', 'warranty'],
                'sku' => 'APL-APP2-USBC',
                'name' => 'Apple AirPods Pro 2 USB-C',
                'name_ar' => 'سماعة آبل AirPods Pro 2 USB-C',
                'slug' => 'apple-airpods-pro-2-usb-c',
                'retail_price' => 249.00,
                'wholesale_price' => 229.00,
                'wholesale_minimum_quantity' => 6,
                'stock_quantity' => 60,
                'weight' => 0.061,
                'is_featured' => true,
                'specs' => ['Noise Cancellation' => 'Active', 'Charging Port' => 'USB-C', 'Battery Life' => 'Up to 30 hours'],
                'variants' => [
                    ['sku' => 'APL-APP2-USBC-WHT', 'attributes' => ['color' => 'White'], 'stock' => 60, 'price' => 249.00],
                ],
            ],
            [
                'key' => 'anker-20w-charger',
                'category' => 'chargers-cables',
                'brand' => 'anker',
                'supplier' => 'albaik-electronics-main',
                'tags' => ['accessories', 'fast-charging', 'wholesale'],
                'sku' => 'ANK-CHG-20W-C',
                'name' => 'Anker 20W USB-C Fast Charger',
                'name_ar' => 'شاحن أنكر سريع 20 واط USB-C',
                'slug' => 'anker-20w-usb-c-fast-charger',
                'retail_price' => 19.00,
                'wholesale_price' => 15.50,
                'wholesale_minimum_quantity' => 20,
                'stock_quantity' => 250,
                'weight' => 0.090,
                'is_featured' => false,
                'specs' => ['Power' => '20W', 'Port' => 'USB-C', 'Fast Charging' => 'PD'],
                'variants' => [
                    ['sku' => 'ANK-CHG-20W-C-WHT', 'attributes' => ['color' => 'White', 'power' => '20W'], 'stock' => 120, 'price' => 19.00],
                    ['sku' => 'ANK-CHG-20W-C-BLK', 'attributes' => ['color' => 'Black', 'power' => '20W'], 'stock' => 130, 'price' => 19.00],
                ],
            ],
            [
                'key' => 'baseus-cable-1m',
                'category' => 'chargers-cables',
                'brand' => 'baseus',
                'supplier' => 'albaik-electronics-main',
                'tags' => ['accessories', 'fast-charging', 'wholesale'],
                'sku' => 'BAS-CBL-C2C-1M',
                'name' => 'Baseus USB-C to USB-C Cable 1m',
                'name_ar' => 'كابل بيسوس USB-C إلى USB-C طول 1 متر',
                'slug' => 'baseus-usb-c-to-usb-c-cable-1m',
                'retail_price' => 8.50,
                'wholesale_price' => 6.25,
                'wholesale_minimum_quantity' => 50,
                'stock_quantity' => 500,
                'weight' => 0.045,
                'is_featured' => false,
                'specs' => ['Length' => '1m', 'Power' => '60W', 'Material' => 'Braided nylon'],
                'variants' => [
                    ['sku' => 'BAS-CBL-C2C-1M-BLK', 'attributes' => ['color' => 'Black', 'length' => '1m'], 'stock' => 250, 'price' => 8.50],
                    ['sku' => 'BAS-CBL-C2C-1M-WHT', 'attributes' => ['color' => 'White', 'length' => '1m'], 'stock' => 250, 'price' => 8.50],
                ],
            ],
            [
                'key' => 'anker-powerbank-10000',
                'category' => 'power-banks',
                'brand' => 'anker',
                'supplier' => 'albaik-electronics-main',
                'tags' => ['accessories', 'popular', 'fast-charging'],
                'sku' => 'ANK-PB-10000-20W',
                'name' => 'Anker Power Bank 10000mAh 20W',
                'name_ar' => 'باور بانك أنكر 10000mAh 20W',
                'slug' => 'anker-power-bank-10000mah-20w',
                'retail_price' => 39.00,
                'wholesale_price' => 33.00,
                'wholesale_minimum_quantity' => 12,
                'stock_quantity' => 90,
                'weight' => 0.220,
                'is_featured' => true,
                'specs' => ['Capacity' => '10000mAh', 'Power' => '20W', 'Ports' => 'USB-C + USB-A'],
                'variants' => [
                    ['sku' => 'ANK-PB-10000-20W-BLK', 'attributes' => ['color' => 'Black', 'capacity' => '10000mAh'], 'stock' => 90, 'price' => 39.00],
                ],
            ],
            [
                'key' => 'sony-wh1000xm5',
                'category' => 'headphones',
                'brand' => 'sony',
                'supplier' => 'smart-goods-supplier',
                'tags' => ['featured', 'warranty'],
                'sku' => 'SNY-WH1000XM5-BLK',
                'name' => 'Sony WH-1000XM5 Wireless Headphones',
                'name_ar' => 'سماعة سوني WH-1000XM5 اللاسلكية',
                'slug' => 'sony-wh-1000xm5-wireless-headphones',
                'retail_price' => 349.00,
                'wholesale_price' => 325.00,
                'wholesale_minimum_quantity' => 5,
                'stock_quantity' => 22,
                'weight' => 0.250,
                'is_featured' => true,
                'specs' => ['Noise Cancellation' => 'Active', 'Battery Life' => '30 hours', 'Bluetooth' => '5.2'],
                'variants' => [
                    ['sku' => 'SNY-WH1000XM5-BLK-01', 'attributes' => ['color' => 'Black'], 'stock' => 12, 'price' => 349.00],
                    ['sku' => 'SNY-WH1000XM5-SLV-01', 'attributes' => ['color' => 'Silver'], 'stock' => 10, 'price' => 349.00],
                ],
            ],
            [
                'key' => 'logitech-mx-master-3s',
                'category' => 'computer-parts',
                'brand' => 'baseus',
                'supplier' => 'albaik-electronics-main',
                'tags' => ['accessories', 'new-arrival'],
                'sku' => 'LOG-MX-MASTER-3S',
                'name' => 'Wireless Performance Mouse',
                'name_ar' => 'ماوس لاسلكي احترافي',
                'slug' => 'wireless-performance-mouse',
                'retail_price' => 89.00,
                'wholesale_price' => 78.00,
                'wholesale_minimum_quantity' => 10,
                'stock_quantity' => 45,
                'weight' => 0.141,
                'is_featured' => false,
                'specs' => ['Connection' => 'Bluetooth / USB Receiver', 'Battery' => 'Rechargeable', 'DPI' => '8000'],
                'variants' => [
                    ['sku' => 'LOG-MX-MASTER-3S-GRY', 'attributes' => ['color' => 'Graphite'], 'stock' => 45, 'price' => 89.00],
                ],
            ],
        ];

        foreach ($products as $data) {
            $brand = Brand::where('slug', $data['brand'])->firstOrFail();
            $supplier = Supplier::where('slug', $data['supplier'])->firstOrFail();
            $category = Category::where('slug', $data['category'])->firstOrFail();

            $product = Product::updateOrCreate(['sku' => $data['sku']], [
                'name' => $this->tr($data['name'], $data['name_ar']),
                'slug' => $data['slug'],
                'brand_id' => $brand->id,
                'supplier_id' => $supplier->id,
                'category_id' => $category->id,
                'short_description' => $this->tr('Original electronics product with warranty.', 'منتج إلكتروني أصلي مع ضمان.'),
                'description' => $this->tr('Detailed demo product for electronics store testing.', 'منتج تجريبي تفصيلي مناسب لاختبار متجر الهواتف والإلكترونيات.'),
                'retail_price' => $data['retail_price'],
                'wholesale_price' => $data['wholesale_price'],
                'wholesale_minimum_quantity' => $data['wholesale_minimum_quantity'],
                'is_wholesale_available' => true,
                'stock_quantity' => $data['stock_quantity'],
                'weight' => $data['weight'],
                'low_stock_threshold' => 10,
                'status' => true,
                'is_featured' => $data['is_featured'],
                'seo_title' => $this->tr($data['name'].' | AlBaik Electronics', $data['name_ar'].' | متجر البيك'),
                'seo_description' => $this->tr('Buy '.$data['name'].' with warranty.', 'اشتر '.$data['name_ar'].' مع ضمان.'),
            ]);

            $tagIds = collect($data['tags'])->map(fn ($slug) => Tag::where('slug', $slug)->value('id'))->filter()->values()->all();
            $product->tags()->sync($tagIds);

            ProductImage::updateOrCreate(['product_id' => $product->id, 'path' => 'demo/products/'.$data['slug'].'-main.jpg'], ['alt_text' => $this->tr($data['name'], $data['name_ar']), 'is_primary' => true]);
            ProductImage::updateOrCreate(['product_id' => $product->id, 'path' => 'demo/products/'.$data['slug'].'-gallery.jpg'], ['alt_text' => $this->tr($data['name'].' gallery', $data['name_ar'].' gallery'), 'is_primary' => false]);

            ProductPriceTier::updateOrCreate(['product_id' => $product->id, 'type' => 'retail', 'min_quantity' => 1], ['price' => $data['retail_price'], 'is_active' => true, 'sort_order' => 1]);
            ProductPriceTier::updateOrCreate(['product_id' => $product->id, 'type' => 'wholesale', 'min_quantity' => $data['wholesale_minimum_quantity']], ['price' => $data['wholesale_price'], 'is_active' => true, 'sort_order' => 10]);
            ProductPriceTier::updateOrCreate(['product_id' => $product->id, 'type' => 'wholesale', 'min_quantity' => max(50, $data['wholesale_minimum_quantity'] * 2)], ['price' => round($data['wholesale_price'] * 0.94, 2), 'is_active' => true, 'sort_order' => 20]);

            foreach ($data['variants'] as $variantData) {
                $variant = ProductVariant::updateOrCreate(['sku' => $variantData['sku']], [
                    'product_id' => $product->id,
                    'barcode' => 'BC'.str_pad((string) abs(crc32($variantData['sku'])), 12, '0', STR_PAD_LEFT),
                    'attributes' => $variantData['attributes'],
                    'stock' => $variantData['stock'],
                    'reserved_stock' => 0,
                    'low_stock_threshold' => 8,
                    'price' => $variantData['price'],
                ]);

                Warehouse::query()->where('is_active', true)->get()->each(function (Warehouse $warehouse) use ($variant, $variantData) {
                    InventoryMovement::updateOrCreate([
                        'warehouse_id' => $warehouse->id,
                        'product_variant_id' => $variant->id,
                        'type' => 'opening_stock',
                        'source_type' => 'seeder',
                        'source_id' => $variant->id,
                    ], [
                        'quantity' => (int) floor($variantData['stock'] / max(1, Warehouse::query()->where('is_active', true)->count())),
                        'metadata' => ['note' => 'Opening stock from electronics demo seeder.'],
                    ]);
                });
            }
        }
    }
}
