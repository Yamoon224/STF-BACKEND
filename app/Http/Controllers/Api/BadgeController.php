<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Badge;
use App\Models\User;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class BadgeController extends Controller
{
    #[OA\Get(
        path: '/badges',
        summary: 'Lister les badges',
        description: '`mine=1` renvoie les badges obtenus par l’utilisatrice connectée ; sinon la liste de tous les types de badges.',
        security: [['bearerAuth' => []]],
        tags: ['Badges'],
        parameters: [new OA\QueryParameter(name: 'mine', schema: new OA\Schema(type: 'boolean'))],
        responses: [new OA\Response(response: 200, description: 'Badges', content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/Badge')))]
    )]
    public function index(Request $request)
    {
        if ($request->boolean('mine') && $request->user()) {
            return $request->user()->badges;
        }

        return Badge::all();
    }

    #[OA\Post(
        path: '/badges',
        summary: 'Créer un type de badge',
        security: [['bearerAuth' => []]],
        tags: ['Badges'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(required: ['title'], properties: [
                new OA\Property(property: 'title', type: 'string'),
                new OA\Property(property: 'description', type: 'string', nullable: true),
                new OA\Property(property: 'icon', type: 'string', nullable: true),
                new OA\Property(property: 'criteria', type: 'string', nullable: true),
            ])
        ),
        responses: [
            new OA\Response(response: 201, description: 'Créé', content: new OA\JsonContent(ref: '#/components/schemas/Badge')),
            new OA\Response(response: 403, description: "Permission `users.manage` requise"),
        ]
    )]
    public function store(Request $request)
    {
        abort_unless($request->user()->can('users.manage'), 403);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'icon' => ['nullable', 'string', 'max:255'],
            'criteria' => ['nullable', 'string'],
        ]);

        return response()->json(Badge::create($data), 201);
    }

    #[OA\Post(
        path: '/badges/{badge}/award',
        summary: 'Attribuer un badge à une utilisatrice',
        security: [['bearerAuth' => []]],
        tags: ['Badges'],
        parameters: [new OA\PathParameter(name: 'badge', schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(required: ['user_id'], properties: [new OA\Property(property: 'user_id', type: 'integer')])
        ),
        responses: [
            new OA\Response(response: 200, description: 'Badge attribué', content: new OA\JsonContent(ref: '#/components/schemas/Badge')),
            new OA\Response(response: 403, description: "Permission `users.manage` requise"),
        ]
    )]
    public function award(Request $request, Badge $badge)
    {
        abort_unless($request->user()->can('users.manage'), 403);

        $data = $request->validate(['user_id' => ['required', 'exists:users,id']]);

        $badge->users()->syncWithoutDetaching([
            $data['user_id'] => ['awarded_at' => now(), 'awarded_by' => $request->user()->id],
        ]);

        $awardedUser = User::findOrFail($data['user_id']);
        AuditLog::record($request->user(), 'badge.attribue', $awardedUser, ['badge_id' => $badge->id]);

        return response()->json($badge->load('users'));
    }
}
