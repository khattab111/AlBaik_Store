<?php

namespace App\Models\Concerns;

use Spatie\Translatable\HasTranslations;

trait HasStoreTranslations
{
    use HasTranslations;

    public function localized(string $attribute, ?string $fallback = null, ?string $locale = null): ?string
    {
        $locale ??= app()->getLocale();

        $value = $this->getTranslation($attribute, $locale, false)
            ?: $this->getTranslation($attribute, config('app.fallback_locale', 'en'), false)
            ?: $this->getTranslation($attribute, config('locales.default', 'ar'), false);

        return filled($value) ? $value : $fallback;
    }
}
