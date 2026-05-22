<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Banner;
use App\Models\Brand;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Currency;
use App\Models\FlashSale;
use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\Review;
use App\Models\Setting;
use App\Models\ShippingMethod;
use App\Models\ShippingRule;
use App\Models\ShippingZone;
use App\Models\Supplier;
use App\Models\Tag;
use App\Models\User;
use App\Models\Warehouse;
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
                'email' => 'admin@albaikstore.local',
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
            'cod' => PaymentMethod::updateOrCreate(['slug' => 'cod'], ['name' => 'Cash on Delivery', 'type' => 'cod', 'description' => 'Collect payment when the order is delivered.', 'image' => 'demo/payments/cod.png', 'wallet_url' => null, 'barcode_image' => null, 'settings' => [], 'fee' => 0, 'is_active' => true]),
            'bank' => PaymentMethod::updateOrCreate(['slug' => 'bank-transfer'], ['name' => 'Bank Transfer', 'type' => 'bank_transfer', 'description' => 'Customer transfers to company bank account and uploads receipt.', 'image' => 'demo/payments/bank.png', 'wallet_url' => 'IBAN: TR00 0000 0000 0000 0000 0000 00', 'barcode_image' => 'demo/payments/bank-qr.png', 'settings' => ['bank_name' => 'Qarid Demo Bank', 'iban' => 'TR00 0000 0000 0000 0000 0000 00'], 'fee' => 0, 'is_active' => true]),
            'manual' => PaymentMethod::updateOrCreate(['slug' => 'manual'], ['name' => 'Manual Wallet', 'type' => 'manual', 'description' => 'Customer pays to wallet, then uploads payment receipt for admin review.', 'image' => 'demo/payments/wallet.png', 'wallet_url' => 'https://wallet.example/pay/albaik-store', 'barcode_image' => 'demo/payments/wallet-qr.png', 'settings' => ['instructions' => 'Upload receipt or contact support.'], 'fee' => 1.50, 'is_active' => true]),
        ];
    }

    private function seedShipping(): array
    {
        $standard = ShippingMethod::updateOrCreate(['slug' => 'standard'], ['name' => 'Standard Shipping', 'type' => 'flat_rate', 'description' => 'Delivery within 5-7 business days.', 'zone' => 'global', 'cost' => 10.00, 'free_shipping_minimum' => 100.00, 'rules' => [], 'is_active' => true]);
        $express = ShippingMethod::updateOrCreate(['slug' => 'express'], ['name' => 'Express Shipping', 'type' => 'rule_based', 'description' => 'Fast delivery for selected cities.', 'zone' => 'city', 'cost' => 18.00, 'free_shipping_minimum' => 200.00, 'rules' => [], 'is_active' => true]);

        $damascus = ShippingZone::updateOrCreate(['name' => 'Damascus - City Center'], ['country' => 'Syria', 'city' => 'Damascus', 'town' => 'City Center', 'is_active' => true]);
        $aleppo = ShippingZone::updateOrCreate(['name' => 'Aleppo - Industrial'], ['country' => 'Syria', 'city' => 'Aleppo', 'town' => 'Industrial', 'is_active' => true]);
        $turkey = ShippingZone::updateOrCreate(['name' => 'Turkey'], ['country' => 'Turkey', 'city' => null, 'town' => null, 'is_active' => true]);

        $rules = [
            [$standard, $damascus, 0, null, null, 5, 5],
            [$standard, $aleppo, 0, null, null, 5, 8],
            [$express, $damascus, 0, null, null, 3, 12],
            [$express, $turkey, 100, null, null, 10, 24],
        ];

        foreach ($rules as [$method, $zone, $subtotal, $minQuantity, $maxQuantity, $maxWeight, $cost]) {
            ShippingRule::updateOrCreate([
                'shipping_method_id' => $method->id,
                'shipping_zone_id' => $zone->id,
                'min_subtotal' => $subtotal,
            ], [
                'min_quantity' => $minQuantity,
                'max_quantity' => $maxQuantity,
                'min_weight' => null,
                'max_weight' => $maxWeight,
                'calculation_type' => $method->slug === 'express' ? 'weight' : 'fixed',
                'cost' => $cost,
                'cost_per_kg' => $method->slug === 'express' ? 2.50 : 0,
                'is_free' => false,
                'is_active' => true,
            ]);
        }

        return compact('standard', 'express', 'damascus', 'aleppo', 'turkey');
    }

    private function seedCatalog(): array
    {
        $brands = [
            'albaik' => Brand::updateOrCreate(['slug' => 'albaik'], ['name' => 'AlBaik', 'description' => 'Private label products.', 'logo' => 'demo/brands/albaik.png', 'status' => true]),
            'qarid' => Brand::updateOrCreate(['slug' => 'qarid-select'], ['name' => 'Qarid Select', 'description' => 'Selected partner products.', 'logo' => 'demo/brands/qarid-select.png', 'status' => true]),
            'levant' => Brand::updateOrCreate(['slug' => 'levant-foods'], ['name' => 'Levant Foods', 'description' => 'Regional food supplier brand.', 'logo' => 'demo/brands/levant-foods.png', 'status' => true]),
        ];

        $suppliers = [
            'main' => Supplier::updateOrCreate(['slug' => 'albaik-main'], ['name' => 'AlBaik Main Supplier', 'email' => 'supply@albaikstore.local', 'phone' => '+963111111111', 'address' => 'Damascus logistics hub', 'is_active' => true]),
            'food' => Supplier::updateOrCreate(['slug' => 'levant-foods-supplier'], ['name' => 'Levant Foods Supplier', 'email' => 'orders@levant.example', 'phone' => '+963222222222', 'address' => 'Aleppo warehouse', 'is_active' => true]),
            'tech' => Supplier::updateOrCreate(['slug' => 'smart-goods-supplier'], ['name' => 'Smart Goods Supplier', 'email' => 'sales@smartgoods.example', 'phone' => '+905551112233', 'address' => 'Istanbul trade zone', 'is_active' => true]),
        ];

        $categories = [
            'food' => Category::updateOrCreate(['slug' => 'food'], ['name' => 'Food', 'description' => 'Premium food products.', 'parent_id' => null, 'status' => true]),
            'electronics' => Category::updateOrCreate(['slug' => 'electronics'], ['name' => 'Electronics', 'description' => 'Smart devices and accessories.', 'parent_id' => null, 'status' => true]),
            'bulk' => Category::updateOrCreate(['slug' => 'bulk-supplies'], ['name' => 'Bulk Supplies', 'description' => 'Wholesale packs and business supplies.', 'parent_id' => null, 'status' => true]),
        ];

        $categories['sandwiches'] = Category::updateOrCreate(['slug' => 'sandwiches'], ['name' => 'Sandwiches', 'description' => 'Fresh and frozen sandwiches.', 'parent_id' => $categories['food']->id, 'status' => true]);
        $categories['sauces'] = Category::updateOrCreate(['slug' => 'sauces'], ['name' => 'Sauces', 'description' => 'Signature sauces.', 'parent_id' => $categories['food']->id, 'status' => true]);
        $categories['drinkware'] = Category::updateOrCreate(['slug' => 'drinkware'], ['name' => 'Drinkware', 'description' => 'Bottles and cups.', 'parent_id' => $categories['electronics']->id, 'status' => true]);

        $tags = [
            'popular' => Tag::updateOrCreate(['slug' => 'popular'], ['name' => 'Popular', 'status' => true]),
            'wholesale' => Tag::updateOrCreate(['slug' => 'wholesale'], ['name' => 'Wholesale', 'status' => true]),
            'new' => Tag::updateOrCreate(['slug' => 'new-arrival'], ['name' => 'New Arrival', 'status' => true]),
            'featured' => Tag::updateOrCreate(['slug' => 'featured'], ['name' => 'Featured', 'status' => true]),
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
        ];

        foreach ($products as $data) {
            $product = Product::updateOrCreate(['sku' => $data['sku']], [
                'name' => $data['name'],
                'slug' => $data['slug'],
                'brand_id' => $catalog['brands'][$data['brand']]->id,
                'supplier_id' => $catalog['suppliers'][$data['supplier']]->id,
                'category_id' => $catalog['categories'][$data['category']]->id,
                'short_description' => "Demo short description for {$data['name']}.",
                'description' => "Detailed demo description for {$data['name']} with retail and wholesale use cases.",
                'retail_price' => $data['retail_price'],
                'wholesale_price' => $data['wholesale_price'],
                'wholesale_minimum_quantity' => $data['wholesale_minimum_quantity'],
                'stock_quantity' => $data['stock_quantity'],
                'weight' => $data['weight'],
                'low_stock_threshold' => 10,
                'status' => true,
                'is_featured' => $data['is_featured'],
                'seo_title' => $data['name'].' | AlBaik Store',
                'seo_description' => "Buy {$data['name']} from AlBaik Store.",
            ]);

            $product->tags()->sync(collect($data['tags'])->map(fn ($tag) => $catalog['tags'][$tag]->id)->all());

            ProductImage::updateOrCreate(['product_id' => $product->id, 'path' => "demo/products/{$data['slug']}-main.jpg"], ['alt_text' => $data['name'], 'is_primary' => true]);
            ProductImage::updateOrCreate(['product_id' => $product->id, 'path' => "demo/products/{$data['slug']}-gallery.jpg"], ['alt_text' => $data['name'].' gallery', 'is_primary' => false]);

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

        $launch = FlashSale::updateOrCreate(['slug' => 'launch-offers'], ['name' => 'Launch Offers', 'starts_at' => now()->subDay(), 'ends_at' => now()->addDays(14), 'is_active' => true]);
        $launch->products()->sync([
            $products['sandwich']->id => ['discount_type' => 'percentage', 'discount_value' => 15, 'quantity_limit' => 200, 'sold_count' => 18],
            $products['bottle']->id => ['discount_type' => 'fixed', 'discount_value' => 5, 'quantity_limit' => 50, 'sold_count' => 6],
            $products['sauce']->id => ['discount_type' => 'percentage', 'discount_value' => 10, 'quantity_limit' => 300, 'sold_count' => 44],
        ]);
    }

    private function seedCustomerData(array $users, array $currencies, array $payments, array $shipping, array $products): void
    {
        $customerAddress = Address::updateOrCreate(['user_id' => $users['customer']->id, 'label' => 'Home'], ['country' => 'Syria', 'city' => 'Damascus', 'town' => 'City Center', 'state' => 'Damascus', 'street' => 'Al Hamra Street', 'postal_code' => '00000', 'phone' => '+963900000002', 'whatsapp' => '+963900000002', 'is_default' => true]);
        $wholesaleAddress = Address::updateOrCreate(['user_id' => $users['wholesale']->id, 'label' => 'Warehouse'], ['country' => 'Syria', 'city' => 'Aleppo', 'town' => 'Industrial', 'state' => 'Aleppo', 'street' => 'Industrial Market', 'postal_code' => '00000', 'phone' => '+963900000003', 'whatsapp' => '+963900000003', 'is_default' => true]);

        Wishlist::updateOrCreate(['user_id' => $users['customer']->id, 'product_id' => $products['bottle']->id]);
        Wishlist::updateOrCreate(['user_id' => $users['wholesale']->id, 'product_id' => $products['bulk-rice']->id]);

        $cart = Cart::firstOrCreate(['user_id' => $users['customer']->id], ['currency_id' => $currencies['USD']->id]);
        CartItem::updateOrCreate(['cart_id' => $cart->id, 'product_id' => $products['sauce']->id, 'variant_id' => null], ['quantity' => 3, 'unit_price' => 3.50]);
        CartItem::updateOrCreate(['cart_id' => $cart->id, 'product_id' => $products['bottle']->id, 'variant_id' => null], ['quantity' => 1, 'unit_price' => 29.99]);

        Review::updateOrCreate(['product_id' => $products['sandwich']->id, 'user_id' => $users['customer']->id], ['rating' => 5, 'title' => 'Excellent taste', 'comment' => 'Great demo product for testing published reviews.', 'images' => [], 'is_published' => true]);
        Review::updateOrCreate(['product_id' => $products['bottle']->id, 'user_id' => $users['wholesale']->id], ['rating' => 4, 'title' => 'Good wholesale option', 'comment' => 'Useful item for business gifting.', 'images' => [], 'is_published' => false]);

        $orders = [
            ['number' => 'ORD-DEMO-00001', 'user' => $users['customer'], 'address' => $customerAddress, 'payment' => $payments['cod'], 'shipping' => $shipping['standard'], 'status' => 'pending', 'tracking' => null, 'items' => [['product' => 'sandwich', 'qty' => 2, 'unit' => 6.99]], 'shipping_cost' => 5],
            ['number' => 'ORD-DEMO-00002', 'user' => $users['customer'], 'address' => $customerAddress, 'payment' => $payments['bank'], 'shipping' => $shipping['express'], 'status' => 'processing', 'tracking' => null, 'items' => [['product' => 'bottle', 'qty' => 1, 'unit' => 34.99], ['product' => 'sauce', 'qty' => 4, 'unit' => 3.50]], 'shipping_cost' => 12],
            ['number' => 'ORD-DEMO-00003', 'user' => $users['wholesale'], 'address' => $wholesaleAddress, 'payment' => $payments['manual'], 'shipping' => $shipping['standard'], 'status' => 'shipped', 'tracking' => 'TRK-DEMO-00003', 'items' => [['product' => 'bulk-rice', 'qty' => 10, 'unit' => 24.00], ['product' => 'spices', 'qty' => 25, 'unit' => 9.25]], 'shipping_cost' => 8],
            ['number' => 'ORD-DEMO-00004', 'user' => $users['customer'], 'address' => $customerAddress, 'payment' => $payments['cod'], 'shipping' => $shipping['standard'], 'status' => 'delivered', 'tracking' => 'TRK-DEMO-00004', 'items' => [['product' => 'sandwich', 'qty' => 1, 'unit' => 6.99], ['product' => 'sauce', 'qty' => 2, 'unit' => 3.50]], 'shipping_cost' => 5],
            ['number' => 'ORD-DEMO-00005', 'user' => $users['customer'], 'address' => $customerAddress, 'payment' => $payments['manual'], 'shipping' => $shipping['standard'], 'status' => 'cancelled', 'tracking' => null, 'items' => [['product' => 'bottle', 'qty' => 1, 'unit' => 34.99]], 'shipping_cost' => 5],
        ];

        foreach ($orders as $data) {
            $subtotal = collect($data['items'])->sum(fn ($item) => $item['qty'] * $item['unit']);
            $discount = $data['number'] === 'ORD-DEMO-00003' ? 25 : 0;
            $paymentFee = (float) $data['payment']->fee;
            $total = $subtotal + $data['shipping_cost'] + $paymentFee - $discount;

            $order = Order::updateOrCreate(['order_number' => $data['number']], [
                'user_id' => $data['user']->id,
                'currency_id' => $currencies['USD']->id,
                'shipping_method_id' => $data['shipping']->id,
                'payment_method_id' => $data['payment']->id,
                'shipping_address_id' => $data['address']->id,
                'billing_address_id' => $data['address']->id,
                'subtotal' => $subtotal,
                'shipping_cost' => $data['shipping_cost'],
                'discount_amount' => $discount,
                'payment_fee' => $paymentFee,
                'total' => $total,
                'status' => $data['status'],
                'tracking_number' => $data['tracking'],
                'notes' => 'Seeded demo order for admin testing.',
                'customer_phone' => $data['address']->phone,
                'customer_whatsapp' => $data['address']->whatsapp,
                'shipping_country' => $data['address']->country,
                'shipping_city' => $data['address']->city,
                'shipping_town' => $data['address']->town,
                'shipping_street' => $data['address']->street,
                'paid_at' => in_array($data['status'], ['processing', 'shipped', 'delivered'], true) ? now()->subDays(2) : null,
                'shipped_at' => in_array($data['status'], ['shipped', 'delivered'], true) ? now()->subDay() : null,
                'delivered_at' => $data['status'] === 'delivered' ? now() : null,
                'cancelled_at' => $data['status'] === 'cancelled' ? now()->subHours(4) : null,
            ]);

            foreach ($data['items'] as $item) {
                OrderItem::updateOrCreate(['order_id' => $order->id, 'product_id' => $products[$item['product']]->id, 'variant_id' => null], [
                    'quantity' => $item['qty'],
                    'unit_price' => $item['unit'],
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
        Setting::updateOrCreate(['key' => 'store.default_locale'], ['group' => 'localization', 'value' => ['value' => 'ar'], 'type' => 'string', 'is_public' => true]);
        Setting::updateOrCreate(['key' => 'store.support_email'], ['group' => 'support', 'value' => ['value' => 'support@albaikstore.local'], 'type' => 'string', 'is_public' => true]);
        Setting::updateOrCreate(['key' => 'orders.low_stock_alert_threshold'], ['group' => 'inventory', 'value' => ['value' => 10], 'type' => 'number', 'is_public' => false]);

        Banner::updateOrCreate(['placement' => 'home', 'sort_order' => 1], ['title' => 'Launch Offers', 'subtitle' => 'Retail and wholesale deals are ready for testing.', 'image' => 'demo/banners/launch.jpg', 'url' => '/admin/flash-sales', 'is_active' => true]);
        Banner::updateOrCreate(['placement' => 'shop', 'sort_order' => 2], ['title' => 'Wholesale Supplies', 'subtitle' => 'Bulk products with special prices.', 'image' => 'demo/banners/wholesale.jpg', 'url' => '/admin/products', 'is_active' => true]);
    }
}
