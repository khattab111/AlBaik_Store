<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SitemapAndAccessibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_sitemap_index_points_to_public_child_sitemaps(): void
    {
        config(['app.url' => 'https://store.test']);

        $activeBrand = Brand::create(['name' => 'Active Brand', 'slug' => 'active-brand', 'status' => true]);
        $inactiveBrand = Brand::create(['name' => 'Hidden Brand', 'slug' => 'hidden-brand', 'status' => false]);
        $activeCategory = Category::create(['name' => 'Food', 'slug' => 'food', 'status' => true]);
        $inactiveCategory = Category::create(['name' => 'Hidden', 'slug' => 'hidden-category', 'status' => false]);

        Product::create([
            'name' => 'Active Product',
            'slug' => 'active-product',
            'sku' => 'ACTIVE-001',
            'brand_id' => $activeBrand->id,
            'category_id' => $activeCategory->id,
            'retail_price' => 10,
            'stock_quantity' => 5,
            'status' => true,
        ]);

        Product::create([
            'name' => 'Hidden Product',
            'slug' => 'hidden-product',
            'sku' => 'HIDDEN-001',
            'brand_id' => $inactiveBrand->id,
            'category_id' => $inactiveCategory->id,
            'retail_price' => 10,
            'stock_quantity' => 5,
            'status' => false,
        ]);

        $response = $this->get('/sitemap.xml');

        $response->assertOk();
        $response->assertHeader('content-type', 'application/xml');
        $response->assertSee('<sitemapindex', false);
        $response->assertSee('https://store.test/sitemap-pages.xml', false);
        $response->assertSee('https://store.test/sitemap-products-1.xml', false);
        $response->assertSee('https://store.test/sitemap-categories-1.xml', false);
        $response->assertSee('https://store.test/sitemap-brands-1.xml', false);
        $response->assertDontSee('active-product', false);
        $response->assertDontSee('hidden-product', false);
        $response->assertDontSee('/cart', false);
        $response->assertDontSee('/checkout', false);
        $response->assertDontSee('/account', false);
        $response->assertDontSee('/login', false);

        $this->assertActiveOnlyUrlset('/sitemap-products-1.xml', 'https://store.test/products/active-product', 'hidden-product');
        $this->assertActiveOnlyUrlset('/sitemap-categories-1.xml', 'https://store.test/categories/food', 'hidden-category');
        $this->assertActiveOnlyUrlset('/sitemap-brands-1.xml', 'https://store.test/brands/active-brand', 'hidden-brand');
    }

    public function test_static_pages_sitemap_contains_public_pages_only(): void
    {
        config(['app.url' => 'https://store.test']);

        $response = $this->get('/sitemap-pages.xml');

        $response->assertOk();
        $response->assertHeader('content-type', 'application/xml');
        $response->assertSee('<urlset', false);
        $response->assertSee('https://store.test/products', false);
        $response->assertSee('https://store.test/offers', false);
        $response->assertSee('https://store.test/brands', false);
        $response->assertSee('https://store.test/categories', false);
        $response->assertSee('https://store.test/about', false);
        $response->assertSee('https://store.test/contact', false);
        $response->assertSee('https://store.test/sitemap', false);
        $response->assertSee('https://store.test/accessibility', false);
        $response->assertDontSee('/cart', false);
        $response->assertDontSee('/checkout', false);
        $response->assertDontSee('/account', false);
        $response->assertDontSee('/login', false);
    }

    public function test_out_of_range_dynamic_sitemap_page_returns_not_found(): void
    {
        $this->get('/sitemap-products-99.xml')->assertNotFound();
    }

    public function test_accessibility_page_and_visible_sitemap_render(): void
    {
        $this->get('/accessibility')
            ->assertOk()
            ->assertSee('id="main-content"', false)
            ->assertSee(__('Accessibility statement'))
            ->assertSee(__('Screen reader support'))
            ->assertSee(__('Blind and low-vision support'));

        $this->get('/sitemap')
            ->assertOk()
            ->assertSee(__('Sitemap'))
            ->assertSee(route('accessibility'), false);
    }

    public function test_storefront_layout_exposes_screen_reader_navigation_helpers(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('href="#main-content"', false)
            ->assertSee('id="main-content"', false)
            ->assertSee('data-scroll-progress', false)
            ->assertSee('data-store-header', false)
            ->assertSee('role="search"', false)
            ->assertSee('id="storefront-search"', false)
            ->assertSee(__('Search storefront'))
            ->assertSee('data-theme-toggle', false)
            ->assertSee('storefront-theme', false)
            ->assertSee(__('Switch to dark mode'), false)
            ->assertSee(__('Switch to light mode'), false)
            ->assertSee('role="contentinfo"', false)
            ->assertSee('data-store-footer', false)
            ->assertSee('store-footer-newsletter', false)
            ->assertSee('id="footer-newsletter-email"', false)
            ->assertSee(__('Subscribe to newsletter'));
    }

    private function assertActiveOnlyUrlset(string $path, string $activeUrl, string $hiddenNeedle): void
    {
        $response = $this->get($path);

        $response->assertOk();
        $response->assertHeader('content-type', 'application/xml');
        $response->assertSee('<urlset', false);
        $response->assertSee($activeUrl, false);
        $response->assertDontSee($hiddenNeedle, false);
        $response->assertDontSee('/cart', false);
        $response->assertDontSee('/checkout', false);
        $response->assertDontSee('/account', false);
        $response->assertDontSee('/login', false);
    }
}
