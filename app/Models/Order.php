<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Order extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'user_id',
        'order_number',
        'currency_id',
        'payment_method_id',
        'shipping_address_id',
        'billing_address_id',
        'shipping_city_id',
        'shipping_city_name',
        'shipping_carrier_id',
        'shipping_carrier_name',
        'subtotal',
        'shipping_cost',
        'shipping_weight',
        'shipping_delivery_time',
        'shipping_address_text',
        'is_free_shipping',
        'discount_amount',
        'payment_fee',
        'total',
        'status',
        'tracking_number',
        'notes',
        'customer_phone',
        'customer_whatsapp',
        'shipping_recipient_name',
        'shipping_phone',
        'shipping_country',
        'shipping_city',
        'shipping_town',
        'shipping_street',
        'shipping_address_line',
        'shipping_building_number',
        'shipping_floor',
        'shipping_apartment',
        'shipping_landmark',
        'shipping_notes',
        'paid_at',
        'shipped_at',
        'delivered_at',
        'cancelled_at',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'payment_fee' => 'decimal:2',
        'total' => 'decimal:2',
        'shipping_weight' => 'decimal:3',
        'is_free_shipping' => 'boolean',
        'paid_at' => 'datetime',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function shippingCarrier(): BelongsTo
    {
        return $this->belongsTo(ShippingCarrier::class);
    }

    public function shippingCity(): BelongsTo
    {
        return $this->belongsTo(City::class, 'shipping_city_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function timeline(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'tracking_number', 'total', 'notes'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
