<?php

namespace App\Filament\Pages;

use App\Models\NewsletterCampaign;
use App\Models\NewsletterDelivery;
use App\Models\NewsletterSubscriber;
use App\Traits\TranslationTrait;
use Filament\Pages\Page;

class NewsletterDashboard extends Page
{
     use TranslationTrait;
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static ?string $navigationGroup = 'Newsletter';

    protected static ?int $navigationSort = 29;

    protected static string $view = 'filament.pages.newsletter-dashboard';

    public static function getNavigationLabel(): string
    {
        return __('Newsletter Dashboard');
    }

    public function getTitle(): string
    {
        return __('Newsletter Dashboard');
    }

    public function stats(): array
    {
        $sent = NewsletterDelivery::where('status', NewsletterDelivery::STATUS_SENT)->count();
        $failed = NewsletterDelivery::where('status', NewsletterDelivery::STATUS_FAILED)->count();
        $totalDeliveries = max(1, $sent + $failed);

        return [
            'total_subscribers' => NewsletterSubscriber::count(),
            'active_subscribers' => NewsletterSubscriber::where('status', NewsletterSubscriber::STATUS_ACTIVE)->count(),
            'unsubscribed' => NewsletterSubscriber::where('status', NewsletterSubscriber::STATUS_UNSUBSCRIBED)->count(),
            'sent_campaigns' => NewsletterCampaign::where('status', NewsletterCampaign::STATUS_SENT)->count(),
            'success_rate' => round(($sent / $totalDeliveries) * 100, 2),
            'sources' => NewsletterSubscriber::query()
                ->selectRaw('COALESCE(source, "unknown") as source, COUNT(*) as total')
                ->groupBy('source')
                ->orderByDesc('total')
                ->limit(5)
                ->pluck('total', 'source')
                ->all(),
            'latest_campaigns' => NewsletterCampaign::latest()->limit(5)->get(),
            'latest_subscribers' => NewsletterSubscriber::latest()->limit(5)->get(),
        ];
    }
}
