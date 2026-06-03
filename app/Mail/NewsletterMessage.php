<?php

namespace App\Mail;

use App\Models\NewsletterCampaign;
use App\Models\NewsletterSubscriber;
use App\Services\NewsletterRenderer;
use App\Services\SiteSettingService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewsletterMessage extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public NewsletterCampaign $campaign,
        public NewsletterSubscriber $subscriber,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: app(NewsletterRenderer::class)->render($this->campaign->subject, $this->campaign, $this->subscriber));
    }

    public function content(): Content
    {
        $renderer = app(NewsletterRenderer::class);
        $identity = app(SiteSettingService::class)->identity();
        $contact = app(SiteSettingService::class)->contact();

        return new Content(
            view: 'emails.newsletter.layout',
            with: [
                'campaign' => $this->campaign,
                'subscriber' => $this->subscriber,
                'subject' => $renderer->render($this->campaign->subject, $this->campaign, $this->subscriber),
                'preheader' => $renderer->render((string) $this->campaign->preheader, $this->campaign, $this->subscriber),
                'content' => $renderer->render($this->campaign->content, $this->campaign, $this->subscriber),
                'unsubscribeUrl' => route('newsletter.unsubscribe', $this->subscriber->unsubscribe_token),
                'direction' => $this->campaign->locale === 'en' ? 'ltr' : 'rtl',
                'locale' => $this->campaign->locale ?: app()->getLocale(),
                'identity' => $identity,
                'contact' => $contact,
            ],
        );
    }
}
