<?php

namespace App\Models;

use App\Models\Concerns\HasStoreTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    use HasFactory, HasStoreTranslations;

    public array $translatable = ['name', 'address'];

    protected $fillable = ['name', 'slug', 'email', 'phone', 'address', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
