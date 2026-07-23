<?php

namespace Tests\Feature\Api;

use App\Models\NewsletterSubscriber;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class NewsletterTest extends TestCase
{
    public function test_anyone_can_subscribe_to_the_newsletter(): void
    {
        $response = $this->postJson('/api/newsletter/subscribe', [
            'email' => 'visiteuse@example.org',
            'name' => 'Visiteuse',
        ]);

        $response->assertCreated()->assertJsonPath('status', 'actif');
        $this->assertDatabaseHas('newsletter_subscribers', [
            'email' => 'visiteuse@example.org',
            'status' => 'actif',
        ]);
    }

    public function test_subscribing_twice_with_the_same_email_does_not_duplicate_the_row(): void
    {
        $this->postJson('/api/newsletter/subscribe', ['email' => 'visiteuse@example.org'])->assertCreated();
        $this->postJson('/api/newsletter/subscribe', ['email' => 'visiteuse@example.org'])->assertCreated();

        $this->assertSame(1, NewsletterSubscriber::where('email', 'visiteuse@example.org')->count());
    }

    public function test_unsubscribing_reactivates_on_a_later_resubscribe(): void
    {
        $this->postJson('/api/newsletter/subscribe', ['email' => 'visiteuse@example.org'])->assertCreated();
        $this->postJson('/api/newsletter/unsubscribe', ['email' => 'visiteuse@example.org'])->assertOk();

        $this->assertDatabaseHas('newsletter_subscribers', [
            'email' => 'visiteuse@example.org',
            'status' => 'desabonne',
        ]);

        $this->postJson('/api/newsletter/subscribe', ['email' => 'visiteuse@example.org'])->assertCreated();

        $this->assertDatabaseHas('newsletter_subscribers', [
            'email' => 'visiteuse@example.org',
            'status' => 'actif',
        ]);
    }

    public function test_only_staff_can_list_and_delete_subscribers(): void
    {
        $this->postJson('/api/newsletter/subscribe', ['email' => 'visiteuse@example.org'])->assertCreated();
        $subscriber = NewsletterSubscriber::firstOrFail();

        Sanctum::actingAs($this->makeUser('donor'), ['*']);
        $this->getJson('/api/newsletter/subscribers')->assertForbidden();
        $this->deleteJson("/api/newsletter/subscribers/{$subscriber->id}")->assertForbidden();

        Sanctum::actingAs($this->makeUser('admin'), ['*']);
        $this->getJson('/api/newsletter/subscribers')->assertOk()->assertJsonCount(1, 'data');
        $this->deleteJson("/api/newsletter/subscribers/{$subscriber->id}")->assertNoContent();
        $this->assertDatabaseMissing('newsletter_subscribers', ['id' => $subscriber->id]);
    }
}
