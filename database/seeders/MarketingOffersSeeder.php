<?php

namespace Database\Seeders;

use App\Models\Coupon;
use App\Models\FlashOffer;
use App\Models\FlashOfferItem;
use App\Models\Product;
use Illuminate\Database\Seeder;

class MarketingOffersSeeder extends Seeder
{
    use SeedsTranslations;

    public function run(): void
    {
        Coupon::updateOrCreate(['code' => 'WELCOME10'], ['type' => 'percentage', 'value' => 10, 'minimum_order_amount' => 50, 'starts_at' => now()->subWeek(), 'expires_at' => now()->addMonth(), 'usage_limit' => 500, 'used_count' => 3, 'is_active' => true]);
        Coupon::updateOrCreate(['code' => 'WHOLESALE25'], ['type' => 'fixed', 'value' => 25, 'minimum_order_amount' => 250, 'starts_at' => now()->subDay(), 'expires_at' => now()->addMonths(2), 'usage_limit' => 100, 'used_count' => 1, 'is_active' => true]);

        $iphone = Product::where('slug', 'apple-iphone-15-pro-max-256gb')->firstOrFail();
        $s24 = Product::where('slug', 'samsung-galaxy-s24-ultra-256gb')->firstOrFail();
        $charger = Product::where('slug', 'anker-20w-usb-c-fast-charger')->firstOrFail();
        $cable = Product::where('slug', 'baseus-usb-c-to-usb-c-cable-1m')->firstOrFail();
        $airpods = Product::where('slug', 'apple-airpods-pro-2-usb-c')->firstOrFail();
        $powerbank = Product::where('slug', 'anker-power-bank-10000mah-20w')->firstOrFail();

        $percentage = FlashOffer::updateOrCreate(['slug' => 'smartphones-13-percent'], [
            'title' => $this->tr('13% Off Selected Smartphones', 'خصم 13% على هواتف مختارة'),
            'description' => $this->tr('Limited percentage discount on premium phones.', 'خصم محدود على هواتف مختارة.'),
            'type' => FlashOffer::TYPE_PERCENTAGE_DISCOUNT,
            'offer_scope' => FlashOffer::SCOPE_PRODUCT,
            'audience' => FlashOffer::AUDIENCE_RETAIL,
            'status' => FlashOffer::STATUS_ACTIVE,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDays(7),
            'priority' => 30,
            'discount_type' => 'percentage',
            'discount_value' => 13,
            'max_quantity' => 80,
            'sold_quantity' => 12,
            'free_shipping_scope' => FlashOffer::FREE_SHIPPING_NONE,
        ]);
        FlashOfferItem::updateOrCreate(['flash_offer_id' => $percentage->id, 'product_id' => $iphone->id], ['quantity' => 1, 'original_price' => $iphone->retail_price, 'offer_price' => null, 'is_free_item' => false]);
        FlashOfferItem::updateOrCreate(['flash_offer_id' => $percentage->id, 'product_id' => $s24->id], ['quantity' => 1, 'original_price' => $s24->retail_price, 'offer_price' => null, 'is_free_item' => false]);

        $fixedQuantity = FlashOffer::updateOrCreate(['slug' => 'anker-charger-first-50'], [
            'title' => $this->tr('First 50 Anker Chargers For $15', 'أول 50 شاحن أنكر بسعر 15$'),
            'description' => $this->tr('Fixed price for limited quantity.', 'سعر ثابت لكمية محدودة.'),
            'type' => FlashOffer::TYPE_FIXED_PRICE_QUANTITY,
            'offer_scope' => FlashOffer::SCOPE_PRODUCT,
            'audience' => FlashOffer::AUDIENCE_BOTH,
            'status' => FlashOffer::STATUS_ACTIVE,
            'starts_at' => now()->subHours(6),
            'ends_at' => now()->addDays(5),
            'priority' => 40,
            'discount_type' => 'fixed_price',
            'discount_value' => null,
            'fixed_price' => 15.00,
            'max_quantity' => 50,
            'sold_quantity' => 9,
            'free_shipping_scope' => FlashOffer::FREE_SHIPPING_NONE,
        ]);
        FlashOfferItem::updateOrCreate(['flash_offer_id' => $fixedQuantity->id, 'product_id' => $charger->id], ['quantity' => 1, 'original_price' => $charger->retail_price, 'offer_price' => 15.00, 'is_free_item' => false]);

        $bundle = FlashOffer::updateOrCreate(['slug' => 'fast-charging-bundle'], [
            'title' => $this->tr('Fast Charging Bundle', 'حزمة الشحن السريع'),
            'description' => $this->tr('Charger + cable + power bank for one fixed bundle price.', 'شاحن + كابل + باور بانك بسعر حزمة ثابت.'),
            'type' => FlashOffer::TYPE_BUNDLE_FIXED_PRICE,
            'offer_scope' => FlashOffer::SCOPE_BUNDLE,
            'audience' => FlashOffer::AUDIENCE_BOTH,
            'status' => FlashOffer::STATUS_ACTIVE,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDays(14),
            'priority' => 50,
            'discount_type' => 'bundle_price',
            'discount_value' => null,
            'fixed_price' => 55.00,
            'max_quantity' => 100,
            'sold_quantity' => 17,
            'free_shipping_scope' => FlashOffer::FREE_SHIPPING_OFFER,
        ]);
        FlashOfferItem::updateOrCreate(['flash_offer_id' => $bundle->id, 'product_id' => $charger->id], ['quantity' => 1, 'original_price' => $charger->retail_price, 'offer_price' => null, 'is_free_item' => false]);
        FlashOfferItem::updateOrCreate(['flash_offer_id' => $bundle->id, 'product_id' => $cable->id], ['quantity' => 2, 'original_price' => $cable->retail_price, 'offer_price' => null, 'is_free_item' => false]);
        FlashOfferItem::updateOrCreate(['flash_offer_id' => $bundle->id, 'product_id' => $powerbank->id], ['quantity' => 1, 'original_price' => $powerbank->retail_price, 'offer_price' => null, 'is_free_item' => false]);

        $buyXGetY = FlashOffer::updateOrCreate(['slug' => 'buy-two-cables-get-one'], [
            'title' => $this->tr('Buy 2 Cables Get 1 Free', 'اشترِ كابلين واحصل على الثالث مجاناً'),
            'description' => $this->tr('Wholesale-friendly accessory offer.', 'عرض مناسب لإكسسوارات الجملة.'),
            'type' => FlashOffer::TYPE_BUY_X_GET_Y,
            'offer_scope' => FlashOffer::SCOPE_PRODUCT,
            'audience' => FlashOffer::AUDIENCE_WHOLESALE,
            'status' => FlashOffer::STATUS_ACTIVE,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDays(10),
            'priority' => 20,
            'discount_type' => 'buy_x_get_y',
            'discount_value' => null,
            'max_quantity' => 200,
            'sold_quantity' => 30,
            'free_shipping_scope' => FlashOffer::FREE_SHIPPING_NONE,
        ]);
        FlashOfferItem::updateOrCreate(['flash_offer_id' => $buyXGetY->id, 'product_id' => $cable->id], ['quantity' => 2, 'original_price' => $cable->retail_price, 'offer_price' => null, 'is_free_item' => false]);
        FlashOfferItem::updateOrCreate(['flash_offer_id' => $buyXGetY->id, 'product_id' => $cable->id, 'is_free_item' => true], ['quantity' => 1, 'original_price' => $cable->retail_price, 'offer_price' => 0, 'is_free_item' => true]);

        $freeShipping = FlashOffer::updateOrCreate(['slug' => 'free-shipping-airpods'], [
            'title' => $this->tr('Free Shipping on AirPods Pro 2', 'توصيل مجاني على AirPods Pro 2'),
            'description' => $this->tr('Free shipping for this product only.', 'توصيل مجاني لهذا المنتج فقط.'),
            'type' => FlashOffer::TYPE_FREE_SHIPPING_PRODUCT,
            'offer_scope' => FlashOffer::SCOPE_PRODUCT,
            'audience' => FlashOffer::AUDIENCE_RETAIL,
            'status' => FlashOffer::STATUS_ACTIVE,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDays(12),
            'priority' => 10,
            'discount_type' => 'free_shipping',
            'discount_value' => null,
            'max_quantity' => 100,
            'sold_quantity' => 8,
            'free_shipping_scope' => FlashOffer::FREE_SHIPPING_OFFER,
        ]);
        FlashOfferItem::updateOrCreate(['flash_offer_id' => $freeShipping->id, 'product_id' => $airpods->id], ['quantity' => 1, 'original_price' => $airpods->retail_price, 'offer_price' => null, 'is_free_item' => false]);
    }
}
