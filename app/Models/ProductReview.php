<?php

namespace App\Models;

use App\Services\ProductReviewRatingService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductReview extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_HIDDEN = 'hidden';

    protected $fillable = [
        'product_id',
        'user_id',
        'order_id',
        'rating',
        'title',
        'comment',
        'status',
        'admin_note',
        'approved_at',
        'approved_by',
    ];

    protected $casts = [
        'rating' => 'integer',
        'approved_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saved(function (ProductReview $review): void {
            if ($review->wasRecentlyCreated || $review->wasChanged(['status', 'rating', 'product_id'])) {
                app(ProductReviewRatingService::class)->recalculate($review->product_id);

                if ($review->wasChanged('product_id')) {
                    app(ProductReviewRatingService::class)->recalculate((int) $review->getOriginal('product_id'));
                }
            }
        });

        static::deleted(fn (ProductReview $review): mixed => app(ProductReviewRatingService::class)->recalculate($review->product_id));
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductReviewImage::class)->orderBy('sort_order');
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_PENDING => __('Pending approval'),
            self::STATUS_APPROVED => __('Approved'),
            self::STATUS_REJECTED => __('Rejected'),
            self::STATUS_HIDDEN => __('Hidden'),
        ];
    }
}
