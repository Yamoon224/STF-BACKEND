<?php

namespace Tests\Feature\Api;

use App\Models\Cohort;
use App\Models\Program;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProgramAndCohortTest extends TestCase
{
    public function test_programs_index_is_public(): void
    {
        Program::create(['name' => 'Mentorat STIM', 'slug' => 'mentorat-stim', 'status' => 'en_cours']);

        $this->getJson('/api/programs')->assertOk()->assertJsonCount(1);
    }

    public function test_program_show_by_slug_includes_cohorts_and_modules(): void
    {
        $program = Program::create(['name' => 'Mentorat STIM', 'slug' => 'mentorat-stim', 'status' => 'en_cours']);
        Cohort::create(['program_id' => $program->id, 'name' => 'Cohorte A', 'status' => 'en_cours']);

        $response = $this->getJson('/api/programs/mentorat-stim');

        $response->assertOk()->assertJsonCount(1, 'cohorts');
    }

    public function test_mentee_cannot_create_program(): void
    {
        Sanctum::actingAs($this->makeUser('mentee'), ['*']);

        $this->postJson('/api/programs', ['name' => 'Nouveau', 'status' => 'a_venir'])->assertForbidden();
    }

    public function test_admin_can_create_update_and_delete_a_program(): void
    {
        Sanctum::actingAs($this->makeUser('admin'), ['*']);

        $create = $this->postJson('/api/programs', ['name' => 'Campus numérique', 'status' => 'a_venir']);
        $create->assertCreated()->assertJsonPath('slug', 'campus-numerique');

        $id = $create->json('id');

        $this->patchJson("/api/programs/{$id}", ['status' => 'en_cours'])
            ->assertOk()->assertJsonPath('status', 'en_cours');

        $this->deleteJson("/api/programs/{$id}")->assertNoContent();
        $this->assertDatabaseMissing('programs', ['id' => $id]);
    }

    public function test_creating_a_program_without_status_defaults_to_a_venir(): void
    {
        Sanctum::actingAs($this->makeUser('admin'), ['*']);

        $response = $this->postJson('/api/programs', ['name' => 'Sans statut']);

        $response->assertCreated()->assertJsonPath('status', 'a_venir');
    }

    public function test_creating_a_cohort_without_status_defaults_to_a_venir(): void
    {
        Sanctum::actingAs($this->makeUser('admin'), ['*']);
        $program = Program::create(['name' => 'Mentorat STIM', 'slug' => 'mentorat-stim', 'status' => 'en_cours']);

        $response = $this->postJson('/api/cohorts', ['program_id' => $program->id, 'name' => 'Cohorte']);

        $response->assertCreated()->assertJsonPath('status', 'a_venir');
    }

    public function test_staff_can_create_a_cohort_for_a_program(): void
    {
        Sanctum::actingAs($this->makeUser('staff'), ['*']);
        $program = Program::create(['name' => 'Mentorat STIM', 'slug' => 'mentorat-stim', 'status' => 'en_cours']);

        $response = $this->postJson('/api/cohorts', [
            'program_id' => $program->id,
            'name' => 'Cohorte 2026',
            'status' => 'en_cours',
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('cohorts', ['name' => 'Cohorte 2026', 'program_id' => $program->id]);
    }

    public function test_mentor_cannot_create_a_cohort(): void
    {
        Sanctum::actingAs($this->makeUser('mentor'), ['*']);
        $program = Program::create(['name' => 'Mentorat STIM', 'slug' => 'mentorat-stim', 'status' => 'en_cours']);

        $this->postJson('/api/cohorts', ['program_id' => $program->id, 'name' => 'X'])->assertForbidden();
    }
}
