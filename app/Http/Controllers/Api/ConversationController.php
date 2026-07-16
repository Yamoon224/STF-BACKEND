<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use Illuminate\Http\Request;

class ConversationController extends Controller
{
    public function index(Request $request)
    {
        return $request->user()
            ->belongsToMany(Conversation::class, 'conversation_participants')
            ->withPivot('last_read_at')
            ->with(['participants', 'messages' => fn ($q) => $q->latest()->limit(1)])
            ->orderByDesc('conversations.updated_at')
            ->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'subject' => ['nullable', 'string', 'max:255'],
            'participant_ids' => ['required', 'array', 'min:1'],
            'participant_ids.*' => ['exists:users,id'],
        ]);

        $conversation = Conversation::create(['subject' => $data['subject'] ?? null]);

        $participantIds = array_unique([...$data['participant_ids'], $request->user()->id]);
        $conversation->participants()->attach($participantIds);

        return response()->json($conversation->load('participants'), 201);
    }

    public function messages(Request $request, Conversation $conversation)
    {
        $this->authorizeParticipant($request, $conversation);

        return $conversation->messages()->with('sender')->orderBy('created_at')->get();
    }

    public function sendMessage(Request $request, Conversation $conversation)
    {
        $this->authorizeParticipant($request, $conversation);

        $data = $request->validate(['body' => ['required', 'string']]);

        $message = $conversation->messages()->create([
            'sender_id' => $request->user()->id,
            'body' => $data['body'],
        ]);

        $conversation->touch();

        return response()->json($message->load('sender'), 201);
    }

    public function markRead(Request $request, Conversation $conversation)
    {
        $this->authorizeParticipant($request, $conversation);

        $conversation->participants()->updateExistingPivot($request->user()->id, [
            'last_read_at' => now(),
        ]);

        return response()->noContent();
    }

    protected function authorizeParticipant(Request $request, Conversation $conversation): void
    {
        abort_unless(
            $conversation->participants()->whereKey($request->user()->id)->exists(),
            403
        );
    }
}
