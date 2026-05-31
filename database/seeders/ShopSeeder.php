<?php

namespace Database\Seeders;

use App\Models\Banner;
use App\Models\Brand;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\City;
use App\Models\Coupon;
use App\Models\Currency;
use App\Models\FlashOffer;
use App\Models\FlashOfferItem;
use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductPriceTier;
use App\Models\ProductVariant;
use App\Models\Review;
use App\Models\Setting;
use App\Models\ShippingCarrier;
use App\Models\ShippingRate;
use App\Models\Supplier;
use App\Models\Tag;
use App\Models\User;
use App\Models\UserAddress;
use App\Models\Warehouse;
use App\Models\WholesaleApplication;
use App\Models\Wishlist;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ShopSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedAccessControl();
        $users = $this->seedUsers();
        $currencies = $this->seedCurrencies();
        $payments = $this->seedPaymentMethods();
        $shipping = $this->seedShipping();
        $catalog = $this->seedCatalog();
        $this->seedProducts($catalog);
        $this->seedMarketing($catalog['products']);
        $this->seedCustomerData($users, $currencies, $payments, $shipping, $catalog['products']);
        $this->seedSettingsAndBanners();
    }

    private function seedAccessControl(): void
    {
        $roles = ['Super Admin', 'Admin', 'Manager', 'Customer', 'Wholesale Customer'];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

        $permissions = [
            'manage products',
            'manage orders',
            'manage users',
            'manage settings',
            'manage inventory',
            'manage marketing',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        Role::findByName('Super Admin')->syncPermissions($permissions);
        Role::findByName('Admin')->syncPermissions($permissions);
        Role::findByName('Manager')->syncPermissions(['manage products', 'manage orders', 'manage inventory']);
        Role::findByName('Customer')->syncPermissions([]);
        Role::findByName('Wholesale Customer')->syncPermissions([]);
    }

    private function seedUsers(): array
    {
        $users = [
            'super_admin' => [
                'email' => ' ',
                'name' => 'Super Admin',
                'mobile' => '+963900000000',
                'type' => 'super_admin',
                'role' => 'Super Admin',
            ],
            'admin' => [
                'email' => 'admin@qr.com',
                'name' => 'Demo Admin',
                'mobile' => '+963900000001',
                'type' => 'admin',
                'role' => 'Admin',
            ],
            'manager' => [
                'email' => 'manager@qr.com',
                'name' => 'Inventory Manager',
                'mobile' => '+963900000004',
                'type' => 'manager',
                'role' => 'Manager',
            ],
            'customer' => [
                'email' => 'customer@qr.com',
                'name' => 'Demo Customer',
                'mobile' => '+963900000002',
                'type' => 'customer',
                'role' => 'Customer',
            ],
            'wholesale' => [
                'email' => 'wholesale@qr.com',
                'name' => 'Wholesale Customer',
                'mobile' => '+963900000003',
                'type' => 'wholesale_customer',
                'role' => 'Wholesale Customer',
            ],
        ];

        return collect($users)->mapWithKeys(function (array $payload, string $key) {
            $role = $payload['role'];
            unset($payload['role']);

            $user = User::updateOrCreate(
                ['email' => $payload['email']],
                array_merge($payload, [
                    'password' => Hash::make('password'),
                    'status' => true,
                    'email_verified_at' => now(),
                ])
            );
            $user->syncRoles([$role]);

            return [$key => $user];
        })->all();
    }

    private function seedCurrencies(): array
    {
        return [
            'USD' => Currency::updateOrCreate(['code' => 'USD'], ['symbol' => '$', 'name' => 'US Dollar', 'rate' => 1.000000, 'is_default' => true, 'status' => true]),
            'TRY' => Currency::updateOrCreate(['code' => 'TRY'], ['symbol' => '₺', 'name' => 'Turkish Lira', 'rate' => 28.000000, 'is_default' => false, 'status' => true]),
            'SYP' => Currency::updateOrCreate(['code' => 'SYP'], ['symbol' => 'ل.س', 'name' => 'Syrian Pound', 'rate' => 13000.000000, 'is_default' => false, 'status' => true]),
        ];
    }

    private function seedPaymentMethods(): array
    {
        return [
            'cod' => PaymentMethod::updateOrCreate(['slug' => 'cod'], ['name' => $this->tr('Cash on Delivery', 'الدفع عند الاستلام'), 'type' => 'cod', 'description' => $this->tr('Collect payment when the order is delivered.', 'يتم تحصيل قيمة الطلب عند التسليم.'), 'image' => 'demo/payments/cod.png', 'wallet_url' => null, 'barcode_image' => null, 'settings' => [], 'fee' => 0, 'is_active' => true]),
            'bank' => PaymentMethod::updateOrCreate(['slug' => 'bank-transfer'], ['name' => $this->tr('Bank Transfer', 'تحويل بنكي'), 'type' => 'bank_transfer', 'description' => $this->tr('Customer transfers to company bank account and uploads receipt.', 'يقوم العميل بالتحويل إلى حساب الشركة ثم يرفع إشعار الدفع.'), 'image' => 'demo/payments/bank.png', 'wallet_url' => 'IBAN: TR00 0000 0000 0000 0000 0000 00', 'barcode_image' => 'demo/payments/bank-qr.png', 'settings' => ['bank_name' => 'Qarid Demo Bank', 'iban' => 'TR00 0000 0000 0000 0000 0000 00'], 'fee' => 0, 'is_active' => true]),
            'manual' => PaymentMethod::updateOrCreate(['slug' => 'manual'], ['name' => $this->tr('Manual Wallet', 'محفظة يدوية'), 'type' => 'manual', 'description' => $this->tr('Customer pays to wallet, then uploads payment receipt for admin review.', 'يدفع العميل إلى المحفظة ثم يرفع صورة الإشعار لمراجعة الإدارة.'), 'image' => 'demo/payments/wallet.png', 'wallet_url' => 'https://wallet.example/pay/albaik-store', 'barcode_image' => 'demo/payments/wallet-qr.png', 'settings' => ['instructions' => 'Upload receipt or contact support.'], 'fee' => 1.50, 'is_active' => true]),
        ];
    }

    private function seedShipping(): array
    {
        $damascus = City::updateOrCreate(['slug' => 'damascus'], ['name' => $this->tr('Damascus', 'دمشق'), 'country' => 'Syria', 'code' => 'DAM', 'is_active' => true, 'sort_order' => 1]);
        $aleppo = City::updateOrCreate(['slug' => 'aleppo'], ['name' => $this->tr('Aleppo', 'حلب'), 'country' => 'Syria', 'code' => 'ALP', 'is_active' => true, 'sort_order' => 2]);
        $idlib = City::updateOrCreate(['slug' => 'idlib'], ['name' => $this->tr('Idlib', 'إدلب'), 'country' => 'Syria', 'code' => 'IDL', 'is_active' => true, 'sort_order' => 3]);

        $alharam = ShippingCarrier::updateOrCreate(['slug' => 'alharam'], ['name' => $this->tr('Al Haram Shipping', 'الهرم للشحن'), 'tracking_url' => 'https://tracking.example/alharam/{tracking}', 'status' => 'active', 'sort_order' => 1]);
        $express = ShippingCarrier::updateOrCreate(['slug' => 'express-courier'], ['name' => $this->tr('Express Courier', 'الشحن السريع'), 'tracking_url' => 'https://tracking.example/express/{tracking}', 'status' => 'active', 'sort_order' => 2]);

        $rates = [
            [$alharam, $damascus, 3.00, 0.50, null, null, 150.00, '24-48h', 0],
            [$alharam, $aleppo, 4.00, 0.65, null, null, 180.00, '2-4 days', 0],
            [$alharam, $idlib, 3.00, 0.50, null, null, 120.00, '2-3 days', 0],
            [$express, $damascus, 6.00, 1.25, null, 20, 250.00, 'Same day', 0],
            [$express, $aleppo, 7.00, 1.50, null, 15, 300.00, '24-48h', 1.50],
        ];

        foreach ($rates as [$carrier, $city, $base, $perKg, $minWeight, $maxWeight, $threshold, $delivery, $remoteFee]) {
            ShippingRate::updateOrCreate([
                'shipping_carrier_id' => $carrier->id,
                'city_id' => $city->id,
            ], [
                'is_active' => true,
                'base_cost' => $base,
                'cost_per_kg' => $perKg,
                'min_weight' => $minWeight,
                'max_weight' => $maxWeight,
                'free_shipping_threshold' => $threshold,
                'estimated_delivery_time' => $delivery,
                'remote_area_fee' => $remoteFee,
                'sort_order' => 1,
            ]);
        }

        return compact('damascus', 'aleppo', 'idlib', 'alharam', 'express');
    }

    private function seedCatalog(): array
    {
        $brands = [
            'albaik' => Brand::updateOrCreate(['slug' => 'albaik'], ['name' => $this->tr('AlBaik', 'البيك'), 'description' => $this->tr('Private label products.', 'منتجات العلامة الخاصة.'), 'logo' => 'demo/brands/albaik.png', 'status' => true]),
            'qarid' => Brand::updateOrCreate(['slug' => 'qarid-select'], ['name' => $this->tr('Qarid Select', 'قريد سيلكت'), 'description' => $this->tr('Selected partner products.', 'منتجات مختارة من شركاء موثوقين.'), 'logo' => 'demo/brands/qarid-select.png', 'status' => true]),
            'levant' => Brand::updateOrCreate(['slug' => 'levant-foods'], ['name' => $this->tr('Levant Foods', 'ليفانت فودز'), 'description' => $this->tr('Regional food supplier brand.', 'علامة إقليمية لتوريد المواد الغذائية.'), 'logo' => 'demo/brands/levant-foods.png', 'status' => true]),
            'apple' => Brand::updateOrCreate(['slug' => 'apple'], ['name' => $this->tr('Apple', 'آبل'), 'description' => $this->tr('Premium smartphones and accessories.', 'هواتف وإكسسوارات فاخرة.'), 'logo' => 'demo/brands/apple.png', 'status' => true]),
            'samsung' => Brand::updateOrCreate(['slug' => 'samsung'], ['name' => $this->tr('Samsung', 'سامسونج'), 'description' => $this->tr('Android smartphones for retail and business customers.', 'هواتف أندرويد لعملاء التجزئة والشركات.'), 'logo' => 'demo/brands/samsung.png', 'status' => true]),
            'xiaomi' => Brand::updateOrCreate(['slug' => 'xiaomi'], ['name' => $this->tr('Xiaomi', 'شاومي'), 'description' => $this->tr('Value smartphones with strong specifications.', 'هواتف اقتصادية بمواصفات قوية.'), 'logo' => 'demo/brands/xiaomi.png', 'status' => true]),
        ];

        $suppliers = [
            'main' => Supplier::updateOrCreate(['slug' => 'albaik-main'], ['name' => 'AlBaik Main Supplier', 'email' => 'supply@albaikstore.local', 'phone' => '+963111111111', 'address' => 'Damascus logistics hub', 'is_active' => true]),
            'food' => Supplier::updateOrCreate(['slug' => 'levant-foods-supplier'], ['name' => 'Levant Foods Supplier', 'email' => 'orders@levant.example', 'phone' => '+963222222222', 'address' => 'Aleppo warehouse', 'is_active' => true]),
            'tech' => Supplier::updateOrCreate(['slug' => 'smart-goods-supplier'], ['name' => 'Smart Goods Supplier', 'email' => 'sales@smartgoods.example', 'phone' => '+905551112233', 'address' => 'Istanbul trade zone', 'is_active' => true]),
        ];

        $categories = [
            'food' => Category::updateOrCreate(['slug' => 'food'], ['name' => $this->tr('Food', 'الأغذية'), 'description' => $this->tr('Premium food products.', 'منتجات غذائية مميزة.'), 'parent_id' => null, 'status' => true]),
            'electronics' => Category::updateOrCreate(['slug' => 'electronics'], ['name' => $this->tr('Electronics', 'الإلكترونيات'), 'description' => $this->tr('Smart devices and accessories.', 'أجهزة ذكية وإكسسوارات.'), 'parent_id' => null, 'status' => true]),
            'bulk' => Category::updateOrCreate(['slug' => 'bulk-supplies'], ['name' => $this->tr('Bulk Supplies', 'توريدات الجملة'), 'description' => $this->tr('Wholesale packs and business supplies.', 'عبوات جملة ومستلزمات تجارية.'), 'parent_id' => null, 'status' => true]),
        ];

        $categories['sandwiches'] = Category::updateOrCreate(['slug' => 'sandwiches'], ['name' => $this->tr('Sandwiches', 'السندويش'), 'description' => $this->tr('Fresh and frozen sandwiches.', 'سندويش طازج ومجمد.'), 'parent_id' => $categories['food']->id, 'status' => true]);
        $categories['sauces'] = Category::updateOrCreate(['slug' => 'sauces'], ['name' => $this->tr('Sauces', 'الصلصات'), 'description' => $this->tr('Signature sauces.', 'صلصات مميزة.'), 'parent_id' => $categories['food']->id, 'status' => true]);
        $categories['drinkware'] = Category::updateOrCreate(['slug' => 'drinkware'], ['name' => $this->tr('Drinkware', 'الأكواب والقوارير'), 'description' => $this->tr('Bottles and cups.', 'قوارير وأكواب.'), 'parent_id' => $categories['electronics']->id, 'status' => true]);
        $categories['smartphones'] = Category::updateOrCreate(['slug' => 'smartphones'], ['name' => $this->tr('Smartphones', 'الهواتف الذكية'), 'description' => $this->tr('Latest original smartphones with warranty.', 'أحدث الهواتف الأصلية مع ضمان.'), 'parent_id' => $categories['electronics']->id, 'status' => true]);
        $categories['phone-accessories'] = Category::updateOrCreate(['slug' => 'phone-accessories'], ['name' => $this->tr('Phone Accessories', 'إكسسوارات الهواتف'), 'description' => $this->tr('Chargers, covers, and essential phone accessories.', 'شواحن وأغطية وإكسسوارات أساسية للهواتف.'), 'parent_id' => $categories['electronics']->id, 'status' => true]);

        $tags = [
            'popular' => Tag::updateOrCreate(['slug' => 'popular'], ['name' => $this->tr('Popular', 'الأكثر طلباً'), 'status' => true]),
            'wholesale' => Tag::updateOrCreate(['slug' => 'wholesale'], ['name' => $this->tr('Wholesale', 'الجملة'), 'status' => true]),
            'new' => Tag::updateOrCreate(['slug' => 'new-arrival'], ['name' => $this->tr('New Arrival', 'وصل حديثاً'), 'status' => true]),
            'featured' => Tag::updateOrCreate(['slug' => 'featured'], ['name' => $this->tr('Featured', 'مميز'), 'status' => true]),
            'phones' => Tag::updateOrCreate(['slug' => 'phones'], ['name' => $this->tr('Phones', 'هواتف'), 'status' => true]),
        ];

        $warehouses = [
            'main' => Warehouse::updateOrCreate(['code' => 'MAIN'], ['name' => 'Main Warehouse', 'address' => 'Industrial Zone, Damascus', 'city' => 'Damascus', 'country' => 'Syria', 'is_active' => true]),
            'north' => Warehouse::updateOrCreate(['code' => 'NORTH'], ['name' => 'North Warehouse', 'address' => 'Aleppo Road', 'city' => 'Aleppo', 'country' => 'Syria', 'is_active' => true]),
        ];

        return compact('brands', 'suppliers', 'categories', 'tags', 'warehouses') + ['products' => []];
    }

    private function seedProducts(array &$catalog): void
    {
        $products = [
            [
                'key' => 'sandwich',
                'category' => 'sandwiches',
                'brand' => 'albaik',
                'supplier' => 'main',
                'tags' => ['popular', 'wholesale', 'featured'],
                'sku' => 'ALB-CHICKEN-001',
                'name' => 'Classic Chicken Sandwich',
                'name_ar' => 'ساندويش دجاج كلاسيك',
                'slug' => 'classic-chicken-sandwich',
                'retail_price' => 6.99,
                'wholesale_price' => 5.49,
                'wholesale_minimum_quantity' => 20,
                'stock_quantity' => 120,
                'weight' => 0.350,
                'is_featured' => true,
                'variants' => [
                    ['sku' => 'ALB-CHICKEN-001-SPICY', 'attributes' => ['flavor' => 'Spicy', 'size' => 'Regular'], 'stock' => 80, 'price' => 7.49],
                    ['sku' => 'ALB-CHICKEN-001-CLASSIC', 'attributes' => ['flavor' => 'Classic', 'size' => 'Regular'], 'stock' => 90, 'price' => 6.99],
                ],
            ],
            [
                'key' => 'sauce',
                'category' => 'sauces',
                'brand' => 'albaik',
                'supplier' => 'main',
                'tags' => ['popular', 'new'],
                'sku' => 'ALB-SAUCE-001',
                'name' => 'Signature Garlic Sauce',
                'name_ar' => 'صلصة الثوم المميزة',
                'slug' => 'signature-garlic-sauce',
                'retail_price' => 3.50,
                'wholesale_price' => 2.75,
                'wholesale_minimum_quantity' => 50,
                'stock_quantity' => 300,
                'weight' => 0.180,
                'is_featured' => true,
                'variants' => [
                    ['sku' => 'ALB-SAUCE-001-250', 'attributes' => ['size' => '250g'], 'stock' => 150, 'price' => 3.50],
                    ['sku' => 'ALB-SAUCE-001-500', 'attributes' => ['size' => '500g'], 'stock' => 75, 'price' => 5.75],
                ],
            ],
            [
                'key' => 'bottle',
                'category' => 'drinkware',
                'brand' => 'qarid',
                'supplier' => 'tech',
                'tags' => ['popular', 'featured'],
                'sku' => 'ALB-TECH-001',
                'name' => 'AlBaik Smart Bottle',
                'name_ar' => 'قارورة البيك الذكية',
                'slug' => 'albaik-smart-bottle',
                'retail_price' => 34.99,
                'wholesale_price' => 27.99,
                'wholesale_minimum_quantity' => 15,
                'stock_quantity' => 45,
                'weight' => 0.800,
                'is_featured' => true,
                'variants' => [
                    ['sku' => 'ALB-TECH-001-BLACK', 'attributes' => ['color' => 'Black', 'capacity' => '750ml'], 'stock' => 30, 'price' => 34.99],
                    ['sku' => 'ALB-TECH-001-WHITE', 'attributes' => ['color' => 'White', 'capacity' => '750ml'], 'stock' => 24, 'price' => 34.99],
                ],
            ],
            [
                'key' => 'bulk-rice',
                'category' => 'bulk',
                'brand' => 'levant',
                'supplier' => 'food',
                'tags' => ['wholesale'],
                'sku' => 'LEV-RICE-025',
                'name' => 'Premium Rice Bulk Bag',
                'name_ar' => 'كيس أرز فاخر للجملة',
                'slug' => 'premium-rice-bulk-bag',
                'retail_price' => 29.00,
                'wholesale_price' => 24.00,
                'wholesale_minimum_quantity' => 10,
                'stock_quantity' => 85,
                'weight' => 25.000,
                'is_featured' => false,
                'variants' => [
                    ['sku' => 'LEV-RICE-025-WHITE', 'attributes' => ['type' => 'White', 'weight' => '25kg'], 'stock' => 70, 'price' => 29.00],
                ],
            ],
            [
                'key' => 'spices',
                'category' => 'bulk',
                'brand' => 'levant',
                'supplier' => 'food',
                'tags' => ['new', 'wholesale'],
                'sku' => 'LEV-SPICE-001',
                'name' => 'Restaurant Spice Mix',
                'name_ar' => 'خلطة بهارات المطاعم',
                'slug' => 'restaurant-spice-mix',
                'retail_price' => 12.00,
                'wholesale_price' => 9.25,
                'wholesale_minimum_quantity' => 25,
                'stock_quantity' => 160,
                'weight' => 1.000,
                'is_featured' => false,
                'variants' => [
                    ['sku' => 'LEV-SPICE-001-MILD', 'attributes' => ['flavor' => 'Mild'], 'stock' => 65, 'price' => 12.00],
                    ['sku' => 'LEV-SPICE-001-HOT', 'attributes' => ['flavor' => 'Hot'], 'stock' => 55, 'price' => 12.50],
                ],
            ],
            [
                'key' => 'iphone-15-pro',
                'category' => 'smartphones',
                'brand' => 'apple',
                'supplier' => 'tech',
                'tags' => ['new', 'featured', 'phones'],
                'sku' => 'PHN-APL-15PRO-128',
                'name' => 'iPhone 15 Pro 128GB',
                'name_ar' => 'آيفون 15 برو 128GB',
                'slug' => 'iphone-15-pro-128gb',
                'retail_price' => 999.00,
                'wholesale_price' => 949.00,
                'wholesale_minimum_quantity' => 5,
                'stock_quantity' => 28,
                'weight' => 0.221,
                'is_featured' => true,
                'variants' => [
                    ['sku' => 'PHN-APL-15PRO-128-BLK', 'attributes' => ['color' => 'Black Titanium', 'storage' => '128GB'], 'stock' => 12, 'price' => 999.00],
                    ['sku' => 'PHN-APL-15PRO-128-NAT', 'attributes' => ['color' => 'Natural Titanium', 'storage' => '128GB'], 'stock' => 16, 'price' => 999.00],
                ],
            ],
            [
                'key' => 'galaxy-s24-ultra',
                'category' => 'smartphones',
                'brand' => 'samsung',
                'supplier' => 'tech',
                'tags' => ['popular', 'featured', 'phones', 'wholesale'],
                'sku' => 'PHN-SAM-S24U-256',
                'name' => 'Samsung Galaxy S24 Ultra 256GB',
                'name_ar' => 'سامسونج جالكسي S24 ألترا 256GB',
                'slug' => 'samsung-galaxy-s24-ultra-256gb',
                'retail_price' => 1099.00,
                'wholesale_price' => 1025.00,
                'wholesale_minimum_quantity' => 4,
                'stock_quantity' => 35,
                'weight' => 0.232,
                'is_featured' => true,
                'variants' => [
                    ['sku' => 'PHN-SAM-S24U-256-BLK', 'attributes' => ['color' => 'Titanium Black', 'storage' => '256GB'], 'stock' => 18, 'price' => 1099.00],
                    ['sku' => 'PHN-SAM-S24U-256-GRY', 'attributes' => ['color' => 'Titanium Gray', 'storage' => '256GB'], 'stock' => 17, 'price' => 1099.00],
                ],
            ],
            [
                'key' => 'galaxy-a55',
                'category' => 'smartphones',
                'brand' => 'samsung',
                'supplier' => 'tech',
                'tags' => ['popular', 'phones', 'wholesale'],
                'sku' => 'PHN-SAM-A55-128',
                'name' => 'Samsung Galaxy A55 128GB',
                'name_ar' => 'سامسونج جالكسي A55 128GB',
                'slug' => 'samsung-galaxy-a55-128gb',
                'retail_price' => 379.00,
                'wholesale_price' => 345.00,
                'wholesale_minimum_quantity' => 8,
                'stock_quantity' => 64,
                'weight' => 0.213,
                'is_featured' => false,
                'variants' => [
                    ['sku' => 'PHN-SAM-A55-128-NVY', 'attributes' => ['color' => 'Navy', 'storage' => '128GB'], 'stock' => 34, 'price' => 379.00],
                    ['sku' => 'PHN-SAM-A55-128-LIL', 'attributes' => ['color' => 'Lilac', 'storage' => '128GB'], 'stock' => 30, 'price' => 379.00],
                ],
            ],
            [
                'key' => 'redmi-note-13-pro',
                'category' => 'smartphones',
                'brand' => 'xiaomi',
                'supplier' => 'tech',
                'tags' => ['new', 'phones', 'wholesale'],
                'sku' => 'PHN-XIA-RN13P-256',
                'name' => 'Xiaomi Redmi Note 13 Pro 256GB',
                'name_ar' => 'شاومي ريدمي نوت 13 برو 256GB',
                'slug' => 'xiaomi-redmi-note-13-pro-256gb',
                'retail_price' => 299.00,
                'wholesale_price' => 269.00,
                'wholesale_minimum_quantity' => 10,
                'stock_quantity' => 92,
                'weight' => 0.187,
                'is_featured' => false,
                'variants' => [
                    ['sku' => 'PHN-XIA-RN13P-256-BLK', 'attributes' => ['color' => 'Midnight Black', 'storage' => '256GB'], 'stock' => 45, 'price' => 299.00],
                    ['sku' => 'PHN-XIA-RN13P-256-BLU', 'attributes' => ['color' => 'Ocean Blue', 'storage' => '256GB'], 'stock' => 47, 'price' => 299.00],
                ],
            ],
            [
                'key' => 'iphone-14',
                'category' => 'smartphones',
                'brand' => 'apple',
                'supplier' => 'tech',
                'tags' => ['phones', 'wholesale'],
                'sku' => 'PHN-APL-14-128',
                'name' => 'iPhone 14 128GB',
                'name_ar' => 'آيفون 14 128GB',
                'slug' => 'iphone-14-128gb',
                'retail_price' => 699.00,
                'wholesale_price' => 655.00,
                'wholesale_minimum_quantity' => 6,
                'stock_quantity' => 42,
                'weight' => 0.172,
                'is_featured' => false,
                'variants' => [
                    ['sku' => 'PHN-APL-14-128-BLK', 'attributes' => ['color' => 'Midnight', 'storage' => '128GB'], 'stock' => 22, 'price' => 699.00],
                    ['sku' => 'PHN-APL-14-128-BLU', 'attributes' => ['color' => 'Blue', 'storage' => '128GB'], 'stock' => 20, 'price' => 699.00],
                ],
            ],
        ];

        foreach ($products as $data) {
            $product = Product::updateOrCreate(['sku' => $data['sku']], [
                'name' => $this->tr($data['name'], $data['name_ar']),
                'slug' => $data['slug'],
                'brand_id' => $catalog['brands'][$data['brand']]->id,
                'supplier_id' => $catalog['suppliers'][$data['supplier']]->id,
                'category_id' => $catalog['categories'][$data['category']]->id,
                'short_description' => $this->tr("Demo short description for {$data['name']}.", "وصف مختصر تجريبي لمنتج {$data['name_ar']}."),
                'description' => $this->tr("Detailed demo description for {$data['name']} with retail and wholesale use cases.", "وصف تفصيلي تجريبي لمنتج {$data['name_ar']} مع سيناريوهات التجزئة والجملة."),
                'retail_price' => $data['retail_price'],
                'wholesale_price' => $data['wholesale_price'],
                'wholesale_minimum_quantity' => $data['wholesale_minimum_quantity'],
                'stock_quantity' => $data['stock_quantity'],
                'weight' => $data['weight'],
                'low_stock_threshold' => 10,
                'status' => true,
                'is_featured' => $data['is_featured'],
                'seo_title' => $this->tr($data['name'].' | AlBaik Store', $data['name_ar'].' | متجر البيك'),
                'seo_description' => $this->tr("Buy {$data['name']} from AlBaik Store.", "اشتر {$data['name_ar']} من متجر البيك."),
            ]);

            $product->tags()->sync(collect($data['tags'])->map(fn ($tag) => $catalog['tags'][$tag]->id)->all());

            ProductImage::updateOrCreate(['product_id' => $product->id, 'path' => "demo/products/{$data['slug']}-main.jpg"], ['alt_text' => $data['name_ar'], 'is_primary' => true]);
            ProductImage::updateOrCreate(['product_id' => $product->id, 'path' => "demo/products/{$data['slug']}-gallery.jpg"], ['alt_text' => $data['name_ar'].' gallery', 'is_primary' => false]);

            ProductPriceTier::updateOrCreate([
                'product_id' => $product->id,
                'type' => 'retail',
                'min_quantity' => 1,
            ], [
                'price' => $data['retail_price'],
                'is_active' => true,
                'sort_order' => 1,
            ]);

            ProductPriceTier::updateOrCreate([
                'product_id' => $product->id,
                'type' => 'wholesale',
                'min_quantity' => $data['wholesale_minimum_quantity'],
            ], [
                'price' => $data['wholesale_price'],
                'is_active' => true,
                'sort_order' => 10,
            ]);

            ProductPriceTier::updateOrCreate([
                'product_id' => $product->id,
                'type' => 'wholesale',
                'min_quantity' => max(50, $data['wholesale_minimum_quantity'] * 2),
            ], [
                'price' => round($data['wholesale_price'] * 0.94, 2),
                'is_active' => true,
                'sort_order' => 20,
            ]);

            ProductPriceTier::updateOrCreate([
                'product_id' => $product->id,
                'type' => 'wholesale',
                'min_quantity' => max(100, $data['wholesale_minimum_quantity'] * 4),
            ], [
                'price' => round($data['wholesale_price'] * 0.9, 2),
                'is_active' => true,
                'sort_order' => 30,
            ]);

            foreach ($data['variants'] as $variantData) {
                $variant = ProductVariant::updateOrCreate(['sku' => $variantData['sku']], [
                    'product_id' => $product->id,
                    'barcode' => 'BC'.str_pad((string) crc32($variantData['sku']), 12, '0', STR_PAD_LEFT),
                    'attributes' => $variantData['attributes'],
                    'stock' => $variantData['stock'],
                    'reserved_stock' => 0,
                    'low_stock_threshold' => 8,
                    'price' => $variantData['price'],
                ]);

                foreach ($catalog['warehouses'] as $warehouse) {
                    InventoryMovement::updateOrCreate([
                        'warehouse_id' => $warehouse->id,
                        'product_variant_id' => $variant->id,
                        'type' => 'opening_stock',
                        'source_type' => 'seeder',
                        'source_id' => $variant->id,
                    ], [
                        'quantity' => (int) floor($variantData['stock'] / count($catalog['warehouses'])),
                        'metadata' => ['note' => 'Opening stock from demo seeder.'],
                    ]);
                }
            }

            $catalog['products'][$data['key']] = $product;
        }
    }

    private function seedMarketing(array $products): void
    {
        Coupon::updateOrCreate(['code' => 'WELCOME10'], ['type' => 'percentage', 'value' => 10, 'minimum_order_amount' => 20, 'starts_at' => now()->subWeek(), 'expires_at' => now()->addMonth(), 'usage_limit' => 500, 'used_count' => 3, 'is_active' => true]);
        Coupon::updateOrCreate(['code' => 'WHOLESALE25'], ['type' => 'fixed', 'value' => 25, 'minimum_order_amount' => 250, 'starts_at' => now()->subDay(), 'expires_at' => now()->addMonths(2), 'usage_limit' => 100, 'used_count' => 1, 'is_active' => true]);
        Coupon::updateOrCreate(['code' => 'EXPIRED5'], ['type' => 'fixed', 'value' => 5, 'minimum_order_amount' => 15, 'starts_at' => now()->subMonths(2), 'expires_at' => now()->subMonth(), 'usage_limit' => 50, 'used_count' => 12, 'is_active' => false]);

        $launch = FlashOffer::updateOrCreate(
            ['slug' => 'launch-offers-20'],
            [
                'title' => $this->tr('Launch Offers 20%', 'عروض الإطلاق 20%'),
                'description' => $this->tr('Limited percentage discount on selected products.', 'خصم نسبة محدود على منتجات مختارة.'),
                'type' => FlashOffer::TYPE_PERCENTAGE_DISCOUNT,
                'offer_scope' => FlashOffer::SCOPE_PRODUCT,
                'status' => FlashOffer::STATUS_ACTIVE,
                'starts_at' => now()->subDay(),
                'ends_at' => now()->addDays(14),
                'priority' => 20,
                'discount_type' => 'percentage',
                'discount_value' => 20,
                'max_quantity' => 300,
                'sold_quantity' => 44,
                'free_shipping_scope' => FlashOffer::FREE_SHIPPING_NONE,
            ]
        );
        FlashOfferItem::updateOrCreate(['flash_offer_id' => $launch->id, 'product_id' => $products['sauce']->id], ['quantity' => 1, 'original_price' => 3.50, 'offer_price' => null, 'is_free_item' => false]);

        $fixedPrice = FlashOffer::updateOrCreate(
            ['slug' => 'smart-bottle-fixed-price'],
            [
                'title' => $this->tr('Smart Bottle Fixed Price', 'سعر ثابت للزجاجة الذكية'),
                'description' => $this->tr('A fixed price for a limited quantity.', 'سعر محدد لكمية محدودة.'),
                'type' => FlashOffer::TYPE_FIXED_PRICE_QUANTITY,
                'offer_scope' => FlashOffer::SCOPE_PRODUCT,
                'status' => FlashOffer::STATUS_ACTIVE,
                'starts_at' => now()->subHours(6),
                'ends_at' => now()->addDays(7),
                'priority' => 30,
                'fixed_price' => 29.99,
                'max_quantity' => 50,
                'sold_quantity' => 6,
                'free_shipping_scope' => FlashOffer::FREE_SHIPPING_NONE,
            ]
        );
        FlashOfferItem::updateOrCreate(['flash_offer_id' => $fixedPrice->id, 'product_id' => $products['bottle']->id], ['quantity' => 1, 'original_price' => 34.99, 'offer_price' => 29.99, 'is_free_item' => false]);

        $bundle = FlashOffer::updateOrCreate(
            ['slug' => 'family-food-bundle'],
            [
                'title' => $this->tr('Family Food Bundle', 'حزمة العائلة الغذائية'),
                'description' => $this->tr('Bundle multiple products at a fixed total price.', 'حزمة منتجات متعددة بسعر إجمالي محدد.'),
                'type' => FlashOffer::TYPE_BUNDLE_FIXED_PRICE,
                'offer_scope' => FlashOffer::SCOPE_BUNDLE,
                'status' => FlashOffer::STATUS_ACTIVE,
                'starts_at' => now()->subDay(),
                'ends_at' => now()->addDays(10),
                'priority' => 10,
                'fixed_price' => 17.98,
                'max_quantity' => 100,
                'free_shipping' => false,
                'free_shipping_scope' => FlashOffer::FREE_SHIPPING_NONE,
            ]
        );
        FlashOfferItem::updateOrCreate(['flash_offer_id' => $bundle->id, 'product_id' => $products['sandwich']->id], ['quantity' => 2, 'original_price' => 6.99, 'offer_price' => 5.99, 'is_free_item' => false]);
        FlashOfferItem::updateOrCreate(['flash_offer_id' => $bundle->id, 'product_id' => $products['sauce']->id], ['quantity' => 2, 'original_price' => 3.50, 'offer_price' => 3.00, 'is_free_item' => false]);

        $freeShipping = FlashOffer::updateOrCreate(
            ['slug' => 'bulk-rice-free-shipping'],
            [
                'title' => $this->tr('Bulk Rice Free Shipping', 'شحن مجاني لأكياس الرز'),
                'description' => $this->tr('Buy the selected bulk product with free shipping.', 'اشتر المنتج المحدد مع شحن مجاني.'),
                'type' => FlashOffer::TYPE_FREE_SHIPPING_PRODUCT,
                'offer_scope' => FlashOffer::SCOPE_PRODUCT,
                'status' => FlashOffer::STATUS_ACTIVE,
                'starts_at' => now()->subDay(),
                'ends_at' => now()->addDays(21),
                'priority' => 5,
                'free_shipping' => true,
                'free_shipping_scope' => FlashOffer::FREE_SHIPPING_OFFER,
                'max_quantity' => 120,
            ]
        );
        FlashOfferItem::updateOrCreate(['flash_offer_id' => $freeShipping->id, 'product_id' => $products['bulk-rice']->id], ['quantity' => 1, 'original_price' => 29.00, 'offer_price' => null, 'is_free_item' => false]);
    }

    private function seedCustomerData(array $users, array $currencies, array $payments, array $shipping, array $products): void
    {
        $customerAddress = UserAddress::updateOrCreate(['user_id' => $users['customer']->id, 'label' => 'Home'], ['recipient_name' => 'Demo Customer', 'phone' => '+963900000002', 'city_id' => $shipping['damascus']->id, 'address_line' => 'Al Hamra Street', 'building_number' => '12', 'floor' => '2', 'apartment' => '5', 'landmark' => 'Near main market', 'notes' => null, 'is_default' => true, 'is_active' => true]);
        $wholesaleAddress = UserAddress::updateOrCreate(['user_id' => $users['wholesale']->id, 'label' => 'Warehouse'], ['recipient_name' => 'Wholesale Buyer', 'phone' => '+963900000003', 'city_id' => $shipping['aleppo']->id, 'address_line' => 'Industrial Market', 'building_number' => '44', 'floor' => null, 'apartment' => null, 'landmark' => 'Gate 2', 'notes' => 'Call before delivery.', 'is_default' => true, 'is_active' => true]);

        Wishlist::updateOrCreate(['user_id' => $users['customer']->id, 'product_id' => $products['bottle']->id]);
        Wishlist::updateOrCreate(['user_id' => $users['wholesale']->id, 'product_id' => $products['bulk-rice']->id]);

        $cart = Cart::firstOrCreate(['user_id' => $users['customer']->id], ['currency_id' => $currencies['USD']->id]);
        CartItem::updateOrCreate(['cart_id' => $cart->id, 'product_id' => $products['sauce']->id, 'variant_id' => null], ['quantity' => 3, 'unit_price' => 3.50, 'price_type' => 'retail', 'applied_tier_id' => $products['sauce']->priceTiers()->where('type', 'retail')->value('id')]);
        CartItem::updateOrCreate(['cart_id' => $cart->id, 'product_id' => $products['bottle']->id, 'variant_id' => null], ['quantity' => 1, 'unit_price' => 34.99, 'price_type' => 'retail', 'applied_tier_id' => $products['bottle']->priceTiers()->where('type', 'retail')->value('id')]);

        WholesaleApplication::updateOrCreate(
            ['email' => 'market.owner@example.test'],
            [
                'full_name' => 'Pending Wholesale Applicant',
                'phone' => '+963900000099',
                'whatsapp' => '+963900000099',
                'business_name' => 'Demo Market',
                'business_type' => 'Grocery / Food Supplies',
                'city' => 'Damascus',
                'address' => 'Main market street',
                'notes' => 'Interested in weekly wholesale orders.',
                'status' => WholesaleApplication::STATUS_PENDING,
            ]
        );

        Review::updateOrCreate(['product_id' => $products['sandwich']->id, 'user_id' => $users['customer']->id], ['rating' => 5, 'title' => 'Excellent taste', 'comment' => 'Great demo product for testing published reviews.', 'images' => [], 'is_published' => true]);
        Review::updateOrCreate(['product_id' => $products['bottle']->id, 'user_id' => $users['wholesale']->id], ['rating' => 4, 'title' => 'Good wholesale option', 'comment' => 'Useful item for business gifting.', 'images' => [], 'is_published' => false]);

        $orders = [
            ['number' => 'ORD-DEMO-00001', 'user' => $users['customer'], 'address' => $customerAddress, 'payment' => $payments['cod'], 'carrier' => $shipping['alharam'], 'city' => $shipping['damascus'], 'status' => 'pending', 'tracking' => null, 'items' => [['product' => 'sandwich', 'qty' => 2, 'unit' => 6.99]], 'shipping_cost' => 5],
            ['number' => 'ORD-DEMO-00002', 'user' => $users['customer'], 'address' => $customerAddress, 'payment' => $payments['bank'], 'carrier' => $shipping['express'], 'city' => $shipping['damascus'], 'status' => 'processing', 'tracking' => null, 'items' => [['product' => 'bottle', 'qty' => 1, 'unit' => 34.99], ['product' => 'sauce', 'qty' => 4, 'unit' => 3.50]], 'shipping_cost' => 12],
            ['number' => 'ORD-DEMO-00003', 'user' => $users['wholesale'], 'address' => $wholesaleAddress, 'payment' => $payments['manual'], 'carrier' => $shipping['alharam'], 'city' => $shipping['aleppo'], 'status' => 'shipped', 'tracking' => 'TRK-DEMO-00003', 'items' => [['product' => 'bulk-rice', 'qty' => 10, 'unit' => 24.00], ['product' => 'spices', 'qty' => 25, 'unit' => 9.25]], 'shipping_cost' => 8],
            ['number' => 'ORD-DEMO-00004', 'user' => $users['customer'], 'address' => $customerAddress, 'payment' => $payments['cod'], 'carrier' => $shipping['alharam'], 'city' => $shipping['damascus'], 'status' => 'delivered', 'tracking' => 'TRK-DEMO-00004', 'items' => [['product' => 'sandwich', 'qty' => 1, 'unit' => 6.99], ['product' => 'sauce', 'qty' => 2, 'unit' => 3.50]], 'shipping_cost' => 5],
            ['number' => 'ORD-DEMO-00005', 'user' => $users['customer'], 'address' => $customerAddress, 'payment' => $payments['manual'], 'carrier' => $shipping['alharam'], 'city' => $shipping['damascus'], 'status' => 'cancelled', 'tracking' => null, 'items' => [['product' => 'bottle', 'qty' => 1, 'unit' => 34.99]], 'shipping_cost' => 5],
        ];

        foreach ($orders as $data) {
            $subtotal = collect($data['items'])->sum(fn ($item) => $item['qty'] * $item['unit']);
            $discount = $data['number'] === 'ORD-DEMO-00003' ? 25 : 0;
            $paymentFee = (float) $data['payment']->fee;
            $total = $subtotal + $data['shipping_cost'] + $paymentFee - $discount;

            $order = Order::updateOrCreate(['order_number' => $data['number']], [
                'user_id' => $data['user']->id,
                'currency_id' => $currencies['USD']->id,
                'payment_method_id' => $data['payment']->id,
                'shipping_address_id' => null,
                'billing_address_id' => null,
                'shipping_city_id' => $data['city']->id,
                'shipping_city_name' => $data['city']->name,
                'shipping_carrier_id' => $data['carrier']->id,
                'shipping_carrier_name' => $data['carrier']->name,
                'shipping_recipient_name' => $data['address']->recipient_name,
                'shipping_phone' => $data['address']->phone,
                'shipping_address_line' => $data['address']->address_line,
                'shipping_building_number' => $data['address']->building_number,
                'shipping_floor' => $data['address']->floor,
                'shipping_apartment' => $data['address']->apartment,
                'shipping_landmark' => $data['address']->landmark,
                'shipping_notes' => $data['address']->notes,
                'subtotal' => $subtotal,
                'shipping_cost' => $data['shipping_cost'],
                'shipping_weight' => collect($data['items'])->sum(fn ($item) => $item['qty'] * (float) $products[$item['product']]->weight),
                'shipping_delivery_time' => '24-48h',
                'shipping_address_text' => $data['city']->country.' / '.$data['city']->name.' / '.$data['address']->address_line,
                'is_free_shipping' => false,
                'discount_amount' => $discount,
                'payment_fee' => $paymentFee,
                'total' => $total,
                'status' => $data['status'],
                'tracking_number' => $data['tracking'],
                'notes' => 'Seeded demo order for admin testing.',
                'customer_phone' => $data['address']->phone,
                'customer_whatsapp' => $data['address']->phone,
                'shipping_country' => $data['city']->country,
                'shipping_city' => $data['city']->name,
                'shipping_town' => null,
                'shipping_street' => $data['address']->address_line,
                'paid_at' => in_array($data['status'], ['processing', 'shipped', 'delivered'], true) ? now()->subDays(2) : null,
                'shipped_at' => in_array($data['status'], ['shipped', 'delivered'], true) ? now()->subDay() : null,
                'delivered_at' => $data['status'] === 'delivered' ? now() : null,
                'cancelled_at' => $data['status'] === 'cancelled' ? now()->subHours(4) : null,
            ]);

            foreach ($data['items'] as $item) {
                $product = $products[$item['product']];
                $priceType = (float) $item['unit'] < (float) $product->retail_price ? 'wholesale' : 'retail';
                $tier = $product->priceTiers()
                    ->where('type', $priceType)
                    ->where('min_quantity', '<=', $item['qty'])
                    ->orderByDesc('min_quantity')
                    ->first();

                OrderItem::updateOrCreate(['order_id' => $order->id, 'product_id' => $product->id, 'variant_id' => null], [
                    'quantity' => $item['qty'],
                    'unit_price' => $item['unit'],
                    'price_type' => $priceType,
                    'applied_tier_id' => $tier?->id,
                    'subtotal' => $item['qty'] * $item['unit'],
                    'total_price' => $item['qty'] * $item['unit'],
                ]);
            }

            Payment::updateOrCreate(['order_id' => $order->id, 'driver' => $data['payment']->type], [
                'payment_method_id' => $data['payment']->id,
                'status' => in_array($data['status'], ['processing', 'shipped', 'delivered'], true) ? 'paid' : ($data['status'] === 'cancelled' ? 'failed' : 'pending'),
                'amount' => $total,
                'transaction_reference' => 'PAY-'.$data['number'],
                'payload' => ['seeded' => true, 'method' => $data['payment']->slug],
            ]);

            OrderStatusHistory::updateOrCreate(['order_id' => $order->id, 'to_status' => $data['status']], [
                'user_id' => $users['admin']->id,
                'from_status' => $data['status'] === 'pending' ? null : 'pending',
                'note' => "Seeded status: {$data['status']}.",
            ]);
        }
    }

    private function seedSettingsAndBanners(): void
    {
        Setting::updateOrCreate(['key' => 'store.name'], ['group' => 'general', 'value' => ['en' => 'AlBaik Store', 'ar' => 'متجر البيك'], 'type' => 'json', 'is_public' => true]);
        Setting::updateOrCreate(['key' => 'store.tagline'], ['group' => 'identity', 'value' => ['en' => 'Premium Market', 'ar' => 'سوق مميز'], 'type' => 'json', 'is_public' => true]);
        Setting::updateOrCreate(['key' => 'store.short_description'], ['group' => 'identity', 'value' => ['en' => 'Original products, competitive prices, and a complete shopping experience for retail and wholesale customers.', 'ar' => 'منتجات أصلية، أسعار منافسة، وتجربة تسوق كاملة لعملاء التجزئة والجملة.'], 'type' => 'json', 'is_public' => true]);
        Setting::updateOrCreate(['key' => 'store.logo'], ['group' => 'identity', 'value' => ['value' => null], 'type' => 'string', 'is_public' => true]);
        Setting::updateOrCreate(['key' => 'store.favicon'], ['group' => 'identity', 'value' => ['value' => null], 'type' => 'string', 'is_public' => true]);
        Setting::updateOrCreate(['key' => 'store.default_product_image'], ['group' => 'identity', 'value' => ['value' => null], 'type' => 'string', 'is_public' => true]);
        Setting::updateOrCreate(['key' => 'store.primary_color'], ['group' => 'identity', 'value' => ['value' => '#111111'], 'type' => 'string', 'is_public' => true]);
        Setting::updateOrCreate(['key' => 'store.primary_hover_color'], ['group' => 'identity', 'value' => ['value' => '#2a2a2a'], 'type' => 'string', 'is_public' => true]);
        Setting::updateOrCreate(['key' => 'store.accent_color'], ['group' => 'identity', 'value' => ['value' => '#d99a16'], 'type' => 'string', 'is_public' => true]);
        Setting::updateOrCreate(['key' => 'store.topbar_color'], ['group' => 'identity', 'value' => ['value' => '#111111'], 'type' => 'string', 'is_public' => true]);
        Setting::updateOrCreate(['key' => 'store.header_bg_color'], ['group' => 'identity', 'value' => ['value' => '#ffffff'], 'type' => 'string', 'is_public' => true]);
        Setting::updateOrCreate(['key' => 'store.nav_bg_color'], ['group' => 'identity', 'value' => ['value' => '#ffffff'], 'type' => 'string', 'is_public' => true]);
        Setting::updateOrCreate(['key' => 'store.body_bg_color'], ['group' => 'identity', 'value' => ['value' => '#fafafa'], 'type' => 'string', 'is_public' => true]);
        Setting::updateOrCreate(['key' => 'store.surface_color'], ['group' => 'identity', 'value' => ['value' => '#ffffff'], 'type' => 'string', 'is_public' => true]);
        Setting::updateOrCreate(['key' => 'store.surface_tint_color'], ['group' => 'identity', 'value' => ['value' => '#f5f6f8'], 'type' => 'string', 'is_public' => true]);
        Setting::updateOrCreate(['key' => 'store.text_color'], ['group' => 'identity', 'value' => ['value' => '#111111'], 'type' => 'string', 'is_public' => true]);
        Setting::updateOrCreate(['key' => 'store.muted_text_color'], ['group' => 'identity', 'value' => ['value' => '#6b7280'], 'type' => 'string', 'is_public' => true]);
        Setting::updateOrCreate(['key' => 'store.border_color'], ['group' => 'identity', 'value' => ['value' => '#e5e7eb'], 'type' => 'string', 'is_public' => true]);
        Setting::updateOrCreate(['key' => 'store.hero_overlay_from'], ['group' => 'identity', 'value' => ['value' => 'rgba(255,255,255,.06)'], 'type' => 'string', 'is_public' => true]);
        Setting::updateOrCreate(['key' => 'store.hero_overlay_to'], ['group' => 'identity', 'value' => ['value' => 'rgba(255,255,255,.42)'], 'type' => 'string', 'is_public' => true]);
        Setting::updateOrCreate(['key' => 'store.default_locale'], ['group' => 'localization', 'value' => ['value' => 'ar'], 'type' => 'string', 'is_public' => true]);
        Setting::updateOrCreate(['key' => 'store.support_email'], ['group' => 'support', 'value' => ['value' => 'support@albaikstore.local'], 'type' => 'string', 'is_public' => true]);
        Setting::updateOrCreate(['key' => 'shipping.default_product_weight'], ['group' => 'shipping', 'value' => ['value' => '0.5'], 'type' => 'number', 'is_public' => false]);
        Setting::updateOrCreate(['key' => 'shipping.enable_free_shipping'], ['group' => 'shipping', 'value' => ['value' => false], 'type' => 'boolean', 'is_public' => false]);
        Setting::updateOrCreate(['key' => 'shipping.global_free_shipping_threshold'], ['group' => 'shipping', 'value' => ['value' => '250'], 'type' => 'number', 'is_public' => false]);
        Setting::updateOrCreate(['key' => 'shipping.calculation_mode'], ['group' => 'shipping', 'value' => ['value' => 'carrier_city_weight'], 'type' => 'string', 'is_public' => false]);
        Setting::updateOrCreate(['key' => 'contact.email'], ['group' => 'contact', 'value' => ['value' => 'support@albaikstore.local'], 'type' => 'string', 'is_public' => true]);
        Setting::updateOrCreate(['key' => 'contact.phone'], ['group' => 'contact', 'value' => ['value' => '+963 900 000 000'], 'type' => 'string', 'is_public' => true]);
        Setting::updateOrCreate(['key' => 'contact.whatsapp'], ['group' => 'contact', 'value' => ['value' => '+963 900 000 000'], 'type' => 'string', 'is_public' => true]);
        Setting::updateOrCreate(['key' => 'contact.address'], ['group' => 'contact', 'value' => ['en' => 'Damascus, Syria', 'ar' => 'دمشق، سوريا'], 'type' => 'json', 'is_public' => true]);
        Setting::updateOrCreate(['key' => 'contact.working_hours'], ['group' => 'contact', 'value' => ['en' => 'Daily from 9:00 to 18:00', 'ar' => 'يومياً من 9:00 إلى 18:00'], 'type' => 'json', 'is_public' => true]);
        Setting::updateOrCreate(['key' => 'contact.map_url'], ['group' => 'contact', 'value' => ['value' => null], 'type' => 'string', 'is_public' => true]);
        Setting::updateOrCreate(['key' => 'social.facebook'], ['group' => 'social', 'value' => ['value' => 'https://facebook.com'], 'type' => 'string', 'is_public' => true]);
        Setting::updateOrCreate(['key' => 'social.instagram'], ['group' => 'social', 'value' => ['value' => 'https://instagram.com'], 'type' => 'string', 'is_public' => true]);
        Setting::updateOrCreate(['key' => 'social.youtube'], ['group' => 'social', 'value' => ['value' => 'https://youtube.com'], 'type' => 'string', 'is_public' => true]);
        Setting::updateOrCreate(['key' => 'orders.low_stock_alert_threshold'], ['group' => 'inventory', 'value' => ['value' => 10], 'type' => 'number', 'is_public' => false]);

        Banner::updateOrCreate(['placement' => 'home', 'sort_order' => 1], [
            'title' => ['ar' => 'عروض الإطلاق', 'en' => 'Launch Offers'],
            'subtitle' => ['ar' => 'عروض التجزئة والجملة جاهزة للتجربة مع دفع يدوي وشحن مرن.', 'en' => 'Retail and wholesale deals are ready for testing with manual payment and flexible shipping.'],
            'eyebrow' => ['ar' => 'متجر إلكتروني مميز', 'en' => 'Premium online store'],
            'primary_button_text' => ['ar' => 'تسوق الآن', 'en' => 'Shop Now'],
            'secondary_button_text' => ['ar' => 'مشاهدة العروض', 'en' => 'View Offers'],
            'title_ar' => 'عروض الإطلاق',
            'title_en' => 'Launch Offers',
            'subtitle_ar' => 'عروض التجزئة والجملة جاهزة للتجربة مع دفع يدوي وشحن مرن.',
            'subtitle_en' => 'Retail and wholesale deals are ready for testing with manual payment and flexible shipping.',
            'eyebrow_ar' => 'متجر إلكتروني مميز',
            'eyebrow_en' => 'Premium online store',
            'primary_button_text_ar' => 'تسوق الآن',
            'primary_button_text_en' => 'Shop Now',
            'secondary_button_text_ar' => 'مشاهدة العروض',
            'secondary_button_text_en' => 'View Offers',
            'image' => 'demo/banners/launch.jpg',
            'url' => '/products',
            'secondary_url' => '/offers',
            'background_color' => '#fff7f7',
            'text_color' => null,
            'is_active' => true,
        ]);
        Banner::updateOrCreate(['placement' => 'home', 'sort_order' => 2], [
            'title' => ['ar' => 'توريدات الجملة', 'en' => 'Wholesale Supplies'],
            'subtitle' => ['ar' => 'منتجات بكميات كبيرة وأسعار خاصة لعملاء الجملة.', 'en' => 'Bulk products with special prices for wholesale customers.'],
            'eyebrow' => ['ar' => 'حلول تجارية', 'en' => 'Business supplies'],
            'primary_button_text' => ['ar' => 'تصفح المنتجات', 'en' => 'Browse Products'],
            'secondary_button_text' => ['ar' => 'تواصل معنا', 'en' => 'Contact Us'],
            'title_ar' => 'توريدات الجملة',
            'title_en' => 'Wholesale Supplies',
            'subtitle_ar' => 'منتجات بكميات كبيرة وأسعار خاصة لعملاء الجملة.',
            'subtitle_en' => 'Bulk products with special prices for wholesale customers.',
            'eyebrow_ar' => 'حلول تجارية',
            'eyebrow_en' => 'Business supplies',
            'primary_button_text_ar' => 'تصفح المنتجات',
            'primary_button_text_en' => 'Browse Products',
            'secondary_button_text_ar' => 'تواصل معنا',
            'secondary_button_text_en' => 'Contact Us',
            'image' => 'demo/banners/wholesale.jpg',
            'url' => '/products',
            'secondary_url' => '/contact',
            'background_color' => '#f8fafc',
            'text_color' => null,
            'is_active' => true,
        ]);
        Banner::updateOrCreate(['placement' => Banner::PLACEMENT_HOME_AFTER_HERO, 'sort_order' => 1], [
            'title' => ['ar' => 'تسوق أحدث الأجهزة بثقة', 'en' => 'Shop the latest devices with confidence'],
            'subtitle' => ['ar' => 'بنر تسويقي يظهر بعد القسم الرئيسي ويمكن تغييره من لوحة التحكم.', 'en' => 'A marketing banner displayed after the hero and managed from the admin panel.'],
            'eyebrow' => ['ar' => 'مختارات المتجر', 'en' => 'Store picks'],
            'primary_button_text' => ['ar' => 'تصفح المنتجات', 'en' => 'Browse Products'],
            'secondary_button_text' => ['ar' => 'العروض', 'en' => 'Offers'],
            'image' => null,
            'url' => '/products',
            'secondary_url' => '/offers',
            'background_color' => '#ffffff',
            'is_active' => true,
        ]);
        Banner::updateOrCreate(['placement' => Banner::PLACEMENT_PRODUCTS_TOP, 'sort_order' => 1], [
            'title' => ['ar' => 'توريدات الجملة', 'en' => 'Wholesale Supplies'],
            'subtitle' => ['ar' => 'منتجات بكميات كبيرة وأسعار خاصة.', 'en' => 'Bulk products with special prices.'],
            'eyebrow' => ['ar' => 'صفحة المنتجات', 'en' => 'Products page'],
            'primary_button_text' => ['ar' => 'تسوق الآن', 'en' => 'Shop Now'],
            'image' => 'demo/banners/wholesale.jpg',
            'url' => '/products',
            'background_color' => '#f5f6f8',
            'is_active' => true,
        ]);
        Banner::updateOrCreate(['placement' => Banner::PLACEMENT_OFFERS_TOP, 'sort_order' => 1], [
            'title' => ['ar' => 'عروض مميزة لفترة محدودة', 'en' => 'Limited-time featured offers'],
            'subtitle' => ['ar' => 'استخدم البنرات لإبراز الحملات والعروض الخاصة.', 'en' => 'Use banners to highlight campaigns and special deals.'],
            'eyebrow' => ['ar' => 'صفحة العروض', 'en' => 'Offers page'],
            'primary_button_text' => ['ar' => 'مشاهدة العروض', 'en' => 'View Offers'],
            'url' => '/offers',
            'background_color' => '#111111',
            'text_color' => '#ffffff',
            'is_active' => true,
        ]);
        Banner::updateOrCreate(['placement' => Banner::PLACEMENT_CATEGORIES_TOP, 'sort_order' => 1], [
            'title' => ['ar' => 'اختر القسم المناسب بسرعة', 'en' => 'Find the right category faster'],
            'subtitle' => ['ar' => 'بنر خاص بصفحات التصنيفات.', 'en' => 'A banner dedicated to category pages.'],
            'eyebrow' => ['ar' => 'التصنيفات', 'en' => 'Categories'],
            'primary_button_text' => ['ar' => 'كل المنتجات', 'en' => 'All Products'],
            'url' => '/products',
            'background_color' => '#ffffff',
            'is_active' => true,
        ]);
        Banner::updateOrCreate(['placement' => Banner::PLACEMENT_BRANDS_TOP, 'sort_order' => 1], [
            'title' => ['ar' => 'علامات تجارية موثوقة', 'en' => 'Trusted brands'],
            'subtitle' => ['ar' => 'اعرض رسالة تسويقية أعلى صفحات العلامات التجارية.', 'en' => 'Display a marketing message above brand pages.'],
            'eyebrow' => ['ar' => 'العلامات التجارية', 'en' => 'Brands'],
            'primary_button_text' => ['ar' => 'استعرض العلامات', 'en' => 'Browse Brands'],
            'url' => '/brands',
            'background_color' => '#f5f6f8',
            'is_active' => true,
        ]);
    }

    private function tr(string $en, string $ar): array
    {
        return [
            'ar' => $ar,
            'en' => $en,
        ];
    }
}
