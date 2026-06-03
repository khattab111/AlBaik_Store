<?php

namespace App\Services;

use App\Models\NewsletterCampaign;
use App\Models\NewsletterSubscriber;
use Illuminate\Support\Str;

class NewsletterRenderer
{
    public function render(string $content, NewsletterCampaign $campaign, NewsletterSubscriber $subscriber): string
    {
        return strtr($content, $this->variables($campaign, $subscriber));
    }

    public function variables(NewsletterCampaign $campaign, NewsletterSubscriber $subscriber): array
    {
        $store = app(SiteSettingService::class)->identity();

        return [
            '{{store_name}}' => $store['name'] ?? config('app.name', 'AlBaik Store'),
            '{{subscriber_name}}' => $subscriber->name ?: __('Customer'),
            '{{email}}' => $subscriber->email,
            '{{unsubscribe_url}}' => route('newsletter.unsubscribe', $subscriber->unsubscribe_token),
            '{{campaign_title}}' => $campaign->title,
            '{{current_date}}' => now()->locale($campaign->locale ?: app()->getLocale())->translatedFormat('Y-m-d'),
        ];
    }

    public function previewContent(?string $content): string
    {
        $content = trim((string) $content);

        return $content !== '' ? $content : '<p>'.e(__('No content available.')).'</p>';
    }

    public function plainTextExcerpt(string $html, int $limit = 180): string
    {
        return Str::limit(trim(strip_tags($html)), $limit);
    }
}
