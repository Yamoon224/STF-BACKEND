<?php

namespace Tests\Feature\Api;

use App\Models\Group;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class GroupTest extends TestCase
{
    public function test_member_can_list_only_groups_she_belongs_to(): void
    {
        $member = $this->makeUser('mentee');
        $mine = Group::create(['name' => 'Mon groupe', 'type' => 'travail', 'status' => 'actif']);
        $mine->members()->attach($member->id, ['role_in_group' => 'membre', 'joined_at' => now()]);
        Group::create(['name' => 'Autre groupe', 'type' => 'travail', 'status' => 'actif']);

        Sanctum::actingAs($member, ['*']);

        $response = $this->getJson('/api/groups');

        $response->assertOk()->assertJsonCount(1)->assertJsonPath('0.name', 'Mon groupe');
    }

    public function test_staff_sees_all_groups(): void
    {
        Group::create(['name' => 'Groupe A', 'type' => 'travail', 'status' => 'actif']);
        Group::create(['name' => 'Groupe B', 'type' => 'mentorat', 'status' => 'en_validation']);

        Sanctum::actingAs($this->makeUser('staff'), ['*']);

        $this->getJson('/api/groups')->assertOk()->assertJsonCount(2);
    }

    public function test_creator_becomes_group_animatrice_and_can_post(): void
    {
        $staff = $this->makeUser('staff');
        Sanctum::actingAs($staff, ['*']);

        $create = $this->postJson('/api/groups', ['name' => 'Atelier Robotique', 'type' => 'travail']);
        $create->assertCreated();
        $groupId = $create->json('id');

        $this->assertDatabaseHas('group_members', [
            'group_id' => $groupId, 'user_id' => $staff->id, 'role_in_group' => 'animatrice',
        ]);

        $this->postJson("/api/groups/{$groupId}/posts", ['content' => 'Bienvenue dans le groupe !'])
            ->assertCreated();
    }

    public function test_creating_a_group_without_type_or_status_uses_defaults(): void
    {
        Sanctum::actingAs($this->makeUser('staff'), ['*']);

        $response = $this->postJson('/api/groups', ['name' => 'Groupe sans détails']);

        $response->assertCreated()
            ->assertJsonPath('type', 'travail')
            ->assertJsonPath('status', 'en_validation');
    }

    public function test_non_member_cannot_post_in_a_group(): void
    {
        $group = Group::create(['name' => 'Groupe fermé', 'type' => 'mentorat', 'status' => 'actif']);
        Sanctum::actingAs($this->makeUser('mentee'), ['*']);

        $this->postJson("/api/groups/{$group->id}/posts", ['content' => 'Salut'])->assertForbidden();
    }

    public function test_member_can_comment_on_a_post(): void
    {
        $group = Group::create(['name' => 'Groupe', 'type' => 'travail', 'status' => 'actif']);
        $member = $this->makeUser('mentee');
        $group->members()->attach($member->id, ['role_in_group' => 'membre', 'joined_at' => now()]);
        $post = $group->posts()->create(['author_id' => $member->id, 'content' => 'Post initial']);

        Sanctum::actingAs($member, ['*']);

        $this->postJson("/api/posts/{$post->id}/comments", ['content' => 'Super !'])->assertCreated();
    }

    public function test_animatrice_can_add_and_remove_members(): void
    {
        $group = Group::create(['name' => 'Groupe', 'type' => 'travail', 'status' => 'actif']);
        $animatrice = $this->makeUser('mentor');
        $group->members()->attach($animatrice->id, ['role_in_group' => 'animatrice', 'joined_at' => now()]);
        $newMember = $this->makeUser('mentee');

        Sanctum::actingAs($animatrice, ['*']);

        $this->postJson("/api/groups/{$group->id}/members", ['user_id' => $newMember->id])->assertOk();
        $this->assertTrue($group->members()->where('user_id', $newMember->id)->exists());

        $this->deleteJson("/api/groups/{$group->id}/members/{$newMember->id}")->assertNoContent();
        $this->assertFalse($group->fresh()->members()->where('user_id', $newMember->id)->exists());
    }
}
