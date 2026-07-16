<?php

namespace Tests\Feature\Api;

use App\Models\MentorProfile;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    public function test_mentee_cannot_list_users(): void
    {
        Sanctum::actingAs($this->makeUser('mentee'), ['*']);

        $this->getJson('/api/users')->assertForbidden();
    }

    public function test_admin_can_list_and_filter_users(): void
    {
        Sanctum::actingAs($this->makeUser('admin'), ['*']);

        $this->makeUser('mentee', ['name' => 'Aïcha Diallo']);
        $this->makeUser('mentor', ['name' => 'Fatou Konaté']);

        $response = $this->getJson('/api/users?role=mentee');

        $response->assertOk();
        $names = collect($response->json('data'))->pluck('name');
        $this->assertTrue($names->contains('Aïcha Diallo'));
        $this->assertFalse($names->contains('Fatou Konaté'));
    }

    public function test_staff_can_view_but_not_manage_settings(): void
    {
        $staff = $this->makeUser('staff');
        Sanctum::actingAs($staff, ['*']);

        $this->getJson('/api/users')->assertOk();

        $target = $this->makeUser('mentee');
        $this->postJson("/api/users/{$target->id}/role", ['role' => 'staff'])->assertForbidden();
    }

    public function test_admin_can_invite_a_collaboratrice_account(): void
    {
        Sanctum::actingAs($this->makeUser('admin'), ['*']);

        $response = $this->postJson('/api/users', [
            'name' => 'Nouvelle Collaboratrice',
            'email' => 'collab@example.org',
            'role' => 'staff',
        ]);

        $response->assertCreated()->assertJsonPath('roles.0.name', 'staff');
        $this->assertDatabaseHas('users', ['email' => 'collab@example.org', 'status' => 'active']);
    }

    public function test_admin_can_validate_a_pending_mentor(): void
    {
        $admin = $this->makeUser('admin');
        $mentor = $this->makeUser('mentor', ['status' => 'pending']);
        MentorProfile::create(['user_id' => $mentor->id, 'expertise' => 'Data science']);

        Sanctum::actingAs($admin, ['*']);

        $response = $this->postJson("/api/users/{$mentor->id}/validate-mentor");

        $response->assertOk();
        $this->assertSame('active', $mentor->fresh()->status);
        $this->assertNotNull($mentor->fresh()->mentorProfile->validated_at);
        $this->assertSame($admin->id, $mentor->fresh()->mentorProfile->validated_by);
    }

    public function test_validating_mentor_without_profile_fails(): void
    {
        Sanctum::actingAs($this->makeUser('admin'), ['*']);
        $mentor = $this->makeUser('mentor');

        $this->postJson("/api/users/{$mentor->id}/validate-mentor")->assertStatus(422);
    }

    public function test_admin_can_suspend_and_activate_a_user(): void
    {
        Sanctum::actingAs($this->makeUser('admin'), ['*']);
        $target = $this->makeUser('mentee');

        $this->postJson("/api/users/{$target->id}/suspend")->assertOk();
        $this->assertSame('suspended', $target->fresh()->status);

        $this->postJson("/api/users/{$target->id}/activate")->assertOk();
        $this->assertSame('active', $target->fresh()->status);
    }

    public function test_only_admin_can_assign_roles(): void
    {
        Sanctum::actingAs($this->makeUser('admin'), ['*']);
        $target = $this->makeUser('mentee');

        $response = $this->postJson("/api/users/{$target->id}/role", ['role' => 'donor']);

        $response->assertOk();
        $this->assertTrue($target->fresh()->hasRole('donor'));
        $this->assertFalse($target->fresh()->hasRole('mentee'));
    }

    public function test_roles_endpoint_lists_seeded_roles_with_permissions(): void
    {
        Sanctum::actingAs($this->makeUser('admin'), ['*']);

        $response = $this->getJson('/api/roles');

        $response->assertOk();
        $names = collect($response->json())->pluck('name');
        foreach (['admin', 'staff', 'mentor', 'mentee', 'donor'] as $role) {
            $this->assertTrue($names->contains($role));
        }
    }
}
