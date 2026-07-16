<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\GroupPost;
use Illuminate\Http\Request;

class GroupPostController extends Controller
{
    public function index(Group $group)
    {
        $this->authorize('view', $group);

        return $group->posts()->with(['author', 'comments.author'])->orderByDesc('created_at')->get();
    }

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
