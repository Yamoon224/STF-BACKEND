<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Experiment;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use OpenApi\Attributes as OA;

class ExperimentController extends Controller
{
    #[OA\Get(
        path: '/experiments',
        summary: 'Lister les expériences du labo virtuel',
        description: 'Public. Les expériences `brouillon` ne sont visibles qu’avec `programs.manage`.',
        tags: ['Labo virtuel'],
        parameters: [
            new OA\QueryParameter(name: 'subject_id', schema: new OA\Schema(type: 'integer')),
            new OA\QueryParameter(name: 'level_id', schema: new OA\Schema(type: 'integer')),
            new OA\QueryParameter(name: 'course_id', schema: new OA\Schema(type: 'integer')),
        ],
        responses: [new OA\Response(response: 200, description: 'Expériences', content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/Experiment')))]
    )]
    public function index(Request $request)
    {
        return Experiment::query()
            ->when($request->query('subject_id'), fn ($q, $id) => $q->where('subject_id', $id))
            ->when($request->query('level_id'), fn ($q, $id) => $q->where('level_id', $id))
            ->when($request->query('course_id'), fn ($q, $id) => $q->where('course_id', $id))
            ->when(! $request->user()?->can('programs.manage'), fn ($q) => $q->where('status', 'publie'))
            ->orderBy('order')
            ->get();
    }

    #[OA\Get(
        path: '/experiments/{experiment}',
        summary: 'Consulter une expérience du labo virtuel',
        description: 'Public.',
        tags: ['Labo virtuel'],
        parameters: [new OA\PathParameter(name: 'experiment', schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Expérience', content: new OA\JsonContent(ref: '#/components/schemas/Experiment'))]
    )]
    public function show(Experiment $experiment)
    {
        return $experiment->load(['subject', 'level', 'course']);
    }

    #[OA\Post(
        path: '/experiments',
        summary: 'Créer une expérience du labo virtuel',
        security: [['bearerAuth' => []]],
        tags: ['Labo virtuel'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['subject_id', 'title'],
                properties: [
                    new OA\Property(property: 'subject_id', type: 'integer'),
                    new OA\Property(property: 'level_id', type: 'integer', nullable: true),
                    new OA\Property(property: 'course_id', type: 'integer', nullable: true),
                    new OA\Property(property: 'title', type: 'string'),
                    new OA\Property(property: 'description', type: 'string', nullable: true),
                    new OA\Property(property: 'instructions', type: 'string', nullable: true),
                    new OA\Property(property: 'order', type: 'integer', minimum: 0, nullable: true),
                    new OA\Property(property: 'status', type: 'string', enum: ['brouillon', 'publie'], default: 'brouillon'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Créée', content: new OA\JsonContent(ref: '#/components/schemas/Experiment')),
            new OA\Response(response: 403, description: "Permission `programs.manage` requise"),
        ]
    )]
    public function store(Request $request)
    {
        $this->authorize('create', Experiment::class);

        $data = $request->validate([
            'subject_id' => ['required', 'exists:subjects,id'],
            'level_id' => ['nullable', 'exists:levels,id'],
            'course_id' => ['nullable', 'exists:courses,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'instructions' => ['nullable', 'string'],
            'order' => ['nullable', 'integer', 'min:0'],
            'status' => [Rule::in(['brouillon', 'publie'])],
        ]);

        $data['status'] ??= 'brouillon';

        return response()->json(Experiment::create($data), 201);
    }

    #[OA\Patch(
        path: '/experiments/{experiment}',
        summary: 'Modifier une expérience du labo virtuel',
        security: [['bearerAuth' => []]],
        tags: ['Labo virtuel'],
        parameters: [new OA\PathParameter(name: 'experiment', schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(content: new OA\JsonContent(properties: [
            new OA\Property(property: 'subject_id', type: 'integer'),
            new OA\Property(property: 'level_id', type: 'integer', nullable: true),
            new OA\Property(property: 'course_id', type: 'integer', nullable: true),
            new OA\Property(property: 'title', type: 'string'),
            new OA\Property(property: 'description', type: 'string', nullable: true),
            new OA\Property(property: 'instructions', type: 'string', nullable: true),
            new OA\Property(property: 'order', type: 'integer', minimum: 0, nullable: true),
            new OA\Property(property: 'status', type: 'string', enum: ['brouillon', 'publie']),
        ])),
        responses: [
            new OA\Response(response: 200, description: 'Modifiée', content: new OA\JsonContent(ref: '#/components/schemas/Experiment')),
            new OA\Response(response: 403, description: "Permission `programs.manage` requise"),
        ]
    )]
    public function update(Request $request, Experiment $experiment)
    {
        $this->authorize('update', $experiment);

        $data = $request->validate([
            'subject_id' => ['sometimes', 'exists:subjects,id'],
            'level_id' => ['nullable', 'exists:levels,id'],
            'course_id' => ['nullable', 'exists:courses,id'],
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'instructions' => ['nullable', 'string'],
            'order' => ['nullable', 'integer', 'min:0'],
            'status' => [Rule::in(['brouillon', 'publie'])],
        ]);

        $experiment->update($data);

        return $experiment;
    }

    #[OA\Delete(
        path: '/experiments/{experiment}',
        summary: 'Supprimer une expérience du labo virtuel',
        security: [['bearerAuth' => []]],
        tags: ['Labo virtuel'],
        parameters: [new OA\PathParameter(name: 'experiment', schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 204, description: 'Supprimée'),
            new OA\Response(response: 403, description: "Permission `programs.manage` requise"),
        ]
    )]
    public function destroy(Experiment $experiment)
    {
        $this->authorize('delete', $experiment);

        $experiment->delete();

        return response()->noContent();
    }
}
