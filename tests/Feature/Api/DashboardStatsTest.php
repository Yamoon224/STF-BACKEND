<?php

namespace Tests\Feature\Api;

use App\Models\MentorProfile;
use App\Models\MentorshipPairing;
use App\Models\MentorshipSession;
use App\Models\Program;
use App\Models\Report;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DashboardStatsTest extends TestCase
{
    public function test_impact_stats_are_public_and_reflect_real_data(): void
    {
        $program = Program::create(['name' => 'Mentorat STIM', 'slug' => 'mentorat-stim', 'status' => 'en_cours']);
        $mentee = $this->makeUser('mentee', ['country' => 'Sénégal']);
        $mentor = $this->makeUser('mentor', ['country' => 'Mali']);
        MentorProfile::create(['user_id' => $mentor->id, 'expertise' => 'X', 'validated_at' => now()]);
        MentorshipPairing::create(['mentee_id' => $mentee->id, 'mentor_id' => $mentor->id, 'program_id' => $program->id, 'status' => 'actif']);

        $response = $this->getJson('/api/stats/impact');

        $response->assertOk()
            ->assertJsonPath('beneficiaries', 1)
            ->assertJsonPath('active_mentors', 1)
            ->assertJsonPath('pairings', 1)
            ->assertJsonPath('countries', 2);
    }

    public function test_dashboard_endpoints_require_users_view_permission(): void
    {
        Sanctum::actingAs($this->makeUser('mentee'), ['*']);

        $this->getJson('/api/dashboard/kpis')->assertForbidden();
        $this->getJson('/api/dashboard/alerts')->assertForbidden();
        $this->getJson('/api/dashboard/activity-by-program')->assertForbidden();
    }

    public function test_kpis_reflect_real_counts(): void
    {
        $program = Program::create(['name' => 'Mentorat STIM', 'slug' => 'mentorat-stim', 'status' => 'en_cours']);
        $mentee = $this->makeUser('mentee', ['status' => 'active']);
        $mentor = $this->makeUser('mentor');
        MentorProfile::create(['user_id' => $mentor->id, 'expertise' => 'X', 'validated_at' => now()]);
        $pairing = MentorshipPairing::create(['mentee_id' => $mentee->id, 'mentor_id' => $mentor->id, 'program_id' => $program->id, 'status' => 'actif']);
        MentorshipSession::create(['pairing_id' => $pairing->id, 'scheduled_at' => now(), 'status' => 'realisee']);

        Sanctum::actingAs($this->makeUser('admin'), ['*']);

        $response = $this->getJson('/api/dashboard/kpis');

        $response->assertOk()
            ->assertJsonPath('active_mentees', 1)
            ->assertJsonPath('validated_mentors', 1)
            ->assertJsonPath('active_pairings', 1)
            ->assertJsonPath('sessions_this_month', 1);
    }

    public function test_alerts_reflect_pending_mentors_and_open_reports(): void
    {
        $this->makeUser('mentor', ['status' => 'pending']);
        Report::create(['reporter_id' => $this->makeUser('mentee')->id, 'context_type' => 'groupe', 'description' => 'X', 'status' => 'nouveau']);

        Sanctum::actingAs($this->makeUser('admin'), ['*']);

        $response = $this->getJson('/api/dashboard/alerts');

        $response->assertOk()
            ->assertJsonPath('pending_mentors', 1)
            ->assertJsonPath('open_reports', 1);
    }
}
