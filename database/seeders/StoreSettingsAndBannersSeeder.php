<?php

namespace Database\Seeders;

use App\Models\Banner;
use App\Models\Setting;
use Illuminate\Database\Seeder;

class StoreSettingsAndBannersSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedSettings();
        $this->seedBanners();
    }

    private function seedSettings(): void
    {
        Setting::query()
            ->where('group', 'store')
            ->whereIn('key', ['name', 'description', 'whatsapp'])
            ->delete();

        $settings = [
            [
                'group' => 'identity',
                'key' => 'store.name',
                'value' => $this->tr('AlBaik Store', 'متجر البيك'),
                'type' => 'json',
                'is_public' => true,
            ],
            [
                'group' => 'identity',
                'key' => 'store.tagline',
                'value' => $this->tr('Premium electronics market', 'سوق إلكترونيات مميز'),
                'type' => 'json',
                'is_public' => true,
            ],
            [
                'group' => 'identity',
                'key' => 'store.short_description',
                'value' => $this->tr(
                    'Original phones, accessories, spare parts, and wholesale-ready bundles.',
                    'هواتف أصلية، إكسسوارات، قطع تبديل، وحزم جاهزة للبيع بالجملة.'
                ),
                'type' => 'json',
                'is_public' => true,
            ],
            [
                'group' => 'identity',
                'key' => 'store.primary_color',
                'value' => ['value' => '#111111'],
                'type' => 'color',
                'is_public' => true,
            ],
            [
                'group' => 'identity',
                'key' => 'store.primary_hover_color',
                'value' => ['value' => '#2a2a2a'],
                'type' => 'color',
                'is_public' => true,
            ],
            [
                'group' => 'identity',
                'key' => 'store.accent_color',
                'value' => ['value' => '#d99a16'],
                'type' => 'color',
                'is_public' => true,
            ],
            [
                'group' => 'identity',
                'key' => 'store.topbar_color',
                'value' => ['value' => '#111111'],
                'type' => 'color',
                'is_public' => true,
            ],
            [
                'group' => 'identity',
                'key' => 'store.header_bg_color',
                'value' => ['value' => '#ffffff'],
                'type' => 'color',
                'is_public' => true,
            ],
            [
                'group' => 'identity',
                'key' => 'store.nav_bg_color',
                'value' => ['value' => '#ffffff'],
                'type' => 'color',
                'is_public' => true,
            ],
            [
                'group' => 'identity',
                'key' => 'store.body_bg_color',
                'value' => ['value' => '#fafafa'],
                'type' => 'color',
                'is_public' => true,
            ],
            [
                'group' => 'identity',
                'key' => 'store.surface_color',
                'value' => ['value' => '#ffffff'],
                'type' => 'color',
                'is_public' => true,
            ],
            [
                'group' => 'identity',
                'key' => 'store.surface_tint_color',
                'value' => ['value' => '#f5f6f8'],
                'type' => 'color',
                'is_public' => true,
            ],
            [
                'group' => 'identity',
                'key' => 'store.text_color',
                'value' => ['value' => '#111111'],
                'type' => 'color',
                'is_public' => true,
            ],
            [
                'group' => 'identity',
                'key' => 'store.muted_text_color',
                'value' => ['value' => '#6b7280'],
                'type' => 'color',
                'is_public' => true,
            ],
            [
                'group' => 'identity',
                'key' => 'store.border_color',
                'value' => ['value' => '#e5e7eb'],
                'type' => 'color',
                'is_public' => true,
            ],
            [
                'group' => 'identity',
                'key' => 'store.logo',
                'value' => ['value' => null],
                'type' => 'image',
                'is_public' => true,
            ],
            [
                'group' => 'identity',
                'key' => 'store.default_product_image',
                'value' => ['value' => 'images/storefront/product-fallback.svg'],
                'type' => 'image',
                'is_public' => true,
            ],
            [
                'group' => 'contact',
                'key' => 'contact.email',
                'value' => ['value' => 'support@albaikstore.local'],
                'type' => 'string',
                'is_public' => true,
            ],
            [
                'group' => 'contact',
                'key' => 'contact.phone',
                'value' => ['value' => '+963 900 000 000'],
                'type' => 'string',
                'is_public' => true,
            ],
            [
                'group' => 'contact',
                'key' => 'contact.whatsapp',
                'value' => ['value' => '+963 900 000 000'],
                'type' => 'string',
                'is_public' => true,
            ],
            [
                'group' => 'contact',
                'key' => 'contact.address',
                'value' => $this->tr('Syria', 'سوريا'),
                'type' => 'json',
                'is_public' => true,
            ],
            [
                'group' => 'contact',
                'key' => 'contact.working_hours',
                'value' => $this->tr('Daily from 9:00 to 18:00', 'يومياً من 9:00 إلى 18:00'),
                'type' => 'json',
                'is_public' => true,
            ],
            [
                'group' => 'social',
                'key' => 'social.facebook',
                'value' => ['value' => 'https://facebook.com/albaikstore'],
                'type' => 'url',
                'is_public' => true,
            ],
            [
                'group' => 'social',
                'key' => 'social.instagram',
                'value' => ['value' => 'https://instagram.com/albaikstore'],
                'type' => 'url',
                'is_public' => true,
            ],
            [
                'group' => 'social',
                'key' => 'social.youtube',
                'value' => ['value' => 'https://youtube.com/@albaikstore'],
                'type' => 'url',
                'is_public' => true,
            ],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }

    private function seedBanners(): void
    {
        $banners = [
            [
                'slug' => 'home-hero-electronics',
                'title' => $this->tr('Latest phones and original accessories', 'أحدث الهواتف والإكسسوارات الأصلية'),
                'subtitle' => $this->tr('Retail and wholesale electronics with warranty.', 'إلكترونيات تجزئة وجملة مع ضمان.'),
                'eyebrow' => $this->tr('AlBaik Store', 'متجر البيك'),
                'primary_button_text' => $this->tr('Browse products', 'تصفح المنتجات'),
                'secondary_button_text' => $this->tr('Current offers', 'العروض الحالية'),
                'image' => 'demo/banners/electronics-hero.jpg',
                'url' => '/products',
                'secondary_url' => '/offers',
                'background_color' => '#111827',
                'text_color' => '#ffffff',
                'placement' => Banner::PLACEMENT_HOME_HERO,
                'sort_order' => 1,
                'is_active' => true,
                'starts_at' => now()->subDay(),
                'ends_at' => now()->addMonths(3),
            ],
            [
                'slug' => 'home-after-hero-accessories',
                'title' => $this->tr('Original accessories for your phone', 'إكسسوارات أصلية لهاتفك'),
                'subtitle' => $this->tr('Chargers, cables, earbuds, cases and protectors with guaranteed quality.', 'شواحن، كابلات، سماعات، أغطية وحمايات بجودة مضمونة.'),
                'eyebrow' => $this->tr('Accessories', 'إكسسوارات'),
                'primary_button_text' => $this->tr('Shop accessories', 'تسوق الإكسسوارات'),
                'secondary_button_text' => $this->tr('View brands', 'شاهد البراندات'),
                'image' => 'demo/banners/accessories.jpg',
                'url' => '/products?category=accessories',
                'secondary_url' => '/brands',
                'background_color' => '#4c1d95',
                'text_color' => '#ffffff',
                'placement' => Banner::PLACEMENT_HOME_AFTER_HERO,
                'sort_order' => 2,
                'is_active' => true,
                'starts_at' => now()->subDay(),
                'ends_at' => now()->addMonths(3),
            ],
            [
                'slug' => 'home-before-products-wholesale',
                'title' => $this->tr('Wholesale prices for partners', 'أسعار جملة للشركاء'),
                'subtitle' => $this->tr('Join as a partner and get special prices for shops and retailers.', 'انضم كشريك واحصل على أسعار خاصة للمتاجر وأصحاب المحلات.'),
                'eyebrow' => $this->tr('Partners', 'شركاؤنا'),
                'primary_button_text' => $this->tr('Join us', 'انضم إلينا'),
                'secondary_button_text' => $this->tr('Browse products', 'تصفح المنتجات'),
                'image' => 'demo/banners/wholesale.jpg',
                'url' => '/join-us',
                'secondary_url' => '/products',
                'background_color' => '#0f172a',
                'text_color' => '#ffffff',
                'placement' => Banner::PLACEMENT_HOME_BEFORE_PRODUCTS,
                'sort_order' => 3,
                'is_active' => true,
                'starts_at' => now()->subDay(),
                'ends_at' => now()->addMonths(3),
            ],
            [
                'slug' => 'products-top-electronics',
                'title' => $this->tr('Everything you need in electronics', 'كل ما تحتاجه من الإلكترونيات'),
                'subtitle' => $this->tr('Phones, parts, accessories and carefully selected products.', 'هواتف، قطع، إكسسوارات ومنتجات مختارة بعناية.'),
                'eyebrow' => $this->tr('Products', 'المنتجات'),
                'primary_button_text' => $this->tr('Browse now', 'تصفح الآن'),
                'secondary_button_text' => $this->tr('Offers', 'العروض'),
                'image' => 'demo/banners/products-top.jpg',
                'url' => '/products',
                'secondary_url' => '/offers',
                'background_color' => '#1e293b',
                'text_color' => '#ffffff',
                'placement' => Banner::PLACEMENT_PRODUCTS_TOP,
                'sort_order' => 4,
                'is_active' => true,
                'starts_at' => now()->subDay(),
                'ends_at' => now()->addMonths(3),
            ],
            [
                'slug' => 'offers-top-flash',
                'title' => $this->tr('Flash offers on electronics', 'عروض خاطفة على الإلكترونيات'),
                'subtitle' => $this->tr('Limited-time discounts and bundles on phones and accessories.', 'خصومات وحزم خاصة لفترة محدودة على الهواتف والإكسسوارات.'),
                'eyebrow' => $this->tr('Limited Offers', 'عروض محدودة'),
                'primary_button_text' => $this->tr('View offers', 'شاهد العروض'),
                'secondary_button_text' => $this->tr('Browse products', 'تصفح المنتجات'),
                'image' => 'demo/banners/offers-top.jpg',
                'url' => '/offers',
                'secondary_url' => '/products',
                'background_color' => '#7c2d12',
                'text_color' => '#ffffff',
                'placement' => Banner::PLACEMENT_OFFERS_TOP,
                'sort_order' => 5,
                'is_active' => true,
                'starts_at' => now()->subDay(),
                'ends_at' => now()->addMonths(3),
            ],
            [
                'slug' => 'categories-top-electronics',
                'title' => $this->tr('Shop by category', 'تسوق حسب الفئة'),
                'subtitle' => $this->tr('Choose the right category: phones, chargers, earbuds, electronic parts and more.', 'اختر الفئة المناسبة: هواتف، شواحن، سماعات، قطع إلكترونية والمزيد.'),
                'eyebrow' => $this->tr('Categories', 'الفئات'),
                'primary_button_text' => $this->tr('View categories', 'عرض الفئات'),
                'secondary_button_text' => $this->tr('Brands', 'البراندات'),
                'image' => 'demo/banners/categories-top.jpg',
                'url' => '/categories',
                'secondary_url' => '/brands',
                'background_color' => '#164e63',
                'text_color' => '#ffffff',
                'placement' => Banner::PLACEMENT_CATEGORIES_TOP,
                'sort_order' => 6,
                'is_active' => true,
                'starts_at' => now()->subDay(),
                'ends_at' => now()->addMonths(3),
            ],
            [
                'slug' => 'brands-top-original',
                'title' => $this->tr('Trusted brands and original products', 'براندات موثوقة ومنتجات أصلية'),
                'subtitle' => $this->tr('Products from top brands in phones and electronics.', 'منتجات من أشهر العلامات التجارية في عالم الهواتف والإلكترونيات.'),
                'eyebrow' => $this->tr('Brands', 'البراندات'),
                'primary_button_text' => $this->tr('Explore brands', 'استعرض البراندات'),
                'secondary_button_text' => $this->tr('Shop now', 'تسوق الآن'),
                'image' => 'demo/banners/brands-top.jpg',
                'url' => '/brands',
                'secondary_url' => '/products',
                'background_color' => '#312e81',
                'text_color' => '#ffffff',
                'placement' => Banner::PLACEMENT_BRANDS_TOP,
                'sort_order' => 7,
                'is_active' => true,
                'starts_at' => now()->subDay(),
                'ends_at' => now()->addMonths(3),
            ],
        ];

        foreach ($banners as $banner) {
            Banner::updateOrCreate(
                ['slug' => $banner['slug']],
                $banner
            );
        }
    }

    /**
     * @return array{en: string, ar: string}
     */
    private function tr(string $en, string $ar): array
    {
        return ['en' => $en, 'ar' => $ar];
    }
}



