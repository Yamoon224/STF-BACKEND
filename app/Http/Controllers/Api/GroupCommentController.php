<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GroupComment;
use App\Models\GroupPost;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class GroupCommentController extends Controller
{
    #[OA\Post(
        path: '/posts/{post}/comments',
        summary: 'Commenter une publication',
        security: [['bearerAuth' => []]],
        tags: ['Groupes'],
        parameters: [new OA\PathParameter(name: 'post', schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(required: ['content'], properties: [new OA\Property(property: 'content', type: 'string')])
        ),
        responses: [
            new OA\Response(response: 201, description: 'Commentaire créé', content: new OA\JsonContent(ref: '#/components/schemas/GroupComment')),
            new OA\Response(response: 403, description: 'Non membre du groupe'),
        ]
    )]
    public function store(Request $request, GroupPost $post)
    {
        $this->authorize('post', $post->group);

        $data = $request->validate(['content' => ['required', 'string']]);

        $comment = $post->comments()->create([
            'author_id' => $request->user()->id,
            'content' => $data['content'],
        ]);

        return response()->json($comment->load('author'), 201);
    }

    #[OA\Delete(
        path: '/comments/{comment}',
        summary: 'Supprimer un commentaire',
        description: "Réservé à l'autrice ou à `groups.manage`.",
        security: [['bearerAuth' => []]],
        tags: ['Groupes'],
        parameters: [new OA\PathParameter(name: 'comment', schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 204, description: 'Supprimé'),
            new OA\Response(response: 403, description: "Réservé à l'autrice"),
        ]
    )]
    public function destroy(Request $request, GroupComment $comment)
    {
        abort_unless(
            $request->user()->id === $comment->author_id || $request->user()->can('groups.manage'),
            403
        );

        $comment->delete();

        return response()->noContent();
    }
}
