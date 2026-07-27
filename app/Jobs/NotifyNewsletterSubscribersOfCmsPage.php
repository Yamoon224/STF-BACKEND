<?php

namespace App\Jobs;

use App\Mail\CmsPageNewsletterMail;
use App\Models\CmsPage;
use App\Models\NewsletterSubscriber;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class NotifyNewsletterSubscribersOfCmsPage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    public function __construct(public CmsPage $page) {}

    public function handle(): void
    {
        NewsletterSubscriber::where('status', 'actif')
            ->chunkById(100, function ($subscribers) {
                foreach ($subscribers as $subscriber) {
                    Mail::to($subscriber->email)->queue(new CmsPageNewsletterMail($this->page, $subscriber));
                }
            });
    }
}
