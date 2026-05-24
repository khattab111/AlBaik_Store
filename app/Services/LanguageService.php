<?php

namespace App\Services;

use App\Models\Language;
use Illuminate\Support\Facades\Cache;

class LanguageService
{
    public function active(): array
    {
        return Cache::rememberForever('languages.active', function (): array {
            if (! class_exists(Language::class) || ! app('db')->getSchemaBuilder()->hasTable('languages')) {
                return config('locales.supported', []);
            }

            $languages = Language::where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get();

            if ($languages->isEmpty()) {
                return config('locales.supported', []);
            }

            return $languages
                ->mapWithKeys(fn (Language $language): array => [
                    $language->code => [
                        'name' => $language->name,
                        'native' => $language->name,
                        'direction' => $language->direction,
                        'regional' => $language->code,
                        'flag' => $language->flag,
                    ],
                ])
                ->all();
        });
    }

    public function defaultCode(): string
    {
        return Cache::rememberForever('languages.default', function (): string {
            if (! class_exists(Language::class) || ! app('db')->getSchemaBuilder()->hasTable('languages')) {
                return config('locales.default', config('app.locale', 'ar'));
            }

            return Language::where('is_default', true)->where('is_active', true)->value('code')
                ?: config('locales.default', config('app.locale', 'ar'));
        });
    }

    public static function forgetCache(): void
    {
        Cache::forget('languages.active');
        Cache::forget('languages.default');
    }
}
