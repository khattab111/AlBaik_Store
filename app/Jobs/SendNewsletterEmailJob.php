<?php

namespace App\Jobs;

use App\Mail\NewsletterMessage;
use App\Models\NewsletterCampaign;
use App\Models\NewsletterDelivery;
use App\Models\NewsletterSubscriber;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendNewsletterEmailJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $deliveryId) {}

    public function handle(): void
    {
        $delivery = NewsletterDelivery::with(['campaign', 'subscriber'])->find($this->deliveryId);

        if (! $delivery || ! $delivery->campaign) {
            return;
        }

        $subscriber = $delivery->subscriber;

        if (! $subscriber || ! $subscriber->isActive() || ! $subscriber->unsubscribe_token) {
            $delivery->forceFill([
                'status' => NewsletterDelivery::STATUS_SKIPPED,
                'error_message' => __('Subscriber is not active or unsubscribe token is missing.'),
            ])->save();

            $this->refreshCampaignStats($delivery->campaign);

            return;
        }

        try {
            Mail::to($delivery->email)->send(new NewsletterMessage($delivery->campaign, $subscriber));

            $delivery->forceFill([
                'status' => NewsletterDelivery::STATUS_SENT,
                'sent_at' => now(),
                'error_message' => null,
            ])->save();
        } catch (Throwable $exception) {
            report($exception);

            $delivery->forceFill([
                'status' => NewsletterDelivery::STATUS_FAILED,
                'error_message' => $exception->getMessage(),
            ])->save();

            Log::warning('Newsletter email failed.', [
                'delivery_id' => $delivery->id,
                'email' => $delivery->email,
            ]);
        }

        $this->refreshCampaignStats($delivery->campaign);
    }

    private function refreshCampaignStats(NewsletterCampaign $campaign): void
    {
        $total = $campaign->deliveries()->count();
        $pending = $campaign->deliveries()->where('status', NewsletterDelivery::STATUS_PENDING)->count();
        $sent = $campaign->deliveries()->where('status', NewsletterDelivery::STATUS_SENT)->count();
        $failed = $campaign->deliveries()->where('status', NewsletterDelivery::STATUS_FAILED)->count();
        $skipped = $campaign->deliveries()->where('status', NewsletterDelivery::STATUS_SKIPPED)->count();

        $campaign->forceFill([
            'status' => $pending > 0 ? NewsletterCampaign::STATUS_SENDING : NewsletterCampaign::STATUS_SENT,
            'sent_at' => $pending > 0 ? null : now(),
            'stats' => [
                'targeted' => $total,
                'sent' => $sent,
                'failed' => $failed,
                'skipped' => $skipped,
                'success_rate' => $total > 0 ? round(($sent / $total) * 100, 2) : 0,
            ],
        ])->save();
    }
}
