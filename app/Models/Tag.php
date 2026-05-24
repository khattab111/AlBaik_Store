<?php

namespace App\Models;

use App\Models\Concerns\HasStoreTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tag extends Model
{
    use HasFactory, HasStoreTranslations;

    public array $translatable = ['name'];

    protected $fillable = ['name', 'slug', 'status'];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class);
    }
}
