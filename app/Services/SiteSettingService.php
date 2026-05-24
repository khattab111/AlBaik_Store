<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SiteSettingService
{
    public function publicSettings(): array
    {
        return Cache::rememberForever('site.public_settings', function (): array {
            return Setting::where('is_public', true)
                ->get()
                ->mapWithKeys(fn (Setting $setting): array => [$setting->key => $setting->value])
                ->all();
        });
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $settings = $this->publicSettings();
        $value = array_key_exists($key, $settings) ? $settings[$key] : null;

        if (is_array($value) && array_key_exists('value', $value)) {
            return $value['value'];
        }

        return $value ?? $default;
    }

    public function localized(string $key, ?string $default = null, ?string $locale = null): ?string
    {
        $locale ??= app()->getLocale();
        $settings = $this->publicSettings();
        $value = array_key_exists($key, $settings) ? $settings[$key] : null;

        if (is_array($value)) {
            return $value[$locale]
                ?? $value[config('app.fallback_locale', 'en')]
                ?? $value['value']
                ?? $default;
        }

        return $value ?: $default;
    }

    public function identity(): array
    {
        return [
            'name' => $this->localized('store.name', __('AlBaik Store')),
            'tagline' => $this->localized('store.tagline', __('Premium Market')),
            'short_description' => $this->localized('store.short_description', __('Original products, competitive prices, and a complete shopping experience for retail and wholesale customers.')),
            'logo' => $this->get('store.logo'),
            'favicon' => $this->get('store.favicon'),
            'default_product_image' => $this->get('store.default_product_image'),
            'primary_color' => $this->get('store.primary_color', '#b91c1c'),
            'primary_hover_color' => $this->get('store.primary_hover_color', '#991b1b'),
            'accent_color' => $this->get('store.accent_color', '#f59e0b'),
            'topbar_color' => $this->get('store.topbar_color', '#020617'),
            'header_bg_color' => $this->get('store.header_bg_color', '#ffffff'),
            'nav_bg_color' => $this->get('store.nav_bg_color', '#ffffff'),
            'body_bg_color' => $this->get('store.body_bg_color', '#f8fafc'),
            'surface_color' => $this->get('store.surface_color', '#ffffff'),
            'surface_tint_color' => $this->get('store.surface_tint_color', '#fff5f5'),
            'text_color' => $this->get('store.text_color', '#0f172a'),
            'muted_text_color' => $this->get('store.muted_text_color', '#64748b'),
            'border_color' => $this->get('store.border_color', '#e2e8f0'),
            'hero_overlay_from' => $this->get('store.hero_overlay_from', 'rgba(2,6,23,.96)'),
            'hero_overlay_to' => $this->get('store.hero_overlay_to', 'rgba(185,28,28,.52)'),
        ];
    }

    public function contact(): array
    {
        return [
            'email' => $this->get('contact.email', 'support@albaikstore.local'),
            'phone' => $this->get('contact.phone', '+963 900 000 000'),
            'whatsapp' => $this->get('contact.whatsapp', '+963 900 000 000'),
            'address' => $this->localized('contact.address', __('Syria')),
            'working_hours' => $this->localized('contact.working_hours', __('Daily from 9:00 to 18:00')),
            'map_url' => $this->get('contact.map_url'),
        ];
    }

    public function social(): array
    {
        return collect([
            'facebook' => $this->get('social.facebook'),
            'instagram' => $this->get('social.instagram'),
            'youtube' => $this->get('social.youtube'),
            'tiktok' => $this->get('social.tiktok'),
            'x' => $this->get('social.x'),
            'linkedin' => $this->get('social.linkedin'),
        ])->filter()->all();
    }

    public static function forgetCache(): void
    {
        Cache::forget('site.public_settings');
    }
}
