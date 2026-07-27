<?php

namespace Tests\Feature\Api;

use App\Jobs\NotifyNewsletterSubscribersOfCmsPage;
use App\Mail\CmsPageNewsletterMail;
use App\Models\CmsPage;
use App\Models\NewsletterSubscriber;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CmsPageNewsletterNotificationTest extends TestCase
{
    public function test_publishing_a_new_article_notifies_active_subscribers(): void
    {
        Bus::fake();
        Sanctum::actingAs($this->makeUser('staff'), ['*']);

        $response = $this->postJson('/api/cms/pages', [
            'title' => 'Nouvel article',
            'type' => 'article',
            'status' => 'publie',
        ])->assertCreated();

        Bus::assertDispatched(
            NotifyNewsletterSubscribersOfCmsPage::class,
            fn ($job) => $job->page->id === $response->json('id')
        );
    }

    public function test_creating_a_draft_article_does_not_notify_subscribers(): void
    {
        Bus::fake();
        Sanctum::actingAs($this->makeUser('staff'), ['*']);

        $this->postJson('/api/cms/pages', [
            'title' => 'Brouillon',
            'type' => 'article',
            'status' => 'brouillon',
        ])->assertCreated();

        Bus::assertNotDispatched(NotifyNewsletterSubscribersOfCmsPage::class);
    }

    public function test_publishing_a_static_page_does_not_notify_subscribers(): void
    {
        Bus::fake();
        Sanctum::actingAs($this->makeUser('staff'), ['*']);

        $this->postJson('/api/cms/pages', [
            'title' => 'À propos',
            'type' => 'page',
            'status' => 'publie',
        ])->assertCreated();

        Bus::assertNotDispatched(NotifyNewsletterSubscribersOfCmsPage::class);
    }

    public function test_publishing_a_draft_article_via_update_notifies_subscribers_once(): void
    {
        Bus::fake();
        Sanctum::actingAs($this->makeUser('staff'), ['*']);

        $page = CmsPage::create(['title' => 'Brouillon', 'slug' => 'brouillon', 'type' => 'article', 'status' => 'brouillon']);

        $this->patchJson("/api/cms/pages/{$page->id}", ['status' => 'publie'])->assertOk();
        Bus::assertDispatchedTimes(NotifyNewsletterSubscribersOfCmsPage::class, 1);

        // Editing the already-published article again must not re-notify.
        $this->patchJson("/api/cms/pages/{$page->id}", ['title' => 'Brouillon modifié'])->assertOk();
        Bus::assertDispatchedTimes(NotifyNewsletterSubscribersOfCmsPage::class, 1);
    }

    public function test_job_queues_a_mail_for_every_active_subscriber_only(): void
    {
        Mail::fake();

        NewsletterSubscriber::create(['email' => 'active@example.org', 'status' => 'actif']);
        NewsletterSubscriber::create(['email' => 'gone@example.org', 'status' => 'desabonne']);

        $page = CmsPage::create(['title' => 'Article publié', 'slug' => 'article-publie', 'type' => 'article', 'status' => 'publie', 'published_at' => now()]);

        (new NotifyNewsletterSubscribersOfCmsPage($page))->handle();

        Mail::assertQueued(CmsPageNewsletterMail::class, 1);
        Mail::assertQueued(CmsPageNewsletterMail::class, fn ($mail) => $mail->hasTo('active@example.org'));
        Mail::assertNotQueued(CmsPageNewsletterMail::class, fn ($mail) => $mail->hasTo('gone@example.org'));
    }
}
