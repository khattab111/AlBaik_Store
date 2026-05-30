<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FlashOfferItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'flash_offer_id',
        'product_id',
        'quantity',
        'original_price',
        'offer_price',
        'is_free_item',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'original_price' => 'decimal:2',
        'offer_price' => 'decimal:2',
        'is_free_item' => 'boolean',
    ];

    public function flashOffer(): BelongsTo
    {
        return $this->belongsTo(FlashOffer::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
