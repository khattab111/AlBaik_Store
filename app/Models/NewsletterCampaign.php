<?php

namespace App\Models;

use App\Models\Concerns\HasAutoSlug;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NewsletterCampaign extends Model
{
    use HasAutoSlug, HasFactory;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_SCHEDULED = 'scheduled';
    public const STATUS_QUEUED = 'queued';
    public const STATUS_SENDING = 'sending';
    public const STATUS_SENT = 'sent';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_FAILED = 'failed';

    protected string $slugSourceField = 'title';

    protected $fillable = [
        'title',
        'slug',
        'template_id',
        'subject',
        'preheader',
        'content',
        'locale',
        'audience',
        'status',
        'scheduled_at',
        'started_at',
        'sent_at',
        'created_by',
        'updated_by',
        'stats',
    ];

    protected $casts = [
        'audience' => 'array',
        'scheduled_at' => 'datetime',
        'started_at' => 'datetime',
        'sent_at' => 'datetime',
        'stats' => 'array',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(NewsletterTemplate::class, 'template_id');
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(NewsletterDelivery::class, 'campaign_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopeReadyToSend(Builder $query): Builder
    {
        return $query
            ->where('status', self::STATUS_SCHEDULED)
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', now());
    }

    public function canBeSent(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_SCHEDULED, self::STATUS_FAILED], true)
            && filled($this->subject)
            && filled(strip_tags($this->content));
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_DRAFT => __('Draft'),
            self::STATUS_SCHEDULED => __('Scheduled'),
            self::STATUS_QUEUED => __('Queued'),
            self::STATUS_SENDING => __('Sending'),
            self::STATUS_SENT => __('Sent'),
            self::STATUS_CANCELLED => __('Cancelled'),
            self::STATUS_FAILED => __('Failed'),
        ];
    }

    public static function audiencePresetOptions(): array
    {
        return [
            'all_active' => __('All active subscribers'),
            'by_locale' => __('By locale'),
            'by_source' => __('By source'),
            'selected' => __('Selected subscribers'),
        ];
    }
}
