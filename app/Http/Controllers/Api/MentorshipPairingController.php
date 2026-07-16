<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\MentorshipPairing;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MentorshipPairingController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', MentorshipPairing::class);

        $user = $request->user();

        return MentorshipPairing::query()
            ->with(['mentee', 'mentor', 'program', 'cohort'])
            ->withCount(['sessions as sessions_realisees_count' => fn ($q) => $q->where('status', 'realisee')])
            ->when(! $user->can('pairings.manage'), function ($q) use ($user) {
                $q->where('mentee_id', $user->id)->orWhere('mentor_id', $user->id);
            })
            ->when($request->query('status'), fn ($q, $status) => $q->where('status', $status))
            ->when($request->query('program_id'), fn ($q, $id) => $q->where('program_id', $id))
            ->orderByDesc('created_at')
            ->paginate(20);
    }

    public function show(MentorshipPairing $pairing)
    {
        $this->authorize('view', $pairing);

        return $pairing->load(['mentee', 'mentor', 'program', 'cohort', 'sessions']);
    }

    public function store(Request $request)
    {
        $this->authorize('create', MentorshipPairing::class);

        $data = $request->validate([
            'mentee_id' => ['required', 'exists:users,id'],
            'mentor_id' => ['nullable', 'exists:users,id'],
            'program_id' => ['required', 'exists:programs,id'],
            'cohort_id' => ['nullable', 'exists:cohorts,id'],
            'match_score' => ['nullable', 'integer', 'min:0', 'max:100'],
            'notes' => ['nullable', 'string'],
        ]);

        $data['status'] = $data['mentor_id'] ?? null ? 'actif' : 'en_attente';
        if ($data['status'] === 'actif') {
            $data['matched_at'] = now();
        }

        $pairing = MentorshipPairing::create($data);

        AuditLog::record($request->user(), 'binome.cree', $pairing);

        return response()->json($pairing->load(['mentee', 'mentor', 'program']), 201);
    }

    public function update(Request $request, MentorshipPairing $pairing)
    {
        $this->authorize('update', $pairing);

        $data = $request->validate([
            'mentor_id' => ['nullable', 'exists:users,id'],
            'status' => [Rule::in(['en_attente', 'actif', 'pause', 'termine'])],
            'match_score' => ['nullable', 'integer', 'min:0', 'max:100'],
            'notes' => ['nullable', 'string'],
        ]);

        if (($data['mentor_id'] ?? null) && ! $pairing->matched_at) {
            $data['matched_at'] = now();
        }

        if (($data['status'] ?? null) === 'termine') {
            $data['ended_at'] = now();
        }

        $pairing->update($data);

        AuditLog::record($request->user(), 'binome.modifie', $pairing);

        return $pairing->load(['mentee', 'mentor', 'program']);
    }

    public function destroy(MentorshipPairing $pairing)
    {
        $this->authorize('delete', $pairing);

        $pairing->delete();

        return response()->noContent();
    }
}
