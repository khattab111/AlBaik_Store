<?php

namespace App\Models;

use App\Models\Concerns\HasStoreTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShippingMethod extends Model
{
    use HasFactory, HasStoreTranslations;

    public array $translatable = ['name', 'description'];

    protected $fillable = ['name', 'slug', 'description', 'zone', 'type', 'cost', 'free_shipping_minimum', 'rules', 'is_active'];

    protected $casts = ['cost' => 'decimal:2', 'free_shipping_minimum' => 'decimal:2', 'rules' => 'array', 'is_active' => 'boolean'];

    public function shippingRules(): HasMany
    {
        return $this->hasMany(ShippingRule::class);
    }
}
