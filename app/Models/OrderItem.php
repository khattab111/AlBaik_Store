<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'item_type',
        'product_id',
        'offer_id',
        'offer_title',
        'offer_type',
        'offer_price',
        'original_total_price',
        'savings_amount',
        'components_snapshot',
        'variant_id',
        'quantity',
        'unit_price',
        'price_type',
        'applied_tier_id',
        'applied_flash_offer_id',
        'subtotal',
        'total_price',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'offer_price' => 'decimal:2',
        'original_total_price' => 'decimal:2',
        'savings_amount' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'total_price' => 'decimal:2',
        'components_snapshot' => 'array',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function offer(): BelongsTo
    {
        return $this->belongsTo(FlashOffer::class, 'offer_id');
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    public function appliedTier(): BelongsTo
    {
        return $this->belongsTo(ProductPriceTier::class, 'applied_tier_id');
    }

    public function appliedFlashOffer(): BelongsTo
    {
        return $this->belongsTo(FlashOffer::class, 'applied_flash_offer_id');
    }
}
