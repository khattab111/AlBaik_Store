<?php

namespace App\Models;

use App\Models\Concerns\HasStoreTranslations;
use App\Models\Concerns\HasAutoSlug;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Product extends Model
{
    use HasAutoSlug, HasFactory, HasStoreTranslations, LogsActivity;

    public array $translatable = ['name', 'short_description', 'description', 'seo_title', 'seo_description'];

    protected string $slugSourceField = 'name';

    protected $fillable = [
        'name',
        'slug',
        'short_description',
        'description',
        'sku',
        'barcode',
        'brand_id',
        'supplier_id',
        'category_id',
        'video_url',
        'status',
        'is_featured',
        'seo_title',
        'seo_description',
        'retail_price',
        'wholesale_price',
        'wholesale_minimum_quantity',
        'stock_quantity',
        'weight',
        'length',
        'width',
        'height',
        'requires_shipping',
        'free_shipping',
        'low_stock_threshold',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'status' => 'boolean',
        'retail_price' => 'decimal:2',
        'wholesale_price' => 'decimal:2',
        'weight' => 'decimal:3',
        'length' => 'decimal:3',
        'width' => 'decimal:3',
        'height' => 'decimal:3',
        'requires_shipping' => 'boolean',
        'free_shipping' => 'boolean',
    ];

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function priceTiers(): HasMany
    {
        return $this->hasMany(ProductPriceTier::class)->orderBy('sort_order')->orderBy('min_quantity');
    }

    public function getPriceForQuantity(int $quantity): float
    {
        if ($quantity >= ($this->wholesale_minimum_quantity ?? 0) && $this->wholesale_price > 0) {
            return (float) $this->wholesale_price;
        }

        return (float) $this->retail_price;
    }

    public function flashOfferItems(): HasMany
    {
        return $this->hasMany(FlashOfferItem::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function availableStock(?int $variantId = null): int
    {
        if ($variantId) {
            $variant = $this->variants->firstWhere('id', $variantId) ?? $this->variants()->find($variantId);

            return $variant ? max(0, $variant->stock - $variant->reserved_stock) : 0;
        }

        return max(0, $this->stock_quantity);
    }
}
