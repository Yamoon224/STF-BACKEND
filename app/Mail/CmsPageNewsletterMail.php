<?php

namespace App\Mail;

use App\Models\CmsPage;
use App\Models\NewsletterSubscriber;
use App\Models\SiteSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class CmsPageNewsletterMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public CmsPage $page,
        public NewsletterSubscriber $subscriber,
    ) {}

    public function envelope(): Envelope
    {
        $label = $this->page->category ?: 'Actualité';

        return new Envelope(
            subject: "{$label} : {$this->page->title}",
        );
    }

    public function content(): Content
    {
        $settings = SiteSetting::query()->pluck('value', 'key');
        $frontendUrl = rtrim(config('app.frontend_url'), '/');

        return new Content(
            view: 'emails.newsletter.cms-page',
            with: [
                'page' => $this->page,
                'settings' => $settings,
                'articleUrl' => "{$frontendUrl}/blog/{$this->page->slug}",
                'unsubscribeUrl' => URL::signedRoute('newsletter.unsubscribe', ['subscriber' => $this->subscriber->id]),
            ],
        );
    }
}
