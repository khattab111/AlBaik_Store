<?php

namespace Tests\Feature;

use App\Models\NewsletterSubscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
