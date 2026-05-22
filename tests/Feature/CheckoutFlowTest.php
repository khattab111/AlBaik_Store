<?php

namespace Tests\Feature;

use App\Actions\Orders\CreateOrderFromCart;
use App\Data\CheckoutData;
use App\Models\Address;
use App\Models\Currency;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ShippingMethod;
use App\Models\User;
use App\Repositories\CartRepository;
use App\Services\InventoryService;
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

        $address = Address::create([
            'user_id' => $user->id,
            'country' => 'Syria',
            'city' => 'Damascus',
            'street' => 'Main Street',
        ]);

        $payment = PaymentMethod::create([
            'name' => 'Cash on Delivery',
            'slug' => 'cod',
            'type' => 'cod',
            'fee' => 0,
            'is_active' => true,
        ]);

        $shipping = ShippingMethod::create([
            'name' => 'Standard',
            'slug' => 'standard',
            'type' => 'flat_rate',
            'cost' => 5,
            'is_active' => true,
        ]);

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
            shippingAddressId: $address->id,
            paymentMethodId: $payment->id,
            shippingMethodId: $shipping->id,
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
}
