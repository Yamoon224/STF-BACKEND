<?php

namespace Tests\Feature\Api;

use App\Models\Module;
use App\Models\ModuleProgress;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ModuleAndQuizTest extends TestCase
{
    public function test_index_hides_draft_modules_from_learners(): void
    {
        Module::create(['title' => 'Publié', 'status' => 'publie']);
        Module::create(['title' => 'Brouillon', 'status' => 'brouillon']);

        Sanctum::actingAs($this->makeUser('mentee'), ['*']);

        $response = $this->getJson('/api/modules');

        $response->assertOk()->assertJsonCount(1);
    }

    public function test_admin_sees_draft_modules_too(): void
    {
        Module::create(['title' => 'Publié', 'status' => 'publie']);
        Module::create(['title' => 'Brouillon', 'status' => 'brouillon']);

        Sanctum::actingAs($this->makeUser('admin'), ['*']);

        $this->getJson('/api/modules')->assertOk()->assertJsonCount(2);
    }

    public function test_index_includes_my_progress_for_authenticated_user(): void
    {
        $module = Module::create(['title' => 'Fondations STIM', 'status' => 'publie']);
        $mentee = $this->makeUser('mentee');
        ModuleProgress::create(['user_id' => $mentee->id, 'module_id' => $module->id, 'progress' => 70]);

        Sanctum::actingAs($mentee, ['*']);

        $response = $this->getJson('/api/modules');

        $response->assertOk()->assertJsonPath('0.my_progress', 70);
    }

    public function test_mentee_cannot_create_module(): void
    {
        Sanctum::actingAs($this->makeUser('mentee'), ['*']);

        $this->postJson('/api/modules', ['title' => 'Nouveau'])->assertForbidden();
    }

    public function test_staff_can_create_a_module_defaulting_to_draft(): void
    {
        Sanctum::actingAs($this->makeUser('staff'), ['*']);

        $response = $this->postJson('/api/modules', ['title' => 'Nouveau module']);

        $response->assertCreated()->assertJsonPath('status', 'brouillon');
    }

    public function test_mentee_can_update_own_progress(): void
    {
        $module = Module::create(['title' => 'Fondations STIM', 'status' => 'publie']);
        $mentee = $this->makeUser('mentee');
        Sanctum::actingAs($mentee, ['*']);

        $firstUpdate = $this->postJson("/api/modules/{$module->id}/progress", ['progress' => 40]);
        $firstUpdate->assertCreated();

        $secondUpdate = $this->postJson("/api/modules/{$module->id}/progress", ['progress' => 100]);
        $secondUpdate->assertOk();

        $progress = ModuleProgress::where('user_id', $mentee->id)->where('module_id', $module->id)->first();
        $this->assertSame(100, $progress->progress);
        $this->assertNotNull($progress->completed_at);
    }

    public function test_staff_can_create_a_quiz_with_questions_and_options(): void
    {
        $module = Module::create(['title' => 'Fondations STIM', 'status' => 'publie']);
        Sanctum::actingAs($this->makeUser('staff'), ['*']);

        $response = $this->postJson("/api/modules/{$module->id}/quizzes", [
            'title' => 'Quiz Fondations',
            'passing_score' => 70,
            'questions' => [
                [
                    'question' => 'STF signifie Sciences et Technologies au Féminin ?',
                    'type' => 'unique',
                    'options' => [
                        ['label' => 'Vrai', 'is_correct' => true],
                        ['label' => 'Faux', 'is_correct' => false],
                    ],
                ],
            ],
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('quiz_questions', ['question' => 'STF signifie Sciences et Technologies au Féminin ?']);
        $this->assertDatabaseCount('quiz_options', 2);
    }

    public function test_quiz_attempt_is_scored_correctly(): void
    {
        $module = Module::create(['title' => 'Fondations STIM', 'status' => 'publie']);
        $quiz = $module->quizzes()->create(['title' => 'Quiz', 'passing_score' => 50]);
        $question = $quiz->questions()->create(['question' => 'Q1', 'type' => 'unique', 'order' => 1]);
        $correct = $question->options()->create(['label' => 'Bonne réponse', 'is_correct' => true]);
        $question->options()->create(['label' => 'Mauvaise réponse', 'is_correct' => false]);

        $mentee = $this->makeUser('mentee');
        Sanctum::actingAs($mentee, ['*']);

        $response = $this->postJson("/api/quizzes/{$quiz->id}/attempts", [
            'answers' => [$question->id => [$correct->id]],
        ]);

        $response->assertCreated()
            ->assertJsonPath('score', 100)
            ->assertJsonPath('passed', true);
    }
}
