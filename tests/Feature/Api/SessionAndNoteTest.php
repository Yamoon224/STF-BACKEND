<?php

namespace Tests\Feature\Api;

use App\Models\MentorshipPairing;
use App\Models\MentorshipSession;
use App\Models\Program;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SessionAndNoteTest extends TestCase
{
    protected function pairing(): MentorshipPairing
    {
        $program = Program::create(['name' => 'Mentorat STIM', 'slug' => 'mentorat-stim-'.uniqid(), 'status' => 'en_cours']);

        return MentorshipPairing::create([
            'mentee_id' => $this->makeUser('mentee')->id,
            'mentor_id' => $this->makeUser('mentor')->id,
            'program_id' => $program->id,
            'status' => 'actif',
        ]);
    }

    public function test_mentor_can_schedule_a_session_for_their_pairing(): void
    {
        $pairing = $this->pairing();
        Sanctum::actingAs($pairing->mentor, ['*']);

        $response = $this->postJson('/api/sessions', [
            'pairing_id' => $pairing->id,
            'scheduled_at' => now()->addDay()->toDateTimeString(),
            'topic' => 'Introduction aux algorithmes',
        ]);

        $response->assertCreated()->assertJsonPath('status', 'en_attente');
        $this->assertDatabaseHas('mentorship_sessions', ['pairing_id' => $pairing->id]);
    }

    public function test_unrelated_mentor_cannot_schedule_a_session_for_someone_elses_pairing(): void
    {
        $pairing = $this->pairing();
        $outsider = $this->makeUser('mentor');
        Sanctum::actingAs($outsider, ['*']);

        $this->postJson('/api/sessions', [
            'pairing_id' => $pairing->id,
            'scheduled_at' => now()->addDay()->toDateTimeString(),
        ])->assertForbidden();
    }

    public function test_mentee_sees_only_sessions_from_her_own_pairings(): void
    {
        $pairing = $this->pairing();
        $session = MentorshipSession::create([
            'pairing_id' => $pairing->id, 'scheduled_at' => now()->addDay(), 'status' => 'en_attente',
        ]);
        $otherPairing = $this->pairing();
        MentorshipSession::create([
            'pairing_id' => $otherPairing->id, 'scheduled_at' => now()->addDay(), 'status' => 'en_attente',
        ]);

        Sanctum::actingAs($pairing->mentee, ['*']);

        $response = $this->getJson('/api/sessions');

        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id');
        $this->assertEquals([$session->id], $ids->all());
    }

    public function test_author_can_add_a_shared_note_visible_to_the_other_party(): void
    {
        $pairing = $this->pairing();
        $session = MentorshipSession::create([
            'pairing_id' => $pairing->id, 'scheduled_at' => now(), 'status' => 'realisee',
        ]);

        Sanctum::actingAs($pairing->mentor, ['*']);
        $this->postJson("/api/sessions/{$session->id}/notes", [
            'content' => 'Bonne progression sur les bases.',
            'visibility' => 'partagee',
        ])->assertCreated();

        Sanctum::actingAs($pairing->mentee, ['*']);
        $response = $this->getJson("/api/sessions/{$session->id}/notes");

        $response->assertOk()->assertJsonCount(1);
    }

    public function test_creating_a_note_without_visibility_defaults_to_partagee(): void
    {
        $pairing = $this->pairing();
        $session = MentorshipSession::create([
            'pairing_id' => $pairing->id, 'scheduled_at' => now(), 'status' => 'realisee',
        ]);

        Sanctum::actingAs($pairing->mentor, ['*']);
        $response = $this->postJson("/api/sessions/{$session->id}/notes", [
            'content' => 'Note sans visibilité explicite.',
        ]);

        $response->assertCreated()->assertJsonPath('visibility', 'partagee');
    }

    public function test_private_note_is_hidden_from_the_other_party(): void
    {
        $pairing = $this->pairing();
        $session = MentorshipSession::create([
            'pairing_id' => $pairing->id, 'scheduled_at' => now(), 'status' => 'realisee',
        ]);

        Sanctum::actingAs($pairing->mentor, ['*']);
        $this->postJson("/api/sessions/{$session->id}/notes", [
            'content' => 'Note confidentielle.',
            'visibility' => 'privee',
        ])->assertCreated();

        Sanctum::actingAs($pairing->mentee, ['*']);
        $response = $this->getJson("/api/sessions/{$session->id}/notes");

        $response->assertOk()->assertJsonCount(0);
    }
}
