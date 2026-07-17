<?php

namespace Tests\Feature\Api;

use App\Models\Course;
use App\Models\CourseProgress;
use App\Models\Level;
use App\Models\Subject;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CourseReinforcementTest extends TestCase
{
    private function makeLevel(): Level
    {
        return Level::create(['name' => 'Terminale C & D', 'slug' => 'terminale-c-d', 'order' => 1]);
    }

    private function makeSubject(): Subject
    {
        return Subject::create(['name' => 'Mathématiques', 'slug' => 'mathematiques']);
    }

    public function test_levels_and_subjects_are_public(): void
    {
        $this->makeLevel();
        $this->makeSubject();

        $this->getJson('/api/levels')->assertOk()->assertJsonCount(1);
        $this->getJson('/api/subjects')->assertOk()->assertJsonCount(1);
    }

    public function test_courses_index_is_public_and_hides_draft(): void
    {
        $level = $this->makeLevel();
        $subject = $this->makeSubject();
        Course::create(['level_id' => $level->id, 'subject_id' => $subject->id, 'title' => 'Publié', 'status' => 'publie']);
        Course::create(['level_id' => $level->id, 'subject_id' => $subject->id, 'title' => 'Brouillon', 'status' => 'brouillon']);

        $this->getJson('/api/courses')->assertOk()->assertJsonCount(1);
    }

    public function test_admin_sees_draft_courses_too(): void
    {
        $level = $this->makeLevel();
        $subject = $this->makeSubject();
        Course::create(['level_id' => $level->id, 'subject_id' => $subject->id, 'title' => 'Publié', 'status' => 'publie']);
        Course::create(['level_id' => $level->id, 'subject_id' => $subject->id, 'title' => 'Brouillon', 'status' => 'brouillon']);

        Sanctum::actingAs($this->makeUser('admin'), ['*']);

        $this->getJson('/api/courses')->assertOk()->assertJsonCount(2);
    }

    public function test_my_progress_only_present_when_authenticated(): void
    {
        $level = $this->makeLevel();
        $subject = $this->makeSubject();
        $course = Course::create(['level_id' => $level->id, 'subject_id' => $subject->id, 'title' => 'Publié', 'status' => 'publie']);
        $mentee = $this->makeUser('mentee');
        CourseProgress::create(['user_id' => $mentee->id, 'course_id' => $course->id, 'progress' => 40]);

        $anonymous = $this->getJson('/api/courses');
        $anonymous->assertOk()->assertJsonMissingPath('0.my_progress');

        Sanctum::actingAs($mentee, ['*']);
        $authenticated = $this->getJson('/api/courses');
        $authenticated->assertOk()->assertJsonPath('0.my_progress', 40);
    }

    public function test_mentee_cannot_create_course(): void
    {
        $level = $this->makeLevel();
        $subject = $this->makeSubject();
        Sanctum::actingAs($this->makeUser('mentee'), ['*']);

        $this->postJson('/api/courses', [
            'level_id' => $level->id,
            'subject_id' => $subject->id,
            'title' => 'Nouveau',
        ])->assertForbidden();
    }

    public function test_staff_can_create_a_course_defaulting_to_draft(): void
    {
        $level = $this->makeLevel();
        $subject = $this->makeSubject();
        Sanctum::actingAs($this->makeUser('staff'), ['*']);

        $response = $this->postJson('/api/courses', [
            'level_id' => $level->id,
            'subject_id' => $subject->id,
            'title' => 'Nouveau cours',
        ]);

        $response->assertCreated()->assertJsonPath('status', 'brouillon');
    }

    public function test_mentee_can_update_own_progress(): void
    {
        $level = $this->makeLevel();
        $subject = $this->makeSubject();
        $course = Course::create(['level_id' => $level->id, 'subject_id' => $subject->id, 'title' => 'Publié', 'status' => 'publie']);
        $mentee = $this->makeUser('mentee');
        Sanctum::actingAs($mentee, ['*']);

        $firstUpdate = $this->postJson("/api/courses/{$course->id}/progress", ['progress' => 40]);
        $firstUpdate->assertCreated();

        $secondUpdate = $this->postJson("/api/courses/{$course->id}/progress", ['progress' => 100]);
        $secondUpdate->assertOk();

        $progress = CourseProgress::where('user_id', $mentee->id)->where('course_id', $course->id)->first();
        $this->assertSame(100, $progress->progress);
        $this->assertNotNull($progress->completed_at);
    }

    public function test_experiments_and_live_sessions_are_public_and_filterable(): void
    {
        $level = $this->makeLevel();
        $subject = $this->makeSubject();
        $course = Course::create(['level_id' => $level->id, 'subject_id' => $subject->id, 'title' => 'Publié', 'status' => 'publie']);
        $course->experiments()->create(['subject_id' => $subject->id, 'level_id' => $level->id, 'title' => 'Expérience', 'status' => 'publie']);
        $course->liveSessions()->create(['title' => 'Session live', 'scheduled_at' => now()->addWeek(), 'status' => 'a_venir']);

        $this->getJson("/api/experiments?course_id={$course->id}")->assertOk()->assertJsonCount(1);
        $this->getJson("/api/live-sessions?course_id={$course->id}")->assertOk()->assertJsonCount(1);
    }
}
