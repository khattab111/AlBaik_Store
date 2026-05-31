<?php

namespace App\Models;

use App\Models\Concerns\HasAutoSlug;
use App\Models\Concerns\HasStoreTranslations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FlashOffer extends Model
{
    use HasAutoSlug, HasFactory, HasStoreTranslations;

    public const TYPE_PERCENTAGE_DISCOUNT = 'percentage_discount';
    public const TYPE_FIXED_AMOUNT_DISCOUNT = 'fixed_amount_discount';
    public const TYPE_FIXED_PRICE_QUANTITY = 'fixed_price_quantity';
    public const TYPE_BUNDLE_FIXED_PRICE = 'bundle_fixed_price';
    public const TYPE_FREE_SHIPPING_PRODUCT = 'free_shipping_product';
    public const TYPE_BUY_X_GET_Y = 'buy_x_get_y';
    public const TYPE_CART_FREE_SHIPPING = 'cart_free_shipping';

    public const SCOPE_PRODUCT = 'product';
    public const SCOPE_BUNDLE = 'bundle';
    public const SCOPE_CART = 'cart';

    public const FREE_SHIPPING_NONE = 'none';
    public const FREE_SHIPPING_OFFER = 'offer';
    public const FREE_SHIPPING_CART = 'cart';

    public const STATUS_DRAFT = 'draft';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';
    public const STATUS_EXPIRED = 'expired';

    public array $translatable = ['title', 'description'];

    protected string $slugSourceField = 'title';

    protected $fillable = [
        'title',
        'slug',
        'description',
        'type',
        'offer_scope',
        'status',
        'starts_at',
        'ends_at',
        'priority',
        'discount_type',
        'discount_value',
        'fixed_price',
        'max_quantity',
        'sold_quantity',
        'free_shipping',
        'free_shipping_scope',
        'min_order_amount',
        'usage_limit',
        'usage_per_user',
    ];

    protected $casts = [
        'title' => 'array',
        'description' => 'array',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'priority' => 'integer',
        'discount_value' => 'decimal:2',
        'fixed_price' => 'decimal:2',
        'max_quantity' => 'integer',
        'sold_quantity' => 'integer',
        'free_shipping' => 'boolean',
        'min_order_amount' => 'decimal:2',
        'usage_limit' => 'integer',
        'usage_per_user' => 'integer',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(FlashOfferItem::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeCurrentlyValid(Builder $query): Builder
    {
        return $query
            ->active()
            ->where(fn (Builder $builder) => $builder->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn (Builder $builder) => $builder->whereNull('ends_at')->orWhere('ends_at', '>=', now()))
            ->where(fn (Builder $builder) => $builder->whereNull('max_quantity')->orWhereColumn('sold_quantity', '<', 'max_quantity'));
    }

    public function scopeAvailableQuantity(Builder $query): Builder
    {
        return $query->where(fn (Builder $builder) => $builder->whereNull('max_quantity')->orWhereColumn('sold_quantity', '<', 'max_quantity'));
    }

    public function remainingQuantity(): ?int
    {
        if ($this->max_quantity === null) {
            return null;
        }

        return max(0, $this->max_quantity - $this->sold_quantity);
    }

    public static function typeOptions(): array
    {
        return [
            self::TYPE_PERCENTAGE_DISCOUNT => __('Percentage discount'),
            self::TYPE_FIXED_AMOUNT_DISCOUNT => __('Fixed amount discount'),
            self::TYPE_FIXED_PRICE_QUANTITY => __('Fixed price quantity'),
            self::TYPE_BUNDLE_FIXED_PRICE => __('Bundle fixed price'),
            self::TYPE_FREE_SHIPPING_PRODUCT => __('Product with free shipping'),
            self::TYPE_BUY_X_GET_Y => __('Buy X get Y'),
            self::TYPE_CART_FREE_SHIPPING => __('Cart free shipping'),
        ];
    }

    public static function scopeOptions(): array
    {
        return [
            self::SCOPE_PRODUCT => __('Product'),
            self::SCOPE_BUNDLE => __('Bundle'),
            self::SCOPE_CART => __('Cart'),
        ];
    }

    public static function freeShippingScopeOptions(): array
    {
        return [
            self::FREE_SHIPPING_NONE => __('None'),
            self::FREE_SHIPPING_OFFER => __('Offer only'),
            self::FREE_SHIPPING_CART => __('Whole cart'),
        ];
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_DRAFT => __('Draft'),
            self::STATUS_ACTIVE => __('Active'),
            self::STATUS_INACTIVE => __('Inactive'),
            self::STATUS_EXPIRED => __('Expired'),
        ];
    }
}
