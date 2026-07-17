<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\MentorshipPairing;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use OpenApi\Attributes as OA;

class MentorshipPairingController extends Controller
{
    #[OA\Get(
        path: '/pairings',
        summary: 'Lister les binômes',
        description: "Une mentée/mentore ne voit que son propre binôme, sauf avec la permission `pairings.manage`.",
        security: [['bearerAuth' => []]],
        tags: ['Binômes'],
        parameters: [
            new OA\QueryParameter(name: 'status', schema: new OA\Schema(type: 'string', enum: ['en_attente', 'actif', 'pause', 'termine'])),
            new OA\QueryParameter(name: 'program_id', schema: new OA\Schema(type: 'integer')),
        ],
        responses: [new OA\Response(response: 200, description: 'Page paginée (20/page)', content: new OA\JsonContent(properties: [
            new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/MentorshipPairing')),
        ]))]
    )]
    public function index(Request $request)
    {
        $this->authorize('viewAny', MentorshipPairing::class);

        $user = $request->user();

        return MentorshipPairing::query()
            ->with(['mentee', 'mentor', 'program', 'cohort'])
            ->withCount(['sessions as sessions_realisees_count' => fn ($q) => $q->where('status', 'realisee')])
            ->when(! $user->can('pairings.manage'), function ($q) use ($user) {
                $q->where('mentee_id', $user->id)->orWhere('mentor_id', $user->id);
            })
            ->when($request->query('status'), fn ($q, $status) => $q->where('status', $status))
            ->when($request->query('program_id'), fn ($q, $id) => $q->where('program_id', $id))
            ->orderByDesc('created_at')
            ->paginate(20);
    }

    #[OA\Get(
        path: '/pairings/{pairing}',
        summary: 'Consulter un binôme',
        security: [['bearerAuth' => []]],
        tags: ['Binômes'],
        parameters: [new OA\PathParameter(name: 'pairing', schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Binôme', content: new OA\JsonContent(ref: '#/components/schemas/MentorshipPairing')),
            new OA\Response(response: 403, description: "Réservé aux membres du binôme ou à `pairings.manage`"),
            new OA\Response(response: 404, description: 'Introuvable'),
        ]
    )]
    public function show(MentorshipPairing $pairing)
    {
        $this->authorize('view', $pairing);

        return $pairing->load(['mentee', 'mentor', 'program', 'cohort', 'sessions']);
    }

    #[OA\Post(
        path: '/pairings',
        summary: 'Créer un binôme',
        description: "Statut `actif` si un `mentor_id` est fourni, sinon `en_attente` (matching à faire).",
        security: [['bearerAuth' => []]],
        tags: ['Binômes'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['mentee_id', 'program_id'],
                properties: [
                    new OA\Property(property: 'mentee_id', type: 'integer'),
                    new OA\Property(property: 'mentor_id', type: 'integer', nullable: true),
                    new OA\Property(property: 'program_id', type: 'integer'),
                    new OA\Property(property: 'cohort_id', type: 'integer', nullable: true),
                    new OA\Property(property: 'match_score', type: 'integer', minimum: 0, maximum: 100, nullable: true),
                    new OA\Property(property: 'notes', type: 'string', nullable: true),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Créé', content: new OA\JsonContent(ref: '#/components/schemas/MentorshipPairing')),
            new OA\Response(response: 403, description: "Permission `matching.manage` ou `pairings.manage` requise"),
            new OA\Response(response: 422, description: 'Validation échouée', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')),
        ]
    )]
    public function store(Request $request)
    {
        $this->authorize('create', MentorshipPairing::class);

        $data = $request->validate([
            'mentee_id' => ['required', 'exists:users,id'],
            'mentor_id' => ['nullable', 'exists:users,id'],
            'program_id' => ['required', 'exists:programs,id'],
            'cohort_id' => ['nullable', 'exists:cohorts,id'],
            'match_score' => ['nullable', 'integer', 'min:0', 'max:100'],
            'notes' => ['nullable', 'string'],
        ]);

        $data['status'] = $data['mentor_id'] ?? null ? 'actif' : 'en_attente';
        if ($data['status'] === 'actif') {
            $data['matched_at'] = now();
        }

        $pairing = MentorshipPairing::create($data);

        AuditLog::record($request->user(), 'binome.cree', $pairing);

        return response()->json($pairing->load(['mentee', 'mentor', 'program']), 201);
    }

    #[OA\Patch(
        path: '/pairings/{pairing}',
        summary: 'Modifier un binôme (affecter une mentore, changer le statut…)',
        security: [['bearerAuth' => []]],
        tags: ['Binômes'],
        parameters: [new OA\PathParameter(name: 'pairing', schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(content: new OA\JsonContent(properties: [
            new OA\Property(property: 'mentor_id', type: 'integer', nullable: true),
            new OA\Property(property: 'status', type: 'string', enum: ['en_attente', 'actif', 'pause', 'termine']),
            new OA\Property(property: 'match_score', type: 'integer', minimum: 0, maximum: 100, nullable: true),
            new OA\Property(property: 'notes', type: 'string', nullable: true),
        ])),
        responses: [
            new OA\Response(response: 200, description: 'Modifié', content: new OA\JsonContent(ref: '#/components/schemas/MentorshipPairing')),
            new OA\Response(response: 403, description: "Permission `matching.manage` ou `pairings.manage` requise"),
        ]
    )]
    public function update(Request $request, MentorshipPairing $pairing)
    {
        $this->authorize('update', $pairing);

        $data = $request->validate([
            'mentor_id' => ['nullable', 'exists:users,id'],
            'status' => [Rule::in(['en_attente', 'actif', 'pause', 'termine'])],
            'match_score' => ['nullable', 'integer', 'min:0', 'max:100'],
            'notes' => ['nullable', 'string'],
        ]);

        if (($data['mentor_id'] ?? null) && ! $pairing->matched_at) {
            $data['matched_at'] = now();
        }

        if (($data['status'] ?? null) === 'termine') {
            $data['ended_at'] = now();
        }

        $pairing->update($data);

        AuditLog::record($request->user(), 'binome.modifie', $pairing);

        return $pairing->load(['mentee', 'mentor', 'program']);
    }

    #[OA\Delete(
        path: '/pairings/{pairing}',
        summary: 'Supprimer un binôme',
        security: [['bearerAuth' => []]],
        tags: ['Binômes'],
        parameters: [new OA\PathParameter(name: 'pairing', schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 204, description: 'Supprimé'),
            new OA\Response(response: 403, description: "Permission `pairings.manage` requise"),
        ]
    )]
    public function destroy(MentorshipPairing $pairing)
    {
        $this->authorize('delete', $pairing);

        $pairing->delete();

        return response()->noContent();
    }
}
