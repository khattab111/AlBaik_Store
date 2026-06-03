<?php

namespace Tests\Feature;

use App\Models\NewsletterSubscriber;
use App\Models\NewsletterCampaign;
use App\Models\NewsletterDelivery;
use App\Jobs\SendNewsletterCampaignJob;
use App\Jobs\SendNewsletterEmailJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class NewsletterSubscriptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_subscribe_to_newsletter(): void
    {
        $response = $this->from('/')->post('/newsletter', [
            'email' => 'shopper@example.com',
        ]);

        $response->assertRedirect('/');
        $response->assertSessionHas('status');
        $this->assertDatabaseHas('newsletter_subscribers', [
            'email' => 'shopper@example.com',
            'status' => NewsletterSubscriber::STATUS_ACTIVE,
        ]);
    }

    public function test_duplicate_newsletter_subscription_is_idempotent(): void
    {
        NewsletterSubscriber::create(['email' => 'shopper@example.com', 'locale' => 'ar']);

        $response = $this->from('/')->post('/newsletter', [
            'email' => 'shopper@example.com',
        ]);

        $response->assertRedirect('/');
        $response->assertSessionHas('status');
        $this->assertDatabaseCount('newsletter_subscribers', 1);
    }

    public function test_unsubscribed_email_is_reactivated_when_subscribing_again(): void
    {
        NewsletterSubscriber::create([
            'email' => 'shopper@example.com',
            'locale' => 'ar',
            'status' => NewsletterSubscriber::STATUS_UNSUBSCRIBED,
            'unsubscribed_at' => now(),
        ]);

        $this->from('/')->post('/newsletter/subscribe', [
            'email' => 'shopper@example.com',
        ])->assertRedirect('/');

        $this->assertDatabaseHas('newsletter_subscribers', [
            'email' => 'shopper@example.com',
            'status' => NewsletterSubscriber::STATUS_ACTIVE,
            'unsubscribed_at' => null,
        ]);
    }

    public function test_subscriber_can_unsubscribe_with_token(): void
    {
        $subscriber = NewsletterSubscriber::create([
            'email' => 'shopper@example.com',
            'locale' => 'ar',
            'status' => NewsletterSubscriber::STATUS_ACTIVE,
        ]);

        $this->get(route('newsletter.unsubscribe', $subscriber->unsubscribe_token))
            ->assertOk()
            ->assertSee(__('You have been unsubscribed from the newsletter.'));

        $this->assertDatabaseHas('newsletter_subscribers', [
            'email' => 'shopper@example.com',
            'status' => NewsletterSubscriber::STATUS_UNSUBSCRIBED,
        ]);
    }

    public function test_campaign_job_creates_deliveries_and_queues_email_jobs(): void
    {
        Queue::fake();

        NewsletterSubscriber::create([
            'email' => 'active@example.com',
            'locale' => 'ar',
            'status' => NewsletterSubscriber::STATUS_ACTIVE,
        ]);
        NewsletterSubscriber::create([
            'email' => 'unsubscribed@example.com',
            'locale' => 'ar',
            'status' => NewsletterSubscriber::STATUS_UNSUBSCRIBED,
        ]);

        $campaign = NewsletterCampaign::create([
            'title' => 'Test campaign',
            'subject' => 'Hello {{subscriber_name}}',
            'content' => '<p>Welcome {{email}} - {{unsubscribe_url}}</p>',
            'locale' => 'ar',
            'status' => NewsletterCampaign::STATUS_DRAFT,
            'audience' => ['preset' => 'all_active'],
        ]);

        (new SendNewsletterCampaignJob($campaign->id))->handle();

        $this->assertDatabaseHas('newsletter_deliveries', [
            'campaign_id' => $campaign->id,
            'email' => 'active@example.com',
            'status' => NewsletterDelivery::STATUS_PENDING,
        ]);
        $this->assertDatabaseMissing('newsletter_deliveries', [
            'campaign_id' => $campaign->id,
            'email' => 'unsubscribed@example.com',
        ]);

        Queue::assertPushed(SendNewsletterEmailJob::class);
    }

    public function test_newsletter_requires_valid_email(): void
    {
        $response = $this->from('/')->post('/newsletter', [
            'email' => 'not-an-email',
        ]);

        $response->assertRedirect('/');
        $response->assertSessionHasErrors('email');
        $this->assertDatabaseCount('newsletter_subscribers', 0);
    }
}
