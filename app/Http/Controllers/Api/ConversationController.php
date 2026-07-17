<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class ConversationController extends Controller
{
    #[OA\Get(
        path: '/conversations',
        summary: 'Lister mes conversations',
        security: [['bearerAuth' => []]],
        tags: ['Messagerie'],
        responses: [new OA\Response(response: 200, description: 'Conversations', content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/Conversation')))]
    )]
    public function index(Request $request)
    {
        return $request->user()
            ->belongsToMany(Conversation::class, 'conversation_participants')
            ->withPivot('last_read_at')
            ->with(['participants', 'messages' => fn ($q) => $q->latest()->limit(1)])
            ->orderByDesc('conversations.updated_at')
            ->get();
    }

    #[OA\Post(
        path: '/conversations',
        summary: 'Démarrer une conversation',
        description: "L'auteure est automatiquement ajoutée aux participantes.",
        security: [['bearerAuth' => []]],
        tags: ['Messagerie'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(required: ['participant_ids'], properties: [
                new OA\Property(property: 'subject', type: 'string', nullable: true),
                new OA\Property(property: 'participant_ids', type: 'array', minItems: 1, items: new OA\Items(type: 'integer')),
            ])
        ),
        responses: [new OA\Response(response: 201, description: 'Créée', content: new OA\JsonContent(ref: '#/components/schemas/Conversation'))]
    )]
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

    #[OA\Get(
        path: '/conversations/{conversation}/messages',
        summary: "Lister les messages d'une conversation",
        security: [['bearerAuth' => []]],
        tags: ['Messagerie'],
        parameters: [new OA\PathParameter(name: 'conversation', schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Messages', content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/Message'))),
            new OA\Response(response: 403, description: 'Non participante'),
        ]
    )]
    public function messages(Request $request, Conversation $conversation)
    {
        $this->authorizeParticipant($request, $conversation);

        return $conversation->messages()->with('sender')->orderBy('created_at')->get();
    }

    #[OA\Post(
        path: '/conversations/{conversation}/messages',
        summary: 'Envoyer un message',
        security: [['bearerAuth' => []]],
        tags: ['Messagerie'],
        parameters: [new OA\PathParameter(name: 'conversation', schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(required: ['body'], properties: [new OA\Property(property: 'body', type: 'string')])
        ),
        responses: [
            new OA\Response(response: 201, description: 'Envoyé', content: new OA\JsonContent(ref: '#/components/schemas/Message')),
            new OA\Response(response: 403, description: 'Non participante'),
        ]
    )]
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

    #[OA\Post(
        path: '/conversations/{conversation}/read',
        summary: 'Marquer la conversation comme lue',
        security: [['bearerAuth' => []]],
        tags: ['Messagerie'],
        parameters: [new OA\PathParameter(name: 'conversation', schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 204, description: 'Marquée comme lue'),
            new OA\Response(response: 403, description: 'Non participante'),
        ]
    )]
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
