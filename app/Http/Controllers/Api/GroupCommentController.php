<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GroupPost;
use Illuminate\Http\Request;

class GroupCommentController extends Controller
{
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

    public function destroy(Request $request, \App\Models\GroupComment $comment)
    {
        abort_unless(
            $request->user()->id === $comment->author_id || $request->user()->can('groups.manage'),
            403
        );

        $comment->delete();

        return response()->noContent();
    }
}
