<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ElectronicServiceOrder extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_REFUNDED = 'refunded';

    public const PAYMENT_PAID = 'paid';
    public const PAYMENT_REFUNDED = 'refunded';

    protected $fillable = [
        'order_number',
        'order_uuid',
        'user_id',
        'electronic_service_id',
        'electronic_service_provider_id',
        'wallet_transaction_id',
        'service_snapshot',
        'customer_inputs',
        'amount',
        'cost',
        'status',
        'payment_status',
        'provider_reference',
        'provider_order_id',
        'execution_type',
        'input_data',
        'quantity',
        'provider_cost_at_order',
        'selling_price_at_order',
        'profit_at_order',
        'total',
        'provider_status',
        'provider_response',
        'failure_reason',
        'processed_by',
        'admin_note',
        'processed_at',
        'completed_at',
        'cancelled_at',
    ];

    protected $casts = [
        'service_snapshot' => 'array',
        'customer_inputs' => 'array',
        'input_data' => 'array',
        'amount' => 'decimal:2',
        'cost' => 'decimal:2',
        'provider_cost_at_order' => 'decimal:2',
        'selling_price_at_order' => 'decimal:2',
        'profit_at_order' => 'decimal:2',
        'total' => 'decimal:2',
        'provider_response' => 'array',
        'processed_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (ElectronicServiceOrder $order): void {
            $order->order_number ??= 'ES-'.now()->format('ymd').'-'.Str::upper(Str::random(6));
            $order->order_uuid ??= (string) Str::uuid();
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(ElectronicService::class, 'electronic_service_id');
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(ElectronicServiceProvider::class, 'electronic_service_provider_id');
    }

    public function walletTransaction(): BelongsTo
    {
        return $this->belongsTo(WalletTransaction::class);
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_PENDING => __('Pending'),
            self::STATUS_PROCESSING => __('Processing'),
            self::STATUS_COMPLETED => __('Completed'),
            self::STATUS_FAILED => __('Failed'),
            self::STATUS_CANCELLED => __('Cancelled'),
            self::STATUS_REFUNDED => __('Refunded'),
        ];
    }

    public static function paymentStatusOptions(): array
    {
        return [
            self::PAYMENT_PAID => __('Paid'),
            self::PAYMENT_REFUNDED => __('Refunded'),
        ];
    }
}
