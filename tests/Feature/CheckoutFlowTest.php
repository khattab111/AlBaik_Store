<?php

namespace Tests\Feature;

use App\Actions\Orders\CreateOrderFromCart;
use App\Data\CheckoutData;
use App\Models\City;
use App\Models\Currency;
use App\Models\FlashOffer;
use App\Models\FlashOfferItem;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ShippingCarrier;
use App\Models\ShippingRate;
use App\Models\UserAddress;
use App\Models\User;
use App\Repositories\CartRepository;
use App\Services\InventoryService;
use App\Services\OfferCartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CheckoutFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_checkout_from_cart(): void
    {
        $user = User::factory()->create(['status' => true]);

        $currency = Currency::create([
            'code' => 'USD',
            'symbol' => '$',
            'name' => 'US Dollar',
            'rate' => 1,
            'is_default' => true,
            'status' => true,
        ]);

        $city = City::create(['name' => ['en' => 'Damascus', 'ar' => 'دمشق'], 'slug' => 'damascus', 'country' => 'Syria', 'is_active' => true]);
        $address = UserAddress::create(['user_id' => $user->id, 'label' => 'Home', 'recipient_name' => 'Test User', 'phone' => '+963900000000', 'city_id' => $city->id, 'address_line' => 'Main Street', 'is_default' => true, 'is_active' => true]);

        $payment = PaymentMethod::create([
            'name' => 'Cash on Delivery',
            'slug' => 'cod',
            'type' => 'cod',
            'fee' => 0,
            'is_active' => true,
        ]);

        $carrier = ShippingCarrier::create(['name' => ['en' => 'Standard', 'ar' => 'قياسي'], 'slug' => 'standard', 'status' => 'active']);
        ShippingRate::create(['shipping_carrier_id' => $carrier->id, 'city_id' => $city->id, 'base_cost' => 5, 'cost_per_kg' => 0, 'is_active' => true]);

        $product = Product::create([
            'name' => 'Test Product',
            'slug' => 'test-product',
            'sku' => 'TEST-001',
            'retail_price' => 10,
            'wholesale_price' => 7,
            'wholesale_minimum_quantity' => 20,
            'stock_quantity' => 10,
            'status' => true,
        ]);

        $cart = app(CartRepository::class)->findForUser($user->id);
        app(CartRepository::class)->addItem($cart, $product, 2);

        $order = app(CreateOrderFromCart::class)->handle(new CheckoutData(
            userId: $user->id,
            paymentMethodId: $payment->id,
            addressMode: 'saved',
            shippingCityId: $city->id,
            shippingCarrierId: $carrier->id,
            userAddressId: $address->id,
        ));

        $this->assertSame('20.00', $order->subtotal);
        $this->assertSame('5.00', $order->shipping_cost);
        $this->assertSame('25.00', $order->total);

        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'currency_id' => $currency->id,
            'total' => 25,
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('payments', ['status' => 'pending', 'amount' => 25]);
        $this->assertDatabaseCount('cart_items', 0);
        $this->assertSame(8, $product->fresh()->stock_quantity);
    }

    public function test_checkout_rejects_insufficient_stock(): void
    {
        $user = User::factory()->create(['status' => true]);

        Currency::create(['code' => 'USD', 'symbol' => '$', 'name' => 'US Dollar', 'rate' => 1, 'is_default' => true, 'status' => true]);

        $product = Product::create([
            'name' => 'Limited Product',
            'slug' => 'limited-product',
            'sku' => 'LIMIT-001',
            'retail_price' => 10,
            'stock_quantity' => 1,
            'status' => true,
        ]);

        $this->expectException(ValidationException::class);

        app(InventoryService::class)->assertAvailable($product, 2);
    }

    public function test_product_free_shipping_does_not_zero_shipping_for_other_cart_items(): void
    {
        $user = User::factory()->create(['status' => true]);

        Currency::create(['code' => 'USD', 'symbol' => '$', 'name' => 'US Dollar', 'rate' => 1, 'is_default' => true, 'status' => true]);

        $city = City::create(['name' => ['en' => 'Idlib', 'ar' => 'إدلب'], 'slug' => 'idlib', 'country' => 'Syria', 'is_active' => true]);
        $address = UserAddress::create(['user_id' => $user->id, 'label' => 'Home', 'recipient_name' => 'Test User', 'phone' => '+963900000001', 'city_id' => $city->id, 'address_line' => 'Main Street', 'is_default' => true, 'is_active' => true]);
        $payment = PaymentMethod::create(['name' => 'Cash on Delivery', 'slug' => 'cod-free-shipping-test', 'type' => 'cod', 'fee' => 0, 'is_active' => true]);
        $carrier = ShippingCarrier::create(['name' => ['en' => 'Carrier', 'ar' => 'شركة'], 'slug' => 'carrier', 'status' => 'active']);

        ShippingRate::create([
            'shipping_carrier_id' => $carrier->id,
            'city_id' => $city->id,
            'base_cost' => 5,
            'cost_per_kg' => 2,
            'is_active' => true,
        ]);

        $freeShippingProduct = Product::create([
            'name' => 'Free Shipping Product',
            'slug' => 'free-shipping-product',
            'sku' => 'FREE-SHIP-001',
            'retail_price' => 10,
            'stock_quantity' => 10,
            'weight' => 10,
            'requires_shipping' => true,
            'free_shipping' => true,
            'status' => true,
        ]);

        $paidShippingProduct = Product::create([
            'name' => 'Paid Shipping Product',
            'slug' => 'paid-shipping-product',
            'sku' => 'PAID-SHIP-001',
            'retail_price' => 10,
            'stock_quantity' => 10,
            'weight' => 1,
            'requires_shipping' => true,
            'free_shipping' => false,
            'status' => true,
        ]);

        $cart = app(CartRepository::class)->findForUser($user->id);
        app(CartRepository::class)->addItem($cart, $freeShippingProduct, 1);
        app(CartRepository::class)->addItem($cart, $paidShippingProduct, 1);

        $order = app(CreateOrderFromCart::class)->handle(new CheckoutData(
            userId: $user->id,
            paymentMethodId: $payment->id,
            addressMode: 'saved',
            shippingCityId: $city->id,
            shippingCarrierId: $carrier->id,
            userAddressId: $address->id,
        ));

        $this->assertSame('7.00', $order->shipping_cost);
        $this->assertSame('1.000', $order->shipping_weight);
        $this->assertFalse((bool) $order->is_free_shipping);
        $this->assertSame('27.00', $order->total);
    }

    public function test_bundle_offer_adds_single_offer_item_to_cart_with_components_snapshot(): void
    {
        $user = User::factory()->create(['status' => true]);

        Currency::create(['code' => 'USD', 'symbol' => '$', 'name' => 'US Dollar', 'rate' => 1, 'is_default' => true, 'status' => true]);

        $sandwich = Product::create([
            'name' => 'Sandwich',
            'slug' => 'sandwich',
            'sku' => 'BUNDLE-SANDWICH',
            'retail_price' => 6.99,
            'stock_quantity' => 20,
            'status' => true,
        ]);

        $sauce = Product::create([
            'name' => 'Sauce',
            'slug' => 'sauce',
            'sku' => 'BUNDLE-SAUCE',
            'retail_price' => 3.50,
            'stock_quantity' => 20,
            'status' => true,
        ]);

        $offer = FlashOffer::create([
            'title' => ['en' => 'Family Bundle', 'ar' => 'حزمة العائلة'],
            'slug' => 'family-bundle-test',
            'type' => FlashOffer::TYPE_BUNDLE_FIXED_PRICE,
            'offer_scope' => FlashOffer::SCOPE_BUNDLE,
            'status' => FlashOffer::STATUS_ACTIVE,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDay(),
            'fixed_price' => 17.98,
            'max_quantity' => 10,
            'free_shipping_scope' => FlashOffer::FREE_SHIPPING_NONE,
        ]);

        FlashOfferItem::create(['flash_offer_id' => $offer->id, 'product_id' => $sandwich->id, 'quantity' => 2, 'original_price' => 6.99, 'offer_price' => 5.99]);
        FlashOfferItem::create(['flash_offer_id' => $offer->id, 'product_id' => $sauce->id, 'quantity' => 2, 'original_price' => 3.50, 'offer_price' => 3.00]);

        $this->actingAs($user)->post(route('offers.cart.add', $offer->slug))->assertRedirect(route('cart.index'));

        $this->assertDatabaseHas('cart_items', [
            'item_type' => 'offer',
            'product_id' => null,
            'offer_id' => $offer->id,
            'quantity' => 1,
            'unit_price' => 17.98,
            'price_type' => 'flash_offer',
            'applied_flash_offer_id' => $offer->id,
        ]);

        $cartItem = \App\Models\CartItem::where('offer_id', $offer->id)->firstOrFail();

        $this->assertCount(2, $cartItem->components_snapshot);
        $this->assertSame('Sandwich', $cartItem->components_snapshot[0]['product_name']);
        $this->assertSame(2, $cartItem->components_snapshot[0]['quantity']);
    }

    public function test_checkout_creates_single_offer_order_item_and_reduces_component_stock(): void
    {
        $user = User::factory()->create(['status' => true]);

        Currency::create(['code' => 'USD', 'symbol' => '$', 'name' => 'US Dollar', 'rate' => 1, 'is_default' => true, 'status' => true]);

        $city = City::create(['name' => ['en' => 'Damascus', 'ar' => 'دمشق'], 'slug' => 'damascus-offer', 'country' => 'Syria', 'is_active' => true]);
        $address = UserAddress::create(['user_id' => $user->id, 'label' => 'Home', 'recipient_name' => 'Offer Buyer', 'phone' => '+963900000004', 'city_id' => $city->id, 'address_line' => 'Main Street', 'is_default' => true, 'is_active' => true]);
        $payment = PaymentMethod::create(['name' => 'Cash on Delivery', 'slug' => 'cod-offer-order', 'type' => 'cod', 'fee' => 0, 'is_active' => true]);
        $carrier = ShippingCarrier::create(['name' => ['en' => 'Standard Offer', 'ar' => 'قياسي عرض'], 'slug' => 'standard-offer', 'status' => 'active']);
        ShippingRate::create(['shipping_carrier_id' => $carrier->id, 'city_id' => $city->id, 'base_cost' => 3, 'cost_per_kg' => 1, 'is_active' => true]);

        $charger = Product::create(['name' => 'Charger', 'slug' => 'charger', 'sku' => 'OFFER-CHARGER', 'retail_price' => 20, 'stock_quantity' => 10, 'weight' => 1, 'status' => true]);
        $cable = Product::create(['name' => 'Cable', 'slug' => 'cable', 'sku' => 'OFFER-CABLE', 'retail_price' => 10, 'stock_quantity' => 10, 'weight' => 0.5, 'status' => true]);

        $offer = FlashOffer::create([
            'title' => ['en' => 'Fast Charging Bundle', 'ar' => 'حزمة الشحن السريع'],
            'slug' => 'fast-charging-bundle-test',
            'type' => FlashOffer::TYPE_BUNDLE_FIXED_PRICE,
            'offer_scope' => FlashOffer::SCOPE_BUNDLE,
            'status' => FlashOffer::STATUS_ACTIVE,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDay(),
            'fixed_price' => 25,
            'max_quantity' => 20,
            'free_shipping_scope' => FlashOffer::FREE_SHIPPING_NONE,
        ]);

        FlashOfferItem::create(['flash_offer_id' => $offer->id, 'product_id' => $charger->id, 'quantity' => 1, 'original_price' => 20]);
        FlashOfferItem::create(['flash_offer_id' => $offer->id, 'product_id' => $cable->id, 'quantity' => 2, 'original_price' => 10]);

        $cart = app(CartRepository::class)->findForUser($user->id);
        app(OfferCartService::class)->addOfferToCart($cart, $offer, 2);

        $order = app(CreateOrderFromCart::class)->handle(new CheckoutData(
            userId: $user->id,
            paymentMethodId: $payment->id,
            addressMode: 'saved',
            shippingCityId: $city->id,
            shippingCarrierId: $carrier->id,
            userAddressId: $address->id,
        ));

        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'item_type' => 'offer',
            'product_id' => null,
            'offer_id' => $offer->id,
            'quantity' => 2,
            'unit_price' => 25,
            'total_price' => 50,
        ]);

        $orderItem = $order->items()->where('item_type', 'offer')->firstOrFail();

        $this->assertCount(2, $orderItem->components_snapshot);
        $this->assertSame(8, $charger->fresh()->stock_quantity);
        $this->assertSame(6, $cable->fresh()->stock_quantity);
        $this->assertSame(2, $offer->fresh()->sold_quantity);
    }

    public function test_buy_x_get_y_offer_checks_and_reduces_free_item_stock(): void
    {
        $user = User::factory()->create(['status' => true]);

        Currency::create(['code' => 'USD', 'symbol' => '$', 'name' => 'US Dollar', 'rate' => 1, 'is_default' => true, 'status' => true]);

        $city = City::create(['name' => ['en' => 'Aleppo', 'ar' => 'حلب'], 'slug' => 'aleppo-offer', 'country' => 'Syria', 'is_active' => true]);
        $address = UserAddress::create(['user_id' => $user->id, 'label' => 'Home', 'recipient_name' => 'Offer Buyer', 'phone' => '+963900000005', 'city_id' => $city->id, 'address_line' => 'Main Street', 'is_default' => true, 'is_active' => true]);
        $payment = PaymentMethod::create(['name' => 'Cash on Delivery', 'slug' => 'cod-buy-x-get-y', 'type' => 'cod', 'fee' => 0, 'is_active' => true]);
        $carrier = ShippingCarrier::create(['name' => ['en' => 'Standard Buy', 'ar' => 'قياسي شراء'], 'slug' => 'standard-buy', 'status' => 'active']);
        ShippingRate::create(['shipping_carrier_id' => $carrier->id, 'city_id' => $city->id, 'base_cost' => 0, 'cost_per_kg' => 0, 'is_active' => true]);

        $paidCable = Product::create(['name' => 'Paid Cable', 'slug' => 'paid-cable', 'sku' => 'BUY-PAID-CABLE', 'retail_price' => 10, 'stock_quantity' => 10, 'weight' => 0.2, 'status' => true]);
        $freeCable = Product::create(['name' => 'Free Cable', 'slug' => 'free-cable', 'sku' => 'BUY-FREE-CABLE', 'retail_price' => 10, 'stock_quantity' => 2, 'weight' => 0.2, 'status' => true]);

        $offer = FlashOffer::create([
            'title' => ['en' => 'Buy 2 Get 1', 'ar' => 'اشتر 2 واحصل على 1'],
            'slug' => 'buy-two-get-one-test',
            'type' => FlashOffer::TYPE_BUY_X_GET_Y,
            'offer_scope' => FlashOffer::SCOPE_PRODUCT,
            'status' => FlashOffer::STATUS_ACTIVE,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDay(),
            'max_quantity' => 10,
            'free_shipping_scope' => FlashOffer::FREE_SHIPPING_NONE,
        ]);

        FlashOfferItem::create(['flash_offer_id' => $offer->id, 'product_id' => $paidCable->id, 'quantity' => 2, 'original_price' => 10, 'is_free_item' => false]);
        FlashOfferItem::create(['flash_offer_id' => $offer->id, 'product_id' => $freeCable->id, 'quantity' => 1, 'original_price' => 10, 'offer_price' => 0, 'is_free_item' => true]);

        $cart = app(CartRepository::class)->findForUser($user->id);
        app(OfferCartService::class)->addOfferToCart($cart, $offer, 2);

        app(CreateOrderFromCart::class)->handle(new CheckoutData(
            userId: $user->id,
            paymentMethodId: $payment->id,
            addressMode: 'saved',
            shippingCityId: $city->id,
            shippingCarrierId: $carrier->id,
            userAddressId: $address->id,
        ));

        $this->assertSame(6, $paidCable->fresh()->stock_quantity);
        $this->assertSame(0, $freeCable->fresh()->stock_quantity);

        $this->expectException(ValidationException::class);
        app(OfferCartService::class)->addOfferToCart($cart, $offer, 1);
    }
}
