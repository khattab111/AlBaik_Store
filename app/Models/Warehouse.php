<?php

namespace App\Models;

use App\Models\Concerns\HasStoreTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Warehouse extends Model
{
    use HasFactory, HasStoreTranslations;

    public array $translatable = ['name', 'address', 'city', 'country'];

    protected $fillable = ['name', 'code', 'address', 'city', 'country', 'is_active'];

    public function inventory(): HasMany
    {
        return $this->hasMany(InventoryMovement::class);
    }
}
