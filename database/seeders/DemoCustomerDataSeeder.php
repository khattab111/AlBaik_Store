<?php

namespace Database\Seeders;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\City;
use App\Models\Currency;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Review;
use App\Models\ShippingCarrier;
use App\Models\User;
use App\Models\UserAddress;
use App\Models\WholesaleApplication;
use App\Models\Wishlist;
use Illuminate\Database\Seeder;

class DemoCustomerDataSeeder extends Seeder
{
    use SeedsTranslations;

    public function run(): void
    {
        $customer = User::where('email', 'customer@albaik.test')->firstOrFail();
        $wholesale = User::where('email', 'wholesale@albaik.test')->firstOrFail();
        $admin = User::where('email', 'admin@albaik.test')->firstOrFail();
        $usd = Currency::where('code', 'USD')->firstOrFail();
        $cod = PaymentMethod::where('slug', 'cod')->firstOrFail();
        $bank = PaymentMethod::where('slug', 'bank-transfer')->firstOrFail();
        $manual = PaymentMethod::where('slug', 'manual-wallet')->firstOrFail();
        $damascus = City::where('slug', 'damascus')->firstOrFail();
        $aleppo = City::where('slug', 'aleppo')->firstOrFail();
        $alharam = ShippingCarrier::where('slug', 'alharam')->firstOrFail();
        $express = ShippingCarrier::where('slug', 'express-courier')->firstOrFail();

        $customerAddress = UserAddress::updateOrCreate(['user_id' => $customer->id, 'label' => 'Home'], ['recipient_name' => 'Demo Customer', 'phone' => '+963900000002', 'city_id' => $damascus->id, 'address_line' => 'Al Hamra Street', 'building_number' => '12', 'floor' => '2', 'apartment' => '5', 'landmark' => 'Near main market', 'notes' => null, 'is_default' => true, 'is_active' => true]);
        $wholesaleAddress = UserAddress::updateOrCreate(['user_id' => $wholesale->id, 'label' => 'Shop'], ['recipient_name' => 'Wholesale Buyer', 'phone' => '+963900000003', 'city_id' => $aleppo->id, 'address_line' => 'Electronics Market', 'building_number' => '44', 'floor' => null, 'apartment' => null, 'landmark' => 'Gate 2', 'notes' => 'Call before delivery.', 'is_default' => true, 'is_active' => true]);

        $iphone = Product::where('slug', 'apple-iphone-15-pro-max-256gb')->firstOrFail();
        $s24 = Product::where('slug', 'samsung-galaxy-s24-ultra-256gb')->firstOrFail();
        $charger = Product::where('slug', 'anker-20w-usb-c-fast-charger')->firstOrFail();
        $cable = Product::where('slug', 'baseus-usb-c-to-usb-c-cable-1m')->firstOrFail();
        $airpods = Product::where('slug', 'apple-airpods-pro-2-usb-c')->firstOrFail();

        Wishlist::updateOrCreate(['user_id' => $customer->id, 'product_id' => $iphone->id]);
        Wishlist::updateOrCreate(['user_id' => $wholesale->id, 'product_id' => $charger->id]);

        $cart = Cart::firstOrCreate(['user_id' => $customer->id], ['currency_id' => $usd->id]);
        CartItem::updateOrCreate(['cart_id' => $cart->id, 'product_id' => $charger->id, 'variant_id' => null], ['quantity' => 2, 'unit_price' => $charger->retail_price, 'price_type' => 'retail', 'applied_tier_id' => $charger->priceTiers()->where('type', 'retail')->value('id')]);
        CartItem::updateOrCreate(['cart_id' => $cart->id, 'product_id' => $cable->id, 'variant_id' => null], ['quantity' => 3, 'unit_price' => $cable->retail_price, 'price_type' => 'retail', 'applied_tier_id' => $cable->priceTiers()->where('type', 'retail')->value('id')]);

        WholesaleApplication::updateOrCreate(['email' => 'electronics.shop@example.test'], [
            'full_name' => 'Pending Electronics Applicant',
            'phone' => '+963900000099',
            'whatsapp' => '+963900000099',
            'business_name' => 'Demo Electronics Shop',
            'business_type' => 'Phones and accessories',
            'city' => 'Damascus',
            'address' => 'Electronics market street',
            'notes' => 'Interested in weekly wholesale phone accessories orders.',
            'status' => WholesaleApplication::STATUS_PENDING,
        ]);

        Review::updateOrCreate(['product_id' => $iphone->id, 'user_id' => $customer->id], ['rating' => 5, 'title' => $this->tr('Original phone', 'هاتف أصلي'), 'comment' => $this->tr('Great demo product for testing published reviews.', 'منتج تجريبي مناسب لاختبار التقييمات المنشورة.'), 'images' => [], 'is_published' => true]);
        Review::updateOrCreate(['product_id' => $airpods->id, 'user_id' => $wholesale->id], ['rating' => 4, 'title' => $this->tr('Good wholesale option', 'خيار جيد للجملة'), 'comment' => $this->tr('Useful item for accessories shop.', 'منتج مفيد لمحلات الإكسسوارات.'), 'images' => [], 'is_published' => false]);

        $orders = [
            ['number' => 'ORD-ELEC-00001', 'user' => $customer, 'address' => $customerAddress, 'payment' => $cod, 'carrier' => $alharam, 'city' => $damascus, 'status' => 'pending', 'items' => [[$charger, 2, 19.00], [$cable, 2, 8.50]], 'shipping_cost' => 5],
            ['number' => 'ORD-ELEC-00002', 'user' => $customer, 'address' => $customerAddress, 'payment' => $bank, 'carrier' => $express, 'city' => $damascus, 'status' => 'processing', 'items' => [[$iphone, 1, 1399.00]], 'shipping_cost' => 8],
            ['number' => 'ORD-ELEC-00003', 'user' => $wholesale, 'address' => $wholesaleAddress, 'payment' => $manual, 'carrier' => $alharam, 'city' => $aleppo, 'status' => 'shipped', 'items' => [[$charger, 25, 15.50], [$cable, 80, 6.25]], 'shipping_cost' => 12],
        ];

        foreach ($orders as $data) {
            $subtotal = collect($data['items'])->sum(fn ($item) => $item[1] * $item[2]);
            $paymentFee = (float) $data['payment']->fee;
            $total = $subtotal + $data['shipping_cost'] + $paymentFee;

            $order = Order::updateOrCreate(['order_number' => $data['number']], [
                'user_id' => $data['user']->id,
                'currency_id' => $usd->id,
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
                'shipping_weight' => collect($data['items'])->sum(fn ($item) => $item[1] * (float) $item[0]->weight),
                'shipping_delivery_time' => '24-48h',
                'shipping_address_text' => $data['city']->country.' / '.$data['city']->name.' / '.$data['address']->address_line,
                'is_free_shipping' => false,
                'discount_amount' => 0,
                'payment_fee' => $paymentFee,
                'total' => $total,
                'status' => $data['status'],
                'tracking_number' => $data['status'] === 'shipped' ? 'TRK-'.$data['number'] : null,
                'notes' => 'Seeded electronics demo order.',
                'customer_phone' => $data['address']->phone,
                'customer_whatsapp' => $data['address']->phone,
                'shipping_country' => $data['city']->country,
                'shipping_city' => $data['city']->name,
                'shipping_town' => null,
                'shipping_street' => $data['address']->address_line,
                'paid_at' => in_array($data['status'], ['processing', 'shipped', 'delivered'], true) ? now()->subDays(2) : null,
                'shipped_at' => in_array($data['status'], ['shipped', 'delivered'], true) ? now()->subDay() : null,
            ]);

            foreach ($data['items'] as [$product, $quantity, $unitPrice]) {
                $tier = $product->priceTiers()->where('min_quantity', '<=', $quantity)->orderByDesc('min_quantity')->first();
                OrderItem::updateOrCreate(['order_id' => $order->id, 'product_id' => $product->id, 'variant_id' => null], [
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'price_type' => $quantity >= $product->wholesale_minimum_quantity ? 'wholesale' : 'retail',
                    'applied_tier_id' => $tier?->id,
                    'subtotal' => $quantity * $unitPrice,
                    'total_price' => $quantity * $unitPrice,
                ]);
            }

            Payment::updateOrCreate(['order_id' => $order->id, 'driver' => $data['payment']->type], [
                'payment_method_id' => $data['payment']->id,
                'status' => in_array($data['status'], ['processing', 'shipped'], true) ? 'paid' : 'pending',
                'amount' => $total,
                'transaction_reference' => 'PAY-'.$data['number'],
                'payload' => ['seeded' => true, 'method' => $data['payment']->slug],
            ]);

            OrderStatusHistory::updateOrCreate(['order_id' => $order->id, 'to_status' => $data['status']], [
                'user_id' => $admin->id,
                'from_status' => $data['status'] === 'pending' ? null : 'pending',
                'note' => 'Seeded status: '.$data['status'],
            ]);
        }
    }
}
