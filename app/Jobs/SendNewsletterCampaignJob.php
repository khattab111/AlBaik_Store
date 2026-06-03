<?php

namespace App\Jobs;

use App\Models\NewsletterCampaign;
use App\Models\NewsletterDelivery;
use App\Models\NewsletterSubscriber;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SendNewsletterCampaignJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $campaignId) {}

    public function handle(): void
    {
        $campaign = NewsletterCampaign::find($this->campaignId);

        if (! $campaign || ! $campaign->canBeSent()) {
            return;
        }

        DB::transaction(function () use ($campaign): void {
            $campaign->forceFill([
                'status' => NewsletterCampaign::STATUS_SENDING,
                'started_at' => $campaign->started_at ?: now(),
            ])->save();

            $subscribers = $this->audienceQuery($campaign)->get();
            $created = 0;

            foreach ($subscribers as $subscriber) {
                if (! $subscriber->unsubscribe_token) {
                    $subscriber->activate();
                }

                $delivery = NewsletterDelivery::firstOrCreate(
                    [
                        'campaign_id' => $campaign->id,
                        'subscriber_id' => $subscriber->id,
                    ],
                    [
                        'email' => $subscriber->email,
                        'subject' => $campaign->subject,
                        'status' => NewsletterDelivery::STATUS_PENDING,
                    ],
                );

                if ($delivery->wasRecentlyCreated || $delivery->status === NewsletterDelivery::STATUS_PENDING) {
                    SendNewsletterEmailJob::dispatch($delivery->id);
                    $created++;
                }
            }

            $campaign->forceFill([
                'stats' => array_merge($campaign->stats ?? [], [
                    'targeted' => $subscribers->count(),
                    'queued' => $created,
                    'sent' => (int) $campaign->deliveries()->where('status', NewsletterDelivery::STATUS_SENT)->count(),
                    'failed' => (int) $campaign->deliveries()->where('status', NewsletterDelivery::STATUS_FAILED)->count(),
                    'skipped' => (int) $campaign->deliveries()->where('status', NewsletterDelivery::STATUS_SKIPPED)->count(),
                ]),
            ])->save();
        });

        Log::info('Newsletter campaign queued.', ['campaign_id' => $campaign->id]);
    }

    private function audienceQuery(NewsletterCampaign $campaign): Builder
    {
        $audience = $campaign->audience ?? [];
        $query = NewsletterSubscriber::query()->where('status', NewsletterSubscriber::STATUS_ACTIVE);

        if (($audience['locale'] ?? null)) {
            $query->where('locale', $audience['locale']);
        } elseif (($audience['preset'] ?? null) === 'by_locale') {
            $query->where('locale', $campaign->locale);
        }

        if (($audience['source'] ?? null)) {
            $query->where('source', $audience['source']);
        }

        if (($audience['subscriber_ids'] ?? null) && is_array($audience['subscriber_ids'])) {
            $query->whereIn('id', $audience['subscriber_ids']);
        }

        return $query->orderBy('id');
    }
}
