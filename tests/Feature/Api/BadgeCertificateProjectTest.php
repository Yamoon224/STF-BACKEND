<?php

namespace Tests\Feature\Api;

use App\Models\Badge;
use App\Models\Certificate;
use App\Models\MentorshipPairing;
use App\Models\Program;
use App\Models\Project;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BadgeCertificateProjectTest extends TestCase
{
    public function test_badge_index_returns_all_badge_types_by_default(): void
    {
        Badge::create(['title' => 'Fondations STIM']);
        Sanctum::actingAs($this->makeUser('mentee'), ['*']);

        $this->getJson('/api/badges')->assertOk()->assertJsonCount(1);
    }

    public function test_badge_index_mine_returns_only_earned_badges(): void
    {
        $badge = Badge::create(['title' => 'Fondations STIM']);
        Badge::create(['title' => 'Non obtenu']);
        $mentee = $this->makeUser('mentee');
        $badge->users()->attach($mentee->id, ['awarded_at' => now()]);

        Sanctum::actingAs($mentee, ['*']);

        $response = $this->getJson('/api/badges?mine=1');

        $response->assertOk()->assertJsonCount(1)->assertJsonPath('0.title', 'Fondations STIM');
    }

    public function test_only_admin_or_staff_can_award_a_badge(): void
    {
        $badge = Badge::create(['title' => 'Fondations STIM']);
        $mentee = $this->makeUser('mentee');

        Sanctum::actingAs($this->makeUser('mentor'), ['*']);
        $this->postJson("/api/badges/{$badge->id}/award", ['user_id' => $mentee->id])->assertForbidden();

        Sanctum::actingAs($this->makeUser('admin'), ['*']);
        $this->postJson("/api/badges/{$badge->id}/award", ['user_id' => $mentee->id])->assertOk();

        $this->assertTrue($badge->users()->where('user_id', $mentee->id)->exists());
    }

    public function test_mentee_only_sees_her_own_certificates(): void
    {
        $mentee = $this->makeUser('mentee');
        $other = $this->makeUser('mentee');
        Certificate::create([
            'user_id' => $mentee->id, 'title' => 'Certificat A', 'serial_number' => 'STF-A', 'issued_at' => now(),
        ]);
        Certificate::create([
            'user_id' => $other->id, 'title' => 'Certificat B', 'serial_number' => 'STF-B', 'issued_at' => now(),
        ]);

        Sanctum::actingAs($mentee, ['*']);

        $response = $this->getJson('/api/certificates');

        $response->assertOk()->assertJsonCount(1)->assertJsonPath('0.title', 'Certificat A');
    }

    public function test_admin_can_issue_a_certificate(): void
    {
        $mentee = $this->makeUser('mentee');
        Sanctum::actingAs($this->makeUser('admin'), ['*']);

        $response = $this->postJson('/api/certificates', [
            'user_id' => $mentee->id,
            'title' => 'Certificat Fondations STIM',
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('certificates', ['user_id' => $mentee->id, 'title' => 'Certificat Fondations STIM']);
    }

    public function test_mentee_can_create_and_submit_her_own_project(): void
    {
        $mentee = $this->makeUser('mentee');
        Sanctum::actingAs($mentee, ['*']);

        $create = $this->postJson('/api/projects', ['title' => 'Application de suivi']);
        $create->assertCreated()->assertJsonPath('status', 'brouillon');

        $id = $create->json('id');

        $submit = $this->patchJson("/api/projects/{$id}", ['submit' => true]);
        $submit->assertOk()->assertJsonPath('status', 'soumis');
    }

    public function test_mentee_cannot_set_her_own_project_status_directly(): void
    {
        $mentee = $this->makeUser('mentee');
        Sanctum::actingAs($mentee, ['*']);
        $id = $this->postJson('/api/projects', ['title' => 'Projet'])->json('id');

        $response = $this->patchJson("/api/projects/{$id}", ['status' => 'valide']);

        $response->assertOk()->assertJsonPath('status', 'brouillon');
    }

    public function test_mentee_cannot_view_another_mentees_project(): void
    {
        $owner = $this->makeUser('mentee');
        $project = Project::create(['mentee_id' => $owner->id, 'title' => 'Projet privé', 'status' => 'brouillon']);

        Sanctum::actingAs($this->makeUser('mentee'), ['*']);

        $this->getJson("/api/projects/{$project->id}")->assertForbidden();
    }

    public function test_assigned_mentor_can_view_mentees_project(): void
    {
        $program = Program::create(['name' => 'Mentorat STIM', 'slug' => 'mentorat-stim', 'status' => 'en_cours']);
        $mentee = $this->makeUser('mentee');
        $mentor = $this->makeUser('mentor');
        $pairing = MentorshipPairing::create([
            'mentee_id' => $mentee->id, 'mentor_id' => $mentor->id, 'program_id' => $program->id, 'status' => 'actif',
        ]);
        $project = Project::create([
            'mentee_id' => $mentee->id, 'pairing_id' => $pairing->id, 'title' => 'Projet', 'status' => 'soumis',
        ]);

        Sanctum::actingAs($mentor, ['*']);

        $this->getJson("/api/projects/{$project->id}")->assertOk();
    }
}
