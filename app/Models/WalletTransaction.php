<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;

class WalletTransaction extends Model
{
    use HasFactory;

    public const TYPE_DEPOSIT = 'deposit';
    public const TYPE_PURCHASE = 'purchase';
    public const TYPE_REFUND = 'refund';
    public const TYPE_ADJUSTMENT = 'adjustment';
    public const TYPE_BONUS = 'bonus';
    public const TYPE_HOLD = 'hold';
    public const TYPE_RELEASE = 'release';
    public const TYPE_WITHDRAW = 'withdraw';

    public const DIRECTION_CREDIT = 'credit';
    public const DIRECTION_DEBIT = 'debit';

    public const STATUS_PENDING = 'pending';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'transaction_number',
        'wallet_id',
        'user_id',
        'type',
        'direction',
        'amount',
        'balance_before',
        'balance_after',
        'status',
        'reference_type',
        'reference_id',
        'description',
        'created_by',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after' => 'decimal:2',
        'metadata' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (WalletTransaction $transaction): void {
            $transaction->transaction_number ??= (string) Str::uuid();
        });
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    public static function typeOptions(): array
    {
        return [
            self::TYPE_DEPOSIT => __('Deposit'),
            self::TYPE_PURCHASE => __('Purchase'),
            self::TYPE_REFUND => __('Refund'),
            self::TYPE_ADJUSTMENT => __('Adjustment'),
            self::TYPE_BONUS => __('Bonus'),
            self::TYPE_HOLD => __('Hold'),
            self::TYPE_RELEASE => __('Release'),
            self::TYPE_WITHDRAW => __('Withdraw'),
        ];
    }

    public static function directionOptions(): array
    {
        return [
            self::DIRECTION_CREDIT => __('Credit'),
            self::DIRECTION_DEBIT => __('Debit'),
        ];
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_PENDING => __('Pending'),
            self::STATUS_COMPLETED => __('Completed'),
            self::STATUS_FAILED => __('Failed'),
            self::STATUS_CANCELLED => __('Cancelled'),
        ];
    }
}
