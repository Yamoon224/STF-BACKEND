<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use OpenApi\Attributes as OA;

class ProgramController extends Controller
{
    #[OA\Get(
        path: '/programs',
        summary: 'Lister les programmes',
        description: 'Public — utilisé par le site vitrine.',
        tags: ['Programmes'],
        parameters: [new OA\QueryParameter(name: 'status', schema: new OA\Schema(type: 'string', enum: ['a_venir', 'en_cours', 'archive']))],
        responses: [new OA\Response(response: 200, description: 'Programmes', content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/Program')))]
    )]
    public function index(Request $request)
    {
        return Program::query()
            ->withCount('cohorts')
            ->withCount(['pairings as mentees_count' => fn ($q) => $q->whereIn('status', ['actif', 'en_attente'])])
            ->when($request->query('status'), fn ($q, $status) => $q->where('status', $status))
            ->orderBy('name')
            ->get();
    }

    #[OA\Get(
        path: '/programs/{program}',
        summary: 'Consulter un programme (par slug)',
        description: 'Public — inclut les cohortes et modules du programme.',
        tags: ['Programmes'],
        parameters: [new OA\PathParameter(name: 'program', description: 'Slug du programme', schema: new OA\Schema(type: 'string', example: 'mentorat-stim'))],
        responses: [
            new OA\Response(response: 200, description: 'Programme', content: new OA\JsonContent(ref: '#/components/schemas/Program')),
            new OA\Response(response: 404, description: 'Introuvable'),
        ]
    )]
    public function show(Program $program)
    {
        return $program->load(['cohorts', 'modules']);
    }

    #[OA\Post(
        path: '/programs',
        summary: 'Créer un programme',
        security: [['bearerAuth' => []]],
        tags: ['Programmes'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name'],
                properties: [
                    new OA\Property(property: 'name', type: 'string'),
                    new OA\Property(property: 'audience', type: 'string', nullable: true),
                    new OA\Property(property: 'description', type: 'string', nullable: true),
                    new OA\Property(property: 'color', type: 'string', nullable: true),
                    new OA\Property(property: 'status', type: 'string', enum: ['a_venir', 'en_cours', 'archive']),
                    new OA\Property(property: 'cycle_start', type: 'string', format: 'date', nullable: true),
                    new OA\Property(property: 'cycle_end', type: 'string', format: 'date', nullable: true),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Créé', content: new OA\JsonContent(ref: '#/components/schemas/Program')),
            new OA\Response(response: 403, description: "Permission `programs.manage` requise"),
            new OA\Response(response: 422, description: 'Validation échouée', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')),
        ]
    )]
    public function store(Request $request)
    {
        $this->authorize('create', Program::class);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'audience' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'color' => ['nullable', 'string', 'max:50'],
            'status' => [Rule::in(['a_venir', 'en_cours', 'archive'])],
            'cycle_start' => ['nullable', 'date'],
            'cycle_end' => ['nullable', 'date', 'after_or_equal:cycle_start'],
        ]);

        $data['slug'] = Str::slug($data['name']);
        $data['status'] ??= 'a_venir';

        return response()->json(Program::create($data), 201);
    }

    #[OA\Patch(
        path: '/programs/{program}',
        summary: 'Modifier un programme',
        security: [['bearerAuth' => []]],
        tags: ['Programmes'],
        parameters: [new OA\PathParameter(name: 'program', schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(content: new OA\JsonContent(properties: [
            new OA\Property(property: 'name', type: 'string'),
            new OA\Property(property: 'audience', type: 'string', nullable: true),
            new OA\Property(property: 'description', type: 'string', nullable: true),
            new OA\Property(property: 'color', type: 'string', nullable: true),
            new OA\Property(property: 'status', type: 'string', enum: ['a_venir', 'en_cours', 'archive']),
            new OA\Property(property: 'cycle_start', type: 'string', format: 'date', nullable: true),
            new OA\Property(property: 'cycle_end', type: 'string', format: 'date', nullable: true),
        ])),
        responses: [
            new OA\Response(response: 200, description: 'Modifié', content: new OA\JsonContent(ref: '#/components/schemas/Program')),
            new OA\Response(response: 403, description: "Permission `programs.manage` requise"),
        ]
    )]
    public function update(Request $request, Program $program)
    {
        $this->authorize('update', $program);

        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'audience' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'color' => ['nullable', 'string', 'max:50'],
            'status' => [Rule::in(['a_venir', 'en_cours', 'archive'])],
            'cycle_start' => ['nullable', 'date'],
            'cycle_end' => ['nullable', 'date', 'after_or_equal:cycle_start'],
        ]);

        if (isset($data['name'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $program->update($data);

        return $program;
    }

    #[OA\Delete(
        path: '/programs/{program}',
        summary: 'Supprimer un programme',
        security: [['bearerAuth' => []]],
        tags: ['Programmes'],
        parameters: [new OA\PathParameter(name: 'program', schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 204, description: 'Supprimé'),
            new OA\Response(response: 403, description: "Permission `programs.manage` requise"),
        ]
    )]
    public function destroy(Program $program)
    {
        $this->authorize('delete', $program);

        $program->delete();

        return response()->noContent();
    }
}
