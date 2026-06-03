<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class NewsletterSubscriber extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE = 'active';
    public const STATUS_UNSUBSCRIBED = 'unsubscribed';
    public const STATUS_BOUNCED = 'bounced';

    public const SOURCE_HOMEPAGE = 'homepage';
    public const SOURCE_FOOTER = 'footer';
    public const SOURCE_CHECKOUT = 'checkout';
    public const SOURCE_MANUAL = 'manual';
    public const SOURCE_POPUP = 'popup';

    protected $fillable = [
        'email',
        'name',
        'phone',
        'locale',
        'status',
        'source',
        'verification_token',
        'verified_at',
        'unsubscribe_token',
        'unsubscribed_at',
        'metadata',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
        'unsubscribed_at' => 'datetime',
        'metadata' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (NewsletterSubscriber $subscriber): void {
            $subscriber->status ??= self::STATUS_ACTIVE;
            $subscriber->locale ??= app()->getLocale() ?: 'ar';
            $subscriber->unsubscribe_token ??= Str::random(48);
        });
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(NewsletterDelivery::class, 'subscriber_id');
    }

    public function activate(): void
    {
        $this->forceFill([
            'status' => self::STATUS_ACTIVE,
            'unsubscribed_at' => null,
            'unsubscribe_token' => $this->unsubscribe_token ?: Str::random(48),
        ])->save();
    }

    public function unsubscribe(): void
    {
        $this->forceFill([
            'status' => self::STATUS_UNSUBSCRIBED,
            'unsubscribed_at' => now(),
        ])->save();
    }

    public function markAsBounced(): void
    {
        $this->forceFill(['status' => self::STATUS_BOUNCED])->save();
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_ACTIVE => __('Active'),
            self::STATUS_UNSUBSCRIBED => __('Unsubscribed'),
            self::STATUS_BOUNCED => __('Bounced'),
        ];
    }

    public static function sourceOptions(): array
    {
        return [
            self::SOURCE_HOMEPAGE => __('Homepage'),
            self::SOURCE_FOOTER => __('Footer'),
            self::SOURCE_CHECKOUT => __('Checkout'),
            self::SOURCE_MANUAL => __('Manual'),
            self::SOURCE_POPUP => __('Popup'),
        ];
    }
}
