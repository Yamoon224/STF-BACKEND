<?php

namespace Tests\Feature\Api;

use App\Models\Conversation;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ConversationTest extends TestCase
{
    public function test_user_can_create_a_conversation_with_another_user(): void
    {
        $mentee = $this->makeUser('mentee');
        $mentor = $this->makeUser('mentor');
        Sanctum::actingAs($mentee, ['*']);

        $response = $this->postJson('/api/conversations', ['participant_ids' => [$mentor->id]]);

        $response->assertCreated();
        $conversationId = $response->json('id');
        $this->assertDatabaseHas('conversation_participants', ['conversation_id' => $conversationId, 'user_id' => $mentee->id]);
        $this->assertDatabaseHas('conversation_participants', ['conversation_id' => $conversationId, 'user_id' => $mentor->id]);
    }

    public function test_participant_can_send_and_read_messages(): void
    {
        $mentee = $this->makeUser('mentee');
        $mentor = $this->makeUser('mentor');
        $conversation = Conversation::create();
        $conversation->participants()->attach([$mentee->id, $mentor->id]);

        Sanctum::actingAs($mentor, ['*']);
        $this->postJson("/api/conversations/{$conversation->id}/messages", ['body' => 'Bonjour !'])
            ->assertCreated();

        Sanctum::actingAs($mentee, ['*']);
        $response = $this->getJson("/api/conversations/{$conversation->id}/messages");

        $response->assertOk()->assertJsonCount(1)->assertJsonPath('0.body', 'Bonjour !');
    }

    public function test_non_participant_cannot_read_or_send_messages(): void
    {
        $conversation = Conversation::create();
        $conversation->participants()->attach([$this->makeUser('mentee')->id, $this->makeUser('mentor')->id]);

        Sanctum::actingAs($this->makeUser('mentee'), ['*']);

        $this->getJson("/api/conversations/{$conversation->id}/messages")->assertForbidden();
        $this->postJson("/api/conversations/{$conversation->id}/messages", ['body' => 'Intrusion'])->assertForbidden();
    }

    public function test_participant_can_mark_conversation_as_read(): void
    {
        $mentee = $this->makeUser('mentee');
        $conversation = Conversation::create();
        $conversation->participants()->attach($mentee->id);

        Sanctum::actingAs($mentee, ['*']);

        $this->postJson("/api/conversations/{$conversation->id}/read")->assertNoContent();
        $pivot = $conversation->participants()->where('user_id', $mentee->id)->first()->pivot;
        $this->assertNotNull($pivot->last_read_at);
    }
}
