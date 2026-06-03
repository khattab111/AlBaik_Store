<?php

namespace App\Models;

use App\Models\Concerns\HasAutoSlug;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NewsletterTemplate extends Model
{
    use HasAutoSlug, HasFactory;

    public const CATEGORY_OFFERS = 'offers';
    public const CATEGORY_NEW_PRODUCTS = 'new_products';
    public const CATEGORY_ANNOUNCEMENT = 'announcement';
    public const CATEGORY_WELCOME = 'welcome';
    public const CATEGORY_ABANDONED_CART = 'abandoned_cart';
    public const CATEGORY_CUSTOM = 'custom';

    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';

    protected $fillable = [
        'name',
        'slug',
        'subject_ar',
        'subject_en',
        'preheader_ar',
        'preheader_en',
        'content_ar',
        'content_en',
        'design',
        'category',
        'status',
        'is_default',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'design' => 'array',
        'is_default' => 'boolean',
    ];

    public function campaigns(): HasMany
    {
        return $this->hasMany(NewsletterCampaign::class, 'template_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function subjectFor(string $locale): string
    {
        return (string) ($locale === 'en' ? ($this->subject_en ?: $this->subject_ar) : ($this->subject_ar ?: $this->subject_en));
    }

    public function preheaderFor(string $locale): ?string
    {
        return $locale === 'en' ? ($this->preheader_en ?: $this->preheader_ar) : ($this->preheader_ar ?: $this->preheader_en);
    }

    public function contentFor(string $locale): string
    {
        return (string) ($locale === 'en' ? ($this->content_en ?: $this->content_ar) : ($this->content_ar ?: $this->content_en));
    }

    public static function categoryOptions(): array
    {
        return [
            self::CATEGORY_OFFERS => __('Offers'),
            self::CATEGORY_NEW_PRODUCTS => __('New products'),
            self::CATEGORY_ANNOUNCEMENT => __('Announcement'),
            self::CATEGORY_WELCOME => __('Welcome'),
            self::CATEGORY_ABANDONED_CART => __('Abandoned cart'),
            self::CATEGORY_CUSTOM => __('Custom'),
        ];
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_ACTIVE => __('Active'),
            self::STATUS_INACTIVE => __('Inactive'),
        ];
    }
}
