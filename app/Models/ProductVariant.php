<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'sku',
        'barcode',
        'attributes',
        'stock',
        'reserved_stock',
        'low_stock_threshold',
        'price',
    ];

    protected $casts = [
        'attributes' => 'array',
        'stock' => 'integer',
        'reserved_stock' => 'integer',
        'low_stock_threshold' => 'integer',
        'price' => 'decimal:2',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getAvailableStockAttribute(): int
    {
        return max(0, $this->stock - $this->reserved_stock);
    }
}
