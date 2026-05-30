<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Brand;
use App\Models\Category;
use App\Models\City;
use App\Models\Currency;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ShippingCarrier;
use App\Models\ShippingRate;
use App\Models\User;
use App\Repositories\CartRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontPageRenderingTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_storefront_pages_render(): void
    {
        $category = Category::create(['name' => 'Food', 'slug' => 'food', 'status' => true]);
        $brand = Brand::create(['name' => 'AlBaik', 'slug' => 'albaik', 'status' => true]);
        $product = Product::create([
            'name' => 'Classic Chicken Sandwich',
            'slug' => 'classic-chicken-sandwich',
            'sku' => 'ALB-001',
            'brand_id' => $brand->id,
            'category_id' => $category->id,
            'retail_price' => 10,
            'stock_quantity' => 5,
            'status' => true,
            'is_featured' => true,
        ]);

        foreach (['/', '/about', '/contact', '/products', '/offers', '/categories', '/brands', '/brands/'.$brand->slug, '/sitemap', '/accessibility', '/account/login', '/account/register', '/products/'.$product->slug] as $path) {
            $this->get($path)->assertOk();
        }

        $this->get('/products')
            ->assertSee(__('Add :product to cart', ['product' => $product->name]), false)
            ->assertSee(__('Rating: :rating out of 5 from :count reviews', ['rating' => '0.0', 'count' => 0]), false)
            ->assertSee(__('Breadcrumb'))
            ->assertSee(__('Filter products'))
            ->assertSee(__('Product view options'))
            ->assertSee('data-product-view="grid"', false)
            ->assertSee('USD 10.00', false)
            ->assertSee(__('Cart, :count items', ['count' => 0]), false)
            ->assertSee(__('Wishlist, :count items', ['count' => 0]), false);

        $this->get('/products?view=list&sort=price_asc')
            ->assertOk()
            ->assertSee('data-product-view="list"', false)
            ->assertSee('view=list', false)
            ->assertSee('sort=price_asc', false);
    }

    public function test_authenticated_storefront_pages_render(): void
    {
        $user = User::factory()->create(['status' => true]);
        Currency::create(['code' => 'USD', 'symbol' => '$', 'name' => 'US Dollar', 'rate' => 1, 'is_default' => true, 'status' => true]);
        $city = City::create(['name' => ['en' => 'Damascus', 'ar' => 'دمشق'], 'slug' => 'damascus', 'country' => 'Syria', 'is_active' => true]);
        Address::create(['user_id' => $user->id, 'country' => 'Syria', 'city_id' => $city->id, 'city' => 'Damascus', 'street' => 'Main Street']);
        PaymentMethod::create(['name' => 'Cash on Delivery', 'slug' => 'cod', 'type' => 'cod', 'fee' => 0, 'is_active' => true]);
        $carrier = ShippingCarrier::create(['name' => ['en' => 'Standard', 'ar' => 'قياسي'], 'slug' => 'standard', 'status' => 'active']);
        ShippingRate::create(['shipping_carrier_id' => $carrier->id, 'city_id' => $city->id, 'base_cost' => 5, 'cost_per_kg' => 0, 'is_active' => true]);

        $product = Product::create([
            'name' => 'Cart Product',
            'slug' => 'cart-product',
            'sku' => 'CART-001',
            'retail_price' => 10,
            'stock_quantity' => 5,
            'status' => true,
        ]);

        $cart = app(CartRepository::class)->findForUser($user->id);
        app(CartRepository::class)->addItem($cart, $product, 1);

        foreach (['/cart', '/checkout', '/account', '/account/profile', '/account/addresses', '/orders', '/favorites'] as $path) {
            $this->actingAs($user)->get($path)->assertOk();
        }
    }
}
