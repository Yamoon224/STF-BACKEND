<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\GroupFile;
use Illuminate\Http\Request;

class GroupFileController extends Controller
{
    public function index(Group $group)
    {
        $this->authorize('view', $group);

        return $group->files()->with('uploader')->orderByDesc('created_at')->get();
    }

    public function store(Request $request, Group $group)
    {
        $this->authorize('post', $group);

        $data = $request->validate([
            'file' => ['required', 'file', 'max:20480'],
        ]);

        $path = $request->file('file')->store("groups/{$group->id}", 'local');

        $file = $group->files()->create([
            'uploader_id' => $request->user()->id,
            'name' => $request->file('file')->getClientOriginalName(),
            'path' => $path,
            'mime_type' => $request->file('file')->getMimeType(),
            'size_bytes' => $request->file('file')->getSize(),
        ]);

        return response()->json($file->load('uploader'), 201);
    }

    public function destroy(Request $request, GroupFile $file)
    {
        abort_unless(
            $request->user()->id === $file->uploader_id || $request->user()->can('groups.manage'),
            403
        );

        \Illuminate\Support\Facades\Storage::disk('local')->delete($file->path);
        $file->delete();

        return response()->noContent();
    }
}
