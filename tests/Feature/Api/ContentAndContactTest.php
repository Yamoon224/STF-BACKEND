<?php

namespace Tests\Feature\Api;

use App\Models\CmsPage;
use App\Models\Faq;
use App\Models\Partner;
use App\Models\Testimonial;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ContentAndContactTest extends TestCase
{
    public function test_public_only_sees_published_cms_pages(): void
    {
        CmsPage::create(['title' => 'Accueil', 'slug' => 'accueil', 'status' => 'publie']);
        CmsPage::create(['title' => 'Brouillon', 'slug' => 'brouillon', 'status' => 'brouillon']);

        $this->getJson('/api/cms/pages')->assertOk()->assertJsonCount(1);
    }

    public function test_public_cannot_fetch_an_unpublished_page_by_slug(): void
    {
        CmsPage::create(['title' => 'Brouillon', 'slug' => 'brouillon', 'status' => 'brouillon']);

        $this->getJson('/api/cms/pages/brouillon')->assertNotFound();
    }

    public function test_staff_can_create_and_publish_a_cms_page(): void
    {
        Sanctum::actingAs($this->makeUser('staff'), ['*']);

        $response = $this->postJson('/api/cms/pages', [
            'title' => 'Nouvel article',
            'type' => 'article',
            'status' => 'publie',
        ]);

        $response->assertCreated()->assertJsonPath('slug', 'nouvel-article');
        $this->assertNotNull($response->json('published_at'));
    }

    public function test_creating_a_cms_page_without_type_or_status_uses_defaults(): void
    {
        Sanctum::actingAs($this->makeUser('staff'), ['*']);

        $response = $this->postJson('/api/cms/pages', ['title' => 'Sans détails']);

        $response->assertCreated()
            ->assertJsonPath('type', 'page')
            ->assertJsonPath('status', 'brouillon');
        $this->assertNull($response->json('published_at'));
    }

    public function test_mentee_cannot_create_cms_content(): void
    {
        Sanctum::actingAs($this->makeUser('mentee'), ['*']);

        $this->postJson('/api/cms/pages', ['title' => 'X', 'type' => 'page'])->assertForbidden();
    }

    public function test_partners_testimonials_and_faqs_are_public(): void
    {
        Partner::create(['name' => 'Fondation Numérique']);
        Testimonial::create(['name' => 'Aïcha D.', 'role' => 'Mentée', 'quote' => 'Merci STF']);
        Faq::create(['question' => 'Qui peut devenir mentée ?', 'answer' => 'Toute fille intéressée.', 'category' => 'mentorat']);

        $this->getJson('/api/partners')->assertOk()->assertJsonCount(1);
        $this->getJson('/api/testimonials')->assertOk()->assertJsonCount(1);
        $this->getJson('/api/faqs')->assertOk()->assertJsonCount(1);
    }

    public function test_only_staff_can_manage_partners(): void
    {
        Sanctum::actingAs($this->makeUser('donor'), ['*']);
        $this->postJson('/api/partners', ['name' => 'Nouveau'])->assertForbidden();

        Sanctum::actingAs($this->makeUser('staff'), ['*']);
        $this->postJson('/api/partners', ['name' => 'Nouveau'])->assertCreated();
    }

    public function test_anyone_can_submit_a_contact_message(): void
    {
        $response = $this->postJson('/api/contact', [
            'name' => 'Visiteur',
            'email' => 'visiteur@example.org',
            'audience' => 'partenaire',
            'subject' => 'Demande de partenariat',
            'message' => 'Bonjour, nous souhaitons collaborer.',
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('contact_messages', ['email' => 'visiteur@example.org', 'status' => 'nouveau']);
    }

    public function test_only_staff_can_list_contact_messages(): void
    {
        $this->postJson('/api/contact', [
            'name' => 'Visiteur', 'email' => 'v@example.org', 'subject' => 'S', 'message' => 'M',
        ]);

        Sanctum::actingAs($this->makeUser('donor'), ['*']);
        $this->getJson('/api/contact-messages')->assertForbidden();

        Sanctum::actingAs($this->makeUser('admin'), ['*']);
        $this->getJson('/api/contact-messages')->assertOk()->assertJsonCount(1, 'data');
    }
}
