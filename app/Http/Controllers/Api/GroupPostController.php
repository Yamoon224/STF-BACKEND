<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\GroupPost;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class GroupPostController extends Controller
{
    #[OA\Get(
        path: '/groups/{group}/posts',
        summary: "Lister les publications d'un groupe",
        security: [['bearerAuth' => []]],
        tags: ['Groupes'],
        parameters: [new OA\PathParameter(name: 'group', schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Publications', content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/GroupPost'))),
            new OA\Response(response: 403, description: 'Non membre du groupe'),
        ]
    )]
    public function index(Group $group)
    {
        $this->authorize('view', $group);

        return $group->posts()->with(['author', 'comments.author'])->orderByDesc('created_at')->get();
    }

    #[OA\Post(
        path: '/groups/{group}/posts',
        summary: 'Publier dans un groupe',
        security: [['bearerAuth' => []]],
        tags: ['Groupes'],
        parameters: [new OA\PathParameter(name: 'group', schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(required: ['content'], properties: [new OA\Property(property: 'content', type: 'string')])
        ),
        responses: [
            new OA\Response(response: 201, description: 'Publiée', content: new OA\JsonContent(ref: '#/components/schemas/GroupPost')),
            new OA\Response(response: 403, description: 'Non membre du groupe'),
        ]
    )]
    public function store(Request $request, Group $group)
    {
        $this->authorize('post', $group);

        $data = $request->validate(['content' => ['required', 'string']]);

        $post = $group->posts()->create([
            'author_id' => $request->user()->id,
            'content' => $data['content'],
        ]);

        return response()->json($post->load('author'), 201);
    }

    #[OA\Delete(
        path: '/posts/{post}',
        summary: 'Supprimer une publication',
        description: "Réservé à l'autrice ou à `groups.manage`.",
        security: [['bearerAuth' => []]],
        tags: ['Groupes'],
        parameters: [new OA\PathParameter(name: 'post', schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 204, description: 'Supprimée'),
            new OA\Response(response: 403, description: "Réservé à l'autrice"),
        ]
    )]
    public function destroy(Request $request, GroupPost $post)
    {
        $group = $post->group;
        abort_unless(
            $request->user()->id === $post->author_id || $request->user()->can('groups.manage'),
            403
        );

        $post->delete();

        return response()->noContent();
    }
}
