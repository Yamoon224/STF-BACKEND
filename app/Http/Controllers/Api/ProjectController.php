<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use OpenApi\Attributes as OA;

class ProjectController extends Controller
{
    #[OA\Get(
        path: '/projects',
        summary: 'Lister les projets',
        description: 'Une mentée voit ses projets ; une mentore voit ceux de ses mentées ; `pairings.manage` voit tout.',
        security: [['bearerAuth' => []]],
        tags: ['Projets'],
        responses: [new OA\Response(response: 200, description: 'Projets', content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/Project')))]
    )]
    public function index(Request $request)
    {
        $user = $request->user();

        return Project::query()
            ->with(['mentee', 'pairing'])
            ->when(! $user->can('pairings.manage'), function ($q) use ($user) {
                $q->where('mentee_id', $user->id)
                    ->orWhereHas('pairing', fn ($q) => $q->where('mentor_id', $user->id));
            })
            ->orderByDesc('updated_at')
            ->get();
    }

    #[OA\Get(
        path: '/projects/{project}',
        summary: 'Consulter un projet',
        security: [['bearerAuth' => []]],
        tags: ['Projets'],
        parameters: [new OA\PathParameter(name: 'project', schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Projet', content: new OA\JsonContent(ref: '#/components/schemas/Project')),
            new OA\Response(response: 403, description: 'Non autorisée'),
        ]
    )]
    public function show(Project $project)
    {
        $this->authorize('view', $project);

        return $project->load(['mentee', 'pairing']);
    }

    #[OA\Post(
        path: '/projects',
        summary: 'Déposer un projet',
        description: 'Réservé aux mentées ; créé au statut `brouillon`.',
        security: [['bearerAuth' => []]],
        tags: ['Projets'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(required: ['title'], properties: [
                new OA\Property(property: 'pairing_id', type: 'integer', nullable: true),
                new OA\Property(property: 'title', type: 'string'),
                new OA\Property(property: 'description', type: 'string', nullable: true),
                new OA\Property(property: 'file_path', type: 'string', nullable: true),
            ])
        ),
        responses: [
            new OA\Response(response: 201, description: 'Créé', content: new OA\JsonContent(ref: '#/components/schemas/Project')),
            new OA\Response(response: 403, description: 'Réservé aux mentées'),
        ]
    )]
    public function store(Request $request)
    {
        $this->authorize('create', Project::class);

        $data = $request->validate([
            'pairing_id' => ['nullable', 'exists:mentorship_pairings,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'file_path' => ['nullable', 'string'],
        ]);

        $data['mentee_id'] = $request->user()->id;
        $data['status'] = 'brouillon';

        return response()->json(Project::create($data), 201);
    }

    #[OA\Patch(
        path: '/projects/{project}',
        summary: 'Modifier / soumettre un projet',
        description: "Une mentée ne peut que modifier le contenu et soumettre (`submit: true`) son propre brouillon ; changer directement `status` requiert `pairings.manage`.",
        security: [['bearerAuth' => []]],
        tags: ['Projets'],
        parameters: [new OA\PathParameter(name: 'project', schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(content: new OA\JsonContent(properties: [
            new OA\Property(property: 'title', type: 'string'),
            new OA\Property(property: 'description', type: 'string', nullable: true),
            new OA\Property(property: 'file_path', type: 'string', nullable: true),
            new OA\Property(property: 'status', type: 'string', enum: ['brouillon', 'soumis', 'en_validation', 'valide', 'rejete'], description: 'pairings.manage uniquement'),
            new OA\Property(property: 'submit', type: 'boolean', description: 'Mentée : passe son brouillon à `soumis`'),
        ])),
        responses: [
            new OA\Response(response: 200, description: 'Modifié', content: new OA\JsonContent(ref: '#/components/schemas/Project')),
            new OA\Response(response: 403, description: 'Non autorisée'),
        ]
    )]
    public function update(Request $request, Project $project)
    {
        $this->authorize('update', $project);

        $data = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'file_path' => ['nullable', 'string'],
            'status' => [Rule::in(['brouillon', 'soumis', 'en_validation', 'valide', 'rejete'])],
        ]);

        if (! $request->user()->can('pairings.manage')) {
            // Mentees may only submit their own draft project; validation is staff-only.
            unset($data['status']);
            if ($request->boolean('submit') && $project->status === 'brouillon') {
                $data['status'] = 'soumis';
            }
        }

        $project->update($data);

        return $project;
    }

    #[OA\Delete(
        path: '/projects/{project}',
        summary: 'Supprimer un projet',
        security: [['bearerAuth' => []]],
        tags: ['Projets'],
        parameters: [new OA\PathParameter(name: 'project', schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 204, description: 'Supprimé'),
            new OA\Response(response: 403, description: 'Non autorisée'),
        ]
    )]
    public function destroy(Project $project)
    {
        $this->authorize('delete', $project);

        $project->delete();

        return response()->noContent();
    }
}
