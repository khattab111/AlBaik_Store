<?php

namespace App\Models;

use App\Models\Concerns\HasAutoSlug;
use App\Models\Concerns\HasStoreTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;

class ElectronicService extends Model
{
    use HasAutoSlug, HasFactory, HasStoreTranslations;

    public const TYPE_MANUAL = 'manual';
    public const TYPE_API = 'api';

    public array $translatable = ['name', 'description', 'instructions'];

    protected string $slugSourceField = 'name';

    protected $fillable = [
        'electronic_service_category_id',
        'electronic_service_provider_id',
        'provider_service_id',
        'name',
        'slug',
        'description',
        'image',
        'instructions',
        'service_type',
        'provider_cost_price',
        'retail_profit_type',
        'retail_profit_value',
        'wholesale_profit_type',
        'wholesale_profit_value',
        'price',
        'wholesale_price',
        'cost',
        'min_amount',
        'max_amount',
        'fields_schema',
        'required_fields',
        'metadata',
        'is_available',
        'is_visible',
        'requires_admin_review',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'wholesale_price' => 'decimal:2',
        'cost' => 'decimal:2',
        'provider_cost_price' => 'decimal:2',
        'retail_profit_value' => 'decimal:2',
        'wholesale_profit_value' => 'decimal:2',
        'min_amount' => 'decimal:2',
        'max_amount' => 'decimal:2',
        'fields_schema' => 'array',
        'required_fields' => 'array',
        'metadata' => 'array',
        'is_available' => 'boolean',
        'is_visible' => 'boolean',
        'requires_admin_review' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(ElectronicServiceCategory::class, 'electronic_service_category_id');
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(ElectronicServiceProvider::class, 'electronic_service_provider_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(ElectronicServiceOrder::class);
    }

    public static function typeOptions(): array
    {
        return [
            self::TYPE_MANUAL => __('Manual fulfillment'),
            self::TYPE_API => __('API fulfillment'),
        ];
    }

    public function visibleFields(): array
    {
        return collect($this->required_fields ?: $this->fields_schema ?? [])
            ->filter(fn ($field): bool => is_array($field) && filled($field['name'] ?? null))
            ->values()
            ->all();
    }

    public function priceForUser(?User $user = null): float
    {
        if ($user?->isWholesaleCustomer() && (float) $this->wholesale_price > 0) {
            return (float) $this->wholesale_price;
        }

        return (float) $this->price;
    }
}
