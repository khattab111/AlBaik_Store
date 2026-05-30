<?php

namespace App\Models;

use App\Models\Concerns\HasAutoSlug;
use App\Models\Concerns\HasStoreTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class City extends Model
{
    use HasAutoSlug, HasFactory, HasStoreTranslations;

    public array $translatable = ['name'];

    protected string $slugSourceField = 'name';

    protected $fillable = [
        'name',
        'slug',
        'country',
        'code',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'name' => 'array',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function shippingRates(): HasMany
    {
        return $this->hasMany(ShippingRate::class);
    }
}
