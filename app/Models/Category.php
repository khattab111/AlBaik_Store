<?php

namespace App\Models;

use App\Models\Concerns\HasStoreTranslations;
use App\Models\Concerns\HasAutoSlug;
use App\Services\StorefrontCacheService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasAutoSlug, HasFactory, HasStoreTranslations;

    public array $translatable = ['name', 'description'];

    protected string $slugSourceField = 'name';

    protected $fillable = ['name', 'slug', 'description', 'parent_id', 'status'];

    protected static function booted(): void
    {
        static::saved(fn (Category $category): mixed => app(StorefrontCacheService::class)->clearCategory($category->id));
        static::deleted(fn (Category $category): mixed => app(StorefrontCacheService::class)->clearCategory($category->id));
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }
}
