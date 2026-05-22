<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShippingRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'shipping_method_id',
        'shipping_zone_id',
        'calculation_type',
        'min_quantity',
        'max_quantity',
        'min_weight',
        'max_weight',
        'min_subtotal',
        'cost',
        'cost_per_kg',
        'is_free',
        'is_active',
    ];

    protected $casts = [
        'min_weight' => 'decimal:3',
        'max_weight' => 'decimal:3',
        'min_subtotal' => 'decimal:2',
        'cost' => 'decimal:2',
        'cost_per_kg' => 'decimal:2',
        'is_free' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function method(): BelongsTo
    {
        return $this->belongsTo(ShippingMethod::class, 'shipping_method_id');
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(ShippingZone::class, 'shipping_zone_id');
    }
}
