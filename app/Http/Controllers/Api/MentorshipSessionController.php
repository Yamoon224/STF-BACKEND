<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MentorshipPairing;
use App\Models\MentorshipSession;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use OpenApi\Attributes as OA;

class MentorshipSessionController extends Controller
{
    #[OA\Get(
        path: '/sessions',
        summary: 'Lister les sessions de mentorat',
        security: [['bearerAuth' => []]],
        tags: ['Sessions'],
        parameters: [
            new OA\QueryParameter(name: 'pairing_id', schema: new OA\Schema(type: 'integer')),
            new OA\QueryParameter(name: 'status', schema: new OA\Schema(type: 'string', enum: ['en_attente', 'confirmee', 'realisee', 'annulee'])),
        ],
        responses: [new OA\Response(response: 200, description: 'Page paginée (20/page)', content: new OA\JsonContent(properties: [
            new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/MentorshipSession')),
        ]))]
    )]
    public function index(Request $request)
    {
        $this->authorize('viewAny', MentorshipSession::class);

        $user = $request->user();

        return MentorshipSession::query()
            ->with(['pairing.mentee', 'pairing.mentor'])
            ->when(! $user->can('sessions.manage'), function ($q) use ($user) {
                $q->whereHas('pairing', fn ($q) => $q->where('mentee_id', $user->id)->orWhere('mentor_id', $user->id));
            })
            ->when($request->query('pairing_id'), fn ($q, $id) => $q->where('pairing_id', $id))
            ->when($request->query('status'), fn ($q, $status) => $q->where('status', $status))
            ->orderBy('scheduled_at')
            ->paginate(20);
    }

    #[OA\Get(
        path: '/sessions/{session}',
        summary: 'Consulter une session',
        security: [['bearerAuth' => []]],
        tags: ['Sessions'],
        parameters: [new OA\PathParameter(name: 'session', schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Session', content: new OA\JsonContent(ref: '#/components/schemas/MentorshipSession')),
            new OA\Response(response: 403, description: 'Non membre du binôme'),
        ]
    )]
    public function show(MentorshipSession $session)
    {
        $this->authorize('view', $session);

        return $session->load(['pairing.mentee', 'pairing.mentor', 'notes.author']);
    }

    #[OA\Post(
        path: '/sessions',
        summary: 'Planifier une session',
        security: [['bearerAuth' => []]],
        tags: ['Sessions'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['pairing_id', 'scheduled_at'],
                properties: [
                    new OA\Property(property: 'pairing_id', type: 'integer'),
                    new OA\Property(property: 'scheduled_at', type: 'string', format: 'date-time'),
                    new OA\Property(property: 'duration_minutes', type: 'integer', minimum: 15, maximum: 240, nullable: true),
                    new OA\Property(property: 'topic', type: 'string', nullable: true),
                    new OA\Property(property: 'location_or_link', type: 'string', nullable: true),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Créée', content: new OA\JsonContent(ref: '#/components/schemas/MentorshipSession')),
            new OA\Response(response: 403, description: 'Non membre du binôme'),
            new OA\Response(response: 422, description: 'Validation échouée', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')),
        ]
    )]
    public function store(Request $request)
    {
        $this->authorize('create', MentorshipSession::class);

        $data = $request->validate([
            'pairing_id' => ['required', 'exists:mentorship_pairings,id'],
            'scheduled_at' => ['required', 'date'],
            'duration_minutes' => ['nullable', 'integer', 'min:15', 'max:240'],
            'topic' => ['nullable', 'string', 'max:255'],
            'location_or_link' => ['nullable', 'string', 'max:255'],
        ]);

        $pairing = MentorshipPairing::findOrFail($data['pairing_id']);
        abort_unless(
            $request->user()->can('sessions.manage')
                || in_array($request->user()->id, [$pairing->mentee_id, $pairing->mentor_id], true),
            403
        );

        $data['created_by'] = $request->user()->id;
        $data['status'] = 'en_attente';

        return response()->json(MentorshipSession::create($data)->load('pairing'), 201);
    }

    #[OA\Patch(
        path: '/sessions/{session}',
        summary: 'Modifier une session (confirmer, replanifier, annuler…)',
        security: [['bearerAuth' => []]],
        tags: ['Sessions'],
        parameters: [new OA\PathParameter(name: 'session', schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(content: new OA\JsonContent(properties: [
            new OA\Property(property: 'scheduled_at', type: 'string', format: 'date-time'),
            new OA\Property(property: 'duration_minutes', type: 'integer', minimum: 15, maximum: 240, nullable: true),
            new OA\Property(property: 'status', type: 'string', enum: ['en_attente', 'confirmee', 'realisee', 'annulee']),
            new OA\Property(property: 'topic', type: 'string', nullable: true),
            new OA\Property(property: 'location_or_link', type: 'string', nullable: true),
        ])),
        responses: [
            new OA\Response(response: 200, description: 'Modifiée', content: new OA\JsonContent(ref: '#/components/schemas/MentorshipSession')),
            new OA\Response(response: 403, description: 'Non autorisée'),
        ]
    )]
    public function update(Request $request, MentorshipSession $session)
    {
        $this->authorize('update', $session);

        $data = $request->validate([
            'scheduled_at' => ['sometimes', 'date'],
            'duration_minutes' => ['nullable', 'integer', 'min:15', 'max:240'],
            'status' => [Rule::in(['en_attente', 'confirmee', 'realisee', 'annulee'])],
            'topic' => ['nullable', 'string', 'max:255'],
            'location_or_link' => ['nullable', 'string', 'max:255'],
        ]);

        $session->update($data);

        return $session->load('pairing');
    }

    #[OA\Delete(
        path: '/sessions/{session}',
        summary: 'Supprimer une session',
        security: [['bearerAuth' => []]],
        tags: ['Sessions'],
        parameters: [new OA\PathParameter(name: 'session', schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 204, description: 'Supprimée'),
            new OA\Response(response: 403, description: "Permission `sessions.manage` requise"),
        ]
    )]
    public function destroy(MentorshipSession $session)
    {
        $this->authorize('delete', $session);

        $session->delete();

        return response()->noContent();
    }
}
