<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MentorshipPairing;
use App\Models\MentorshipSession;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MentorshipSessionController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', MentorshipSession::class);

        $user = $request->user();

        return MentorshipSession::query()
            ->with(['pairing.mentee', 'pairing.mentor'])
            ->when(! $user->can('sessions.manage'), function ($q) use ($user) {
                $q->whereHas('pairing', fn ($q) => $q->where('mentee_id', $user->id)->orWhere('mentor_id', $user->id));
            })
            ->when($request->query('pairing_id'), fn ($q, $id) => $q->where('pairing_id', $id))
            ->when($request->query('status'), fn ($q, $status) => $q->where('status', $status))
            ->orderBy('scheduled_at')
            ->paginate(20);
    }

    public function show(MentorshipSession $session)
    {
        $this->authorize('view', $session);

        return $session->load(['pairing.mentee', 'pairing.mentor', 'notes.author']);
    }

    public function store(Request $request)
    {
        $this->authorize('create', MentorshipSession::class);

        $data = $request->validate([
            'pairing_id' => ['required', 'exists:mentorship_pairings,id'],
            'scheduled_at' => ['required', 'date'],
            'duration_minutes' => ['nullable', 'integer', 'min:15', 'max:240'],
            'topic' => ['nullable', 'string', 'max:255'],
            'location_or_link' => ['nullable', 'string', 'max:255'],
        ]);

        $pairing = MentorshipPairing::findOrFail($data['pairing_id']);
        abort_unless(
            $request->user()->can('sessions.manage')
                || in_array($request->user()->id, [$pairing->mentee_id, $pairing->mentor_id], true),
            403
        );

        $data['created_by'] = $request->user()->id;
        $data['status'] = 'en_attente';

        return response()->json(MentorshipSession::create($data)->load('pairing'), 201);
    }

    public function update(Request $request, MentorshipSession $session)
    {
        $this->authorize('update', $session);

        $data = $request->validate([
            'scheduled_at' => ['sometimes', 'date'],
            'duration_minutes' => ['nullable', 'integer', 'min:15', 'max:240'],
            'status' => [Rule::in(['en_attente', 'confirmee', 'realisee', 'annulee'])],
            'topic' => ['nullable', 'string', 'max:255'],
            'location_or_link' => ['nullable', 'string', 'max:255'],
        ]);

        $session->update($data);

        return $session->load('pairing');
    }

    public function destroy(MentorshipSession $session)
    {
        $this->authorize('delete', $session);

        $session->delete();

        return response()->noContent();
    }
}
