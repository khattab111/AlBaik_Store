<?php

namespace App\Models;

use App\Models\Concerns\HasAutoSlug;
use App\Models\Concerns\HasStoreTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShippingCarrier extends Model
{
    use HasAutoSlug, HasFactory, HasStoreTranslations;

    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';

    public array $translatable = ['name'];

    protected string $slugSourceField = 'name';

    protected $fillable = [
        'name',
        'slug',
        'logo',
        'tracking_url',
        'api_endpoint',
        'api_key',
        'status',
        'sort_order',
    ];

    protected $casts = [
        'name' => 'array',
        'sort_order' => 'integer',
    ];

    public function shippingRates(): HasMany
    {
        return $this->hasMany(ShippingRate::class);
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_ACTIVE => __('Active'),
            self::STATUS_INACTIVE => __('Inactive'),
        ];
    }
}
