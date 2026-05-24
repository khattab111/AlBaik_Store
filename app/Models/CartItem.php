<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    use HasFactory;

    protected $fillable = ['cart_id', 'product_id', 'variant_id', 'quantity', 'unit_price', 'price_type', 'applied_tier_id'];

    protected $casts = ['quantity' => 'integer', 'unit_price' => 'decimal:2'];

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    public function appliedTier(): BelongsTo
    {
        return $this->belongsTo(ProductPriceTier::class, 'applied_tier_id');
    }
}
