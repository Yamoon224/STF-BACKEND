<?php

namespace Tests\Feature\Api;

use App\Models\PageSection;
use App\Models\SiteSetting;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SiteContentTest extends TestCase
{
    public function test_site_settings_are_public(): void
    {
        SiteSetting::create(['key' => 'phone', 'value' => '+225 00 00 00 00']);

        $this->getJson('/api/site-settings')
            ->assertOk()
            ->assertJsonPath('phone', '+225 00 00 00 00');
    }

    public function test_only_staff_can_update_site_settings(): void
    {
        Sanctum::actingAs($this->makeUser('donor'), ['*']);
        $this->patchJson('/api/site-settings', ['phone' => '+225 11 11 11 11'])->assertForbidden();

        Sanctum::actingAs($this->makeUser('staff'), ['*']);
        $this->patchJson('/api/site-settings', ['phone' => '+225 11 11 11 11'])
            ->assertOk()
            ->assertJsonPath('phone', '+225 11 11 11 11');

        $this->assertDatabaseHas('site_settings', ['key' => 'phone', 'value' => '+225 11 11 11 11']);
    }

    public function test_site_settings_update_rejects_unknown_keys(): void
    {
        Sanctum::actingAs($this->makeUser('staff'), ['*']);

        $this->patchJson('/api/site-settings', ['not_a_real_setting' => 'x'])->assertOk();

        $this->assertDatabaseMissing('site_settings', ['key' => 'not_a_real_setting']);
    }

    public function test_page_sections_are_public_and_filterable_by_page(): void
    {
        PageSection::create(['page_key' => 'impact', 'section_key' => 'hero', 'type' => 'hero', 'payload' => ['title' => 'Impact']]);
        PageSection::create(['page_key' => 'contact', 'section_key' => 'hero', 'type' => 'hero', 'payload' => ['title' => 'Contact']]);

        $this->getJson('/api/page-sections?page=impact')
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.payload.title', 'Impact');
    }

    public function test_only_staff_can_update_a_page_section(): void
    {
        $section = PageSection::create([
            'page_key' => 'impact',
            'section_key' => 'hero',
            'type' => 'hero',
            'payload' => ['title' => 'Impact'],
        ]);

        Sanctum::actingAs($this->makeUser('donor'), ['*']);
        $this->patchJson("/api/page-sections/{$section->id}", ['payload' => ['title' => 'Hack']])->assertForbidden();

        Sanctum::actingAs($this->makeUser('staff'), ['*']);
        $this->patchJson("/api/page-sections/{$section->id}", ['payload' => ['title' => 'Nouvel impact']])
            ->assertOk()
            ->assertJsonPath('payload.title', 'Nouvel impact');
    }
}
