<?php

namespace Tests\Feature\Api;

use App\Models\MentorProfile;
use App\Models\MentorshipPairing;
use App\Models\Program;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PairingAndMatchingTest extends TestCase
{
    protected function program(): Program
    {
        return Program::create(['name' => 'Mentorat STIM', 'slug' => 'mentorat-stim', 'status' => 'en_cours']);
    }

    public function test_mentee_only_sees_her_own_pairing(): void
    {
        $program = $this->program();
        $mentee = $this->makeUser('mentee');
        $otherMentee = $this->makeUser('mentee');

        $mine = MentorshipPairing::create([
            'mentee_id' => $mentee->id, 'program_id' => $program->id, 'status' => 'actif',
        ]);
        MentorshipPairing::create([
            'mentee_id' => $otherMentee->id, 'program_id' => $program->id, 'status' => 'actif',
        ]);

        Sanctum::actingAs($mentee, ['*']);

        $response = $this->getJson('/api/pairings');

        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id');
        $this->assertEquals([$mine->id], $ids->all());
    }

    public function test_admin_sees_all_pairings(): void
    {
        $program = $this->program();
        MentorshipPairing::create(['mentee_id' => $this->makeUser('mentee')->id, 'program_id' => $program->id, 'status' => 'actif']);
        MentorshipPairing::create(['mentee_id' => $this->makeUser('mentee')->id, 'program_id' => $program->id, 'status' => 'en_attente']);

        Sanctum::actingAs($this->makeUser('admin'), ['*']);

        $this->getJson('/api/pairings')->assertOk()->assertJsonCount(2, 'data');
    }

    public function test_mentee_cannot_view_another_mentees_pairing(): void
    {
        $program = $this->program();
        $mentee = $this->makeUser('mentee');
        $pairing = MentorshipPairing::create([
            'mentee_id' => $this->makeUser('mentee')->id, 'program_id' => $program->id, 'status' => 'actif',
        ]);

        Sanctum::actingAs($mentee, ['*']);

        $this->getJson("/api/pairings/{$pairing->id}")->assertForbidden();
    }

    public function test_admin_can_confirm_a_match(): void
    {
        $program = $this->program();
        $mentor = $this->makeUser('mentor');
        MentorProfile::create(['user_id' => $mentor->id, 'expertise' => 'Robotique', 'validated_at' => now()]);
        $pairing = MentorshipPairing::create([
            'mentee_id' => $this->makeUser('mentee')->id, 'program_id' => $program->id, 'status' => 'en_attente',
        ]);

        Sanctum::actingAs($this->makeUser('admin'), ['*']);

        $response = $this->patchJson("/api/pairings/{$pairing->id}", [
            'mentor_id' => $mentor->id,
            'status' => 'actif',
        ]);

        $response->assertOk();
        $fresh = $pairing->fresh();
        $this->assertSame('actif', $fresh->status);
        $this->assertSame($mentor->id, $fresh->mentor_id);
        $this->assertNotNull($fresh->matched_at);
    }

    public function test_matching_suggestions_lists_unmatched_mentees_with_available_mentors(): void
    {
        $program = $this->program();
        $mentor = $this->makeUser('mentor');
        MentorProfile::create(['user_id' => $mentor->id, 'expertise' => 'Data science', 'capacity' => 3, 'validated_at' => now()]);
        $mentee = $this->makeUser('mentee');
        MentorshipPairing::create(['mentee_id' => $mentee->id, 'program_id' => $program->id, 'status' => 'en_attente']);

        Sanctum::actingAs($this->makeUser('admin'), ['*']);

        $response = $this->getJson('/api/matching/suggestions');

        $response->assertOk()->assertJsonCount(1);
        $this->assertSame($mentor->id, $response->json('0.suggested_mentor.id'));
    }

    public function test_mentor_cannot_access_matching_suggestions(): void
    {
        Sanctum::actingAs($this->makeUser('mentor'), ['*']);

        $this->getJson('/api/matching/suggestions')->assertForbidden();
    }
}
