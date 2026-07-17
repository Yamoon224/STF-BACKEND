<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use OpenApi\Attributes as OA;

class GroupController extends Controller
{
    #[OA\Get(
        path: '/groups',
        summary: 'Lister les groupes',
        description: 'Sans `groups.manage`, ne renvoie que les groupes dont on est membre.',
        security: [['bearerAuth' => []]],
        tags: ['Groupes'],
        parameters: [new OA\QueryParameter(name: 'program_id', schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Groupes', content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/Group')))]
    )]
    public function index(Request $request)
    {
        $user = $request->user();

        return Group::query()
            ->withCount('members')
            ->when(! $user->can('groups.manage'), fn ($q) => $q->whereHas('members', fn ($q) => $q->whereKey($user->id)))
            ->when($request->query('program_id'), fn ($q, $id) => $q->where('program_id', $id))
            ->orderBy('name')
            ->get();
    }

    #[OA\Get(
        path: '/groups/{group}',
        summary: 'Consulter un groupe',
        security: [['bearerAuth' => []]],
        tags: ['Groupes'],
        parameters: [new OA\PathParameter(name: 'group', schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Groupe', content: new OA\JsonContent(ref: '#/components/schemas/Group')),
            new OA\Response(response: 403, description: 'Non membre du groupe'),
        ]
    )]
    public function show(Group $group)
    {
        $this->authorize('view', $group);

        return $group->load(['members', 'program']);
    }

    #[OA\Post(
        path: '/groups',
        summary: 'Créer un groupe',
        description: "La créatrice devient automatiquement animatrice du groupe.",
        security: [['bearerAuth' => []]],
        tags: ['Groupes'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(required: ['name'], properties: [
                new OA\Property(property: 'name', type: 'string'),
                new OA\Property(property: 'type', type: 'string', enum: ['automatique', 'travail', 'mentorat'], default: 'travail'),
                new OA\Property(property: 'program_id', type: 'integer', nullable: true),
                new OA\Property(property: 'status', type: 'string', enum: ['en_validation', 'actif', 'archive'], default: 'en_validation'),
            ])
        ),
        responses: [
            new OA\Response(response: 201, description: 'Créé', content: new OA\JsonContent(ref: '#/components/schemas/Group')),
            new OA\Response(response: 403, description: "Permission `groups.manage` requise"),
        ]
    )]
    public function store(Request $request)
    {
        $this->authorize('create', Group::class);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => [Rule::in(['automatique', 'travail', 'mentorat'])],
            'program_id' => ['nullable', 'exists:programs,id'],
            'status' => [Rule::in(['en_validation', 'actif', 'archive'])],
        ]);

        $data['type'] ??= 'travail';
        $data['status'] ??= 'en_validation';
        $data['created_by'] = $request->user()->id;

        $group = Group::create($data);
        $group->members()->attach($request->user()->id, [
            'role_in_group' => 'animatrice',
            'joined_at' => now(),
        ]);

        return response()->json($group, 201);
    }

    #[OA\Patch(
        path: '/groups/{group}',
        summary: 'Modifier un groupe',
        security: [['bearerAuth' => []]],
        tags: ['Groupes'],
        parameters: [new OA\PathParameter(name: 'group', schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(content: new OA\JsonContent(properties: [
            new OA\Property(property: 'name', type: 'string'),
            new OA\Property(property: 'type', type: 'string', enum: ['automatique', 'travail', 'mentorat']),
            new OA\Property(property: 'status', type: 'string', enum: ['en_validation', 'actif', 'archive']),
        ])),
        responses: [
            new OA\Response(response: 200, description: 'Modifié', content: new OA\JsonContent(ref: '#/components/schemas/Group')),
            new OA\Response(response: 403, description: 'Non autorisée'),
        ]
    )]
    public function update(Request $request, Group $group)
    {
        $this->authorize('update', $group);

        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'type' => [Rule::in(['automatique', 'travail', 'mentorat'])],
            'status' => [Rule::in(['en_validation', 'actif', 'archive'])],
        ]);

        $group->update($data);

        return $group;
    }

    #[OA\Delete(
        path: '/groups/{group}',
        summary: 'Supprimer un groupe',
        security: [['bearerAuth' => []]],
        tags: ['Groupes'],
        parameters: [new OA\PathParameter(name: 'group', schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 204, description: 'Supprimé'),
            new OA\Response(response: 403, description: "Permission `groups.manage` requise"),
        ]
    )]
    public function destroy(Group $group)
    {
        $this->authorize('delete', $group);

        $group->delete();

        return response()->noContent();
    }

    #[OA\Post(
        path: '/groups/{group}/members',
        summary: 'Ajouter un membre au groupe',
        security: [['bearerAuth' => []]],
        tags: ['Groupes'],
        parameters: [new OA\PathParameter(name: 'group', schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(required: ['user_id'], properties: [
                new OA\Property(property: 'user_id', type: 'integer'),
                new OA\Property(property: 'role_in_group', type: 'string', enum: ['membre', 'animatrice'], default: 'membre'),
            ])
        ),
        responses: [
            new OA\Response(response: 200, description: 'Membre ajouté', content: new OA\JsonContent(ref: '#/components/schemas/Group')),
            new OA\Response(response: 403, description: 'Non autorisée'),
        ]
    )]
    public function addMember(Request $request, Group $group)
    {
        $this->authorize('update', $group);

        $data = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'role_in_group' => [Rule::in(['membre', 'animatrice'])],
        ]);

        $group->members()->syncWithoutDetaching([
            $data['user_id'] => [
                'role_in_group' => $data['role_in_group'] ?? 'membre',
                'joined_at' => now(),
            ],
        ]);

        return response()->json($group->load('members'));
    }

    #[OA\Delete(
        path: '/groups/{group}/members/{userId}',
        summary: 'Retirer un membre du groupe',
        security: [['bearerAuth' => []]],
        tags: ['Groupes'],
        parameters: [
            new OA\PathParameter(name: 'group', schema: new OA\Schema(type: 'integer')),
            new OA\PathParameter(name: 'userId', schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Membre retiré'),
            new OA\Response(response: 403, description: 'Non autorisée'),
        ]
    )]
    public function removeMember(Request $request, Group $group, int $userId)
    {
        $this->authorize('update', $group);

        $group->members()->detach($userId);

        return response()->noContent();
    }
}
