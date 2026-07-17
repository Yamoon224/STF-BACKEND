<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cohort;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use OpenApi\Attributes as OA;

class CohortController extends Controller
{
    #[OA\Get(
        path: '/cohorts',
        summary: 'Lister les cohortes',
        security: [['bearerAuth' => []]],
        tags: ['Cohortes'],
        parameters: [new OA\QueryParameter(name: 'program_id', schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Cohortes', content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/Cohort')))]
    )]
    public function index(Request $request)
    {
        return Cohort::query()
            ->when($request->query('program_id'), fn ($q, $id) => $q->where('program_id', $id))
            ->with('program')
            ->orderByDesc('start_date')
            ->get();
    }

    #[OA\Get(
        path: '/cohorts/{cohort}',
        summary: 'Consulter une cohorte',
        security: [['bearerAuth' => []]],
        tags: ['Cohortes'],
        parameters: [new OA\PathParameter(name: 'cohort', schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Cohorte', content: new OA\JsonContent(ref: '#/components/schemas/Cohort')),
            new OA\Response(response: 404, description: 'Introuvable'),
        ]
    )]
    public function show(Cohort $cohort)
    {
        return $cohort->load(['program', 'users']);
    }

    #[OA\Post(
        path: '/cohorts',
        summary: 'Créer une cohorte',
        security: [['bearerAuth' => []]],
        tags: ['Cohortes'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['program_id', 'name'],
                properties: [
                    new OA\Property(property: 'program_id', type: 'integer'),
                    new OA\Property(property: 'name', type: 'string'),
                    new OA\Property(property: 'start_date', type: 'string', format: 'date', nullable: true),
                    new OA\Property(property: 'end_date', type: 'string', format: 'date', nullable: true),
                    new OA\Property(property: 'status', type: 'string', enum: ['a_venir', 'en_cours', 'termine']),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Créée', content: new OA\JsonContent(ref: '#/components/schemas/Cohort')),
            new OA\Response(response: 403, description: "Permission `cohorts.manage` requise"),
            new OA\Response(response: 422, description: 'Validation échouée', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')),
        ]
    )]
    public function store(Request $request)
    {
        $this->authorize('create', Cohort::class);

        $data = $request->validate([
            'program_id' => ['required', 'exists:programs,id'],
            'name' => ['required', 'string', 'max:255'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'status' => [Rule::in(['a_venir', 'en_cours', 'termine'])],
        ]);

        $data['status'] ??= 'a_venir';

        return response()->json(Cohort::create($data), 201);
    }

    #[OA\Patch(
        path: '/cohorts/{cohort}',
        summary: 'Modifier une cohorte',
        security: [['bearerAuth' => []]],
        tags: ['Cohortes'],
        parameters: [new OA\PathParameter(name: 'cohort', schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(content: new OA\JsonContent(properties: [
            new OA\Property(property: 'name', type: 'string'),
            new OA\Property(property: 'start_date', type: 'string', format: 'date', nullable: true),
            new OA\Property(property: 'end_date', type: 'string', format: 'date', nullable: true),
            new OA\Property(property: 'status', type: 'string', enum: ['a_venir', 'en_cours', 'termine']),
        ])),
        responses: [
            new OA\Response(response: 200, description: 'Modifiée', content: new OA\JsonContent(ref: '#/components/schemas/Cohort')),
            new OA\Response(response: 403, description: "Permission `cohorts.manage` requise"),
        ]
    )]
    public function update(Request $request, Cohort $cohort)
    {
        $this->authorize('update', $cohort);

        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'status' => [Rule::in(['a_venir', 'en_cours', 'termine'])],
        ]);

        $cohort->update($data);

        return $cohort;
    }

    #[OA\Delete(
        path: '/cohorts/{cohort}',
        summary: 'Supprimer une cohorte',
        security: [['bearerAuth' => []]],
        tags: ['Cohortes'],
        parameters: [new OA\PathParameter(name: 'cohort', schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 204, description: 'Supprimée'),
            new OA\Response(response: 403, description: "Permission `cohorts.manage` requise"),
        ]
    )]
    public function destroy(Cohort $cohort)
    {
        $this->authorize('delete', $cohort);

        $cohort->delete();

        return response()->noContent();
    }
}
