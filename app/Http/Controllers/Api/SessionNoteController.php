<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MentorshipSession;
use App\Models\SessionNote;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SessionNoteController extends Controller
{
    public function index(Request $request, MentorshipSession $session)
    {
        $this->authorize('view', $session);

        return $session->notes()
            ->with('author')
            ->get()
            ->filter(fn (SessionNote $note) => $request->user()->can('view', $note))
            ->values();
    }

    public function store(Request $request, MentorshipSession $session)
    {
        $this->authorize('view', $session);
        $this->authorize('create', SessionNote::class);

        $data = $request->validate([
            'content' => ['required', 'string'],
            'visibility' => [Rule::in(['partagee', 'privee'])],
        ]);

        $data['session_id'] = $session->id;
        $data['author_id'] = $request->user()->id;

        return response()->json(SessionNote::create($data)->load('author'), 201);
    }

    public function update(Request $request, SessionNote $note)
    {
        $this->authorize('update', $note);

        $data = $request->validate([
            'content' => ['sometimes', 'string'],
            'visibility' => [Rule::in(['partagee', 'privee'])],
        ]);

        $note->update($data);

        return $note;
    }

    public function destroy(SessionNote $note)
    {
        $this->authorize('delete', $note);

        $note->delete();

        return response()->noContent();
    }
}
