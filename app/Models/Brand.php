<?php

namespace App\Models;

use App\Models\Concerns\HasStoreTranslations;
use App\Models\Concerns\HasAutoSlug;
use App\Services\StorefrontCacheService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    use HasAutoSlug, HasFactory, HasStoreTranslations;

    public array $translatable = ['name', 'description'];

    protected string $slugSourceField = 'name';

    protected $fillable = ['name', 'slug', 'description', 'logo', 'status'];

    protected static function booted(): void
    {
        static::saved(fn (Brand $brand): mixed => app(StorefrontCacheService::class)->clearBrand($brand->id));
        static::deleted(fn (Brand $brand): mixed => app(StorefrontCacheService::class)->clearBrand($brand->id));
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
