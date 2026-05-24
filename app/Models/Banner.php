<?php

namespace App\Models;

use App\Models\Concerns\HasStoreTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    use HasFactory, HasStoreTranslations;

    public array $translatable = ['title', 'subtitle', 'eyebrow', 'primary_button_text', 'secondary_button_text'];

    protected $fillable = [
        'title',
        'eyebrow',
        'primary_button_text',
        'secondary_button_text',
        'title_ar',
        'title_en',
        'subtitle',
        'subtitle_ar',
        'subtitle_en',
        'eyebrow_ar',
        'eyebrow_en',
        'image',
        'url',
        'primary_button_text_ar',
        'primary_button_text_en',
        'secondary_button_text_ar',
        'secondary_button_text_en',
        'secondary_url',
        'background_color',
        'text_color',
        'placement',
        'sort_order',
        'is_active',
        'starts_at',
        'ends_at',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

}
