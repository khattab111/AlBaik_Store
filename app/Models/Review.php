<?php

namespace App\Models;

use App\Models\Concerns\HasStoreTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    use HasFactory, HasStoreTranslations;

    public array $translatable = ['title', 'comment'];

    protected $fillable = ['product_id', 'user_id', 'rating', 'title', 'comment', 'images', 'is_published'];

    protected $casts = ['rating' => 'integer', 'images' => 'array', 'is_published' => 'boolean'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
