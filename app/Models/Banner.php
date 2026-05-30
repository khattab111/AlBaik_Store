<?php

namespace App\Models;

use App\Models\Concerns\HasStoreTranslations;
use App\Models\Concerns\HasAutoSlug;
use App\Services\StorefrontCacheService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    use HasAutoSlug, HasFactory, HasStoreTranslations;

    public const PLACEMENT_HOME_HERO = 'home';
    public const PLACEMENT_HOME_AFTER_HERO = 'home_after_hero';
    public const PLACEMENT_HOME_BEFORE_PRODUCTS = 'home_before_products';
    public const PLACEMENT_PRODUCTS_TOP = 'products_top';
    public const PLACEMENT_OFFERS_TOP = 'offers_top';
    public const PLACEMENT_CATEGORIES_TOP = 'categories_top';
    public const PLACEMENT_BRANDS_TOP = 'brands_top';

    public array $translatable = ['title', 'subtitle', 'eyebrow', 'primary_button_text', 'secondary_button_text'];

    protected string $slugSourceField = 'title';

    protected $fillable = [
        'title',
        'slug',
        'eyebrow',
        'primary_button_text',
        'secondary_button_text',
        'title_ar',
        'title_en',
        'subtitle',
        'subtitle_ar',
        'subtitle_en',
        'eyebrow_ar',
        'eyebrow_en',
        'image',
        'url',
        'primary_button_text_ar',
        'primary_button_text_en',
        'secondary_button_text_ar',
        'secondary_button_text_en',
        'secondary_url',
        'background_color',
        'text_color',
        'placement',
        'sort_order',
        'is_active',
        'starts_at',
        'ends_at',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        $clearHomeCache = fn (): mixed => app(StorefrontCacheService::class)->clearHome();

        static::saved($clearHomeCache);
        static::deleted($clearHomeCache);
    }

    /**
     * @return array<string, string>
     */
    public static function placementOptions(): array
    {
        return [
            self::PLACEMENT_HOME_HERO => __('Home hero slider'),
            self::PLACEMENT_HOME_AFTER_HERO => __('Home after hero'),
            self::PLACEMENT_HOME_BEFORE_PRODUCTS => __('Home before products'),
            self::PLACEMENT_PRODUCTS_TOP => __('Products page top'),
            self::PLACEMENT_OFFERS_TOP => __('Offers page top'),
            self::PLACEMENT_CATEGORIES_TOP => __('Categories page top'),
            self::PLACEMENT_BRANDS_TOP => __('Brands page top'),
        ];
    }

    public function scopeActiveNow($query)
    {
        return $query
            ->where('is_active', true)
            ->where(fn ($builder) => $builder->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn ($builder) => $builder->whereNull('ends_at')->orWhere('ends_at', '>=', now()));
    }

    public function scopeForPlacement($query, string|array $placement)
    {
        return $query->whereIn('placement', (array) $placement);
    }
}
