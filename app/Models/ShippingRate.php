<?php

namespace App\Models;

use App\Models\Concerns\HasStoreTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShippingRate extends Model
{
    use HasFactory, HasStoreTranslations;

    public array $translatable = ['estimated_delivery_time'];

    protected $fillable = [
        'shipping_carrier_id',
        'city_id',
        'is_active',
        'base_cost',
        'cost_per_kg',
        'min_weight',
        'max_weight',
        'free_shipping_threshold',
        'estimated_delivery_time',
        'remote_area_fee',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'base_cost' => 'decimal:2',
        'cost_per_kg' => 'decimal:2',
        'min_weight' => 'decimal:3',
        'max_weight' => 'decimal:3',
        'free_shipping_threshold' => 'decimal:2',
        'remote_area_fee' => 'decimal:2',
        'sort_order' => 'integer',
    ];

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function carrier(): BelongsTo
    {
        return $this->belongsTo(ShippingCarrier::class, 'shipping_carrier_id');
    }
}
