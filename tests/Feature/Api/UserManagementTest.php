<?php

namespace Tests\Feature\Api;

use App\Http\Controllers\Api\UserController;
use App\Models\MentorProfile;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
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

    public function test_invited_account_gets_the_default_password(): void
    {
        Sanctum::actingAs($this->makeUser('admin'), ['*']);

        $this->postJson('/api/users', [
            'name' => 'Nouvelle Collaboratrice',
            'email' => 'collab2@example.org',
            'role' => 'staff',
        ])->assertCreated();

        $user = User::where('email', 'collab2@example.org')->first();
        $this->assertTrue(Hash::check(UserController::DEFAULT_PASSWORD, $user->password));
    }

    public function test_admin_can_reset_a_users_password(): void
    {
        Sanctum::actingAs($this->makeUser('admin'), ['*']);
        $target = $this->makeUser('mentee', ['password' => 'un-ancien-mot-de-passe']);
        $token = $target->createToken('test')->plainTextToken;

        $response = $this->postJson("/api/users/{$target->id}/reset-password");

        $response->assertOk()->assertJsonPath('password', UserController::DEFAULT_PASSWORD);
        $this->assertTrue(Hash::check(UserController::DEFAULT_PASSWORD, $target->fresh()->password));
        $this->assertSame(0, $target->fresh()->tokens()->count());
    }

    public function test_staff_cannot_reset_a_users_password(): void
    {
        Sanctum::actingAs($this->makeUser('staff'), ['*']);
        $target = $this->makeUser('mentee');

        $this->postJson("/api/users/{$target->id}/reset-password")->assertForbidden();
    }

    public function test_admin_can_soft_delete_a_user(): void
    {
        Sanctum::actingAs($this->makeUser('admin'), ['*']);
        $target = $this->makeUser('mentee', ['email' => 'a-supprimer@example.org', 'password' => 'password123']);

        $this->deleteJson("/api/users/{$target->id}")->assertNoContent();

        $this->assertSoftDeleted('users', ['id' => $target->id]);
        $ids = collect($this->getJson('/api/users')->json('data'))->pluck('id');
        $this->assertFalse($ids->contains($target->id));

        // A soft-deleted account can no longer authenticate.
        $this->postJson('/api/auth/login', ['email' => 'a-supprimer@example.org', 'password' => 'password123'])
            ->assertStatus(422);
    }

    public function test_admin_cannot_delete_their_own_account(): void
    {
        $admin = $this->makeUser('admin');
        Sanctum::actingAs($admin, ['*']);

        $this->deleteJson("/api/users/{$admin->id}")->assertStatus(422);
        $this->assertDatabaseHas('users', ['id' => $admin->id, 'deleted_at' => null]);
    }

    public function test_staff_cannot_delete_a_user(): void
    {
        Sanctum::actingAs($this->makeUser('staff'), ['*']);
        $target = $this->makeUser('mentee');

        $this->deleteJson("/api/users/{$target->id}")->assertForbidden();
    }
}
