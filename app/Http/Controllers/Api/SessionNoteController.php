<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MentorshipSession;
use App\Models\SessionNote;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use OpenApi\Attributes as OA;

class SessionNoteController extends Controller
{
    #[OA\Get(
        path: '/sessions/{session}/notes',
        summary: "Lister les notes d'une session",
        description: 'Les notes privées ne sont visibles que par leur autrice (ou avec `sessions.manage`).',
        security: [['bearerAuth' => []]],
        tags: ['Sessions'],
        parameters: [new OA\PathParameter(name: 'session', schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Notes', content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/SessionNote'))),
            new OA\Response(response: 403, description: 'Non membre du binôme'),
        ]
    )]
    public function index(Request $request, MentorshipSession $session)
    {
        $this->authorize('view', $session);

        return $session->notes()
            ->with('author')
            ->get()
            ->filter(fn (SessionNote $note) => $request->user()->can('view', $note))
            ->values();
    }

    #[OA\Post(
        path: '/sessions/{session}/notes',
        summary: 'Ajouter une note à une session',
        security: [['bearerAuth' => []]],
        tags: ['Sessions'],
        parameters: [new OA\PathParameter(name: 'session', schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['content'],
                properties: [
                    new OA\Property(property: 'content', type: 'string'),
                    new OA\Property(property: 'visibility', type: 'string', enum: ['partagee', 'privee'], default: 'partagee'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Créée', content: new OA\JsonContent(ref: '#/components/schemas/SessionNote')),
            new OA\Response(response: 403, description: 'Non membre du binôme'),
        ]
    )]
    public function store(Request $request, MentorshipSession $session)
    {
        $this->authorize('view', $session);
        $this->authorize('create', SessionNote::class);

        $data = $request->validate([
            'content' => ['required', 'string'],
            'visibility' => [Rule::in(['partagee', 'privee'])],
        ]);

        $data['visibility'] ??= 'partagee';
        $data['session_id'] = $session->id;
        $data['author_id'] = $request->user()->id;

        return response()->json(SessionNote::create($data)->load('author'), 201);
    }

    #[OA\Patch(
        path: '/notes/{note}',
        summary: 'Modifier une note',
        description: "Réservé à l'autrice de la note (ou `sessions.manage`).",
        security: [['bearerAuth' => []]],
        tags: ['Sessions'],
        parameters: [new OA\PathParameter(name: 'note', schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(content: new OA\JsonContent(properties: [
            new OA\Property(property: 'content', type: 'string'),
            new OA\Property(property: 'visibility', type: 'string', enum: ['partagee', 'privee']),
        ])),
        responses: [
            new OA\Response(response: 200, description: 'Modifiée', content: new OA\JsonContent(ref: '#/components/schemas/SessionNote')),
            new OA\Response(response: 403, description: "Réservé à l'autrice"),
        ]
    )]
    public function update(Request $request, SessionNote $note)
    {
        $this->authorize('update', $note);

        $data = $request->validate([
            'content' => ['sometimes', 'string'],
            'visibility' => [Rule::in(['partagee', 'privee'])],
        ]);

        $note->update($data);

        return $note;
    }

    #[OA\Delete(
        path: '/notes/{note}',
        summary: 'Supprimer une note',
        security: [['bearerAuth' => []]],
        tags: ['Sessions'],
        parameters: [new OA\PathParameter(name: 'note', schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 204, description: 'Supprimée'),
            new OA\Response(response: 403, description: "Réservé à l'autrice"),
        ]
    )]
    public function destroy(SessionNote $note)
    {
        $this->authorize('delete', $note);

        $note->delete();

        return response()->noContent();
    }
}
