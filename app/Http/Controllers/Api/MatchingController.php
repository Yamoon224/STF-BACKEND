<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MentorProfile;
use App\Models\MentorshipPairing;
use Illuminate\Http\Request;

class MatchingController extends Controller
{
    /**
     * Naive matching suggestions: mentees without an active pairing, matched against
     * validated mentors who still have capacity. The STF team confirms/adjusts manually.
     */
    public function suggestions(Request $request)
    {
        $this->authorize('create', MentorshipPairing::class);

        $unmatchedMentees = MentorshipPairing::query()
            ->where('status', 'en_attente')
            ->whereNull('mentor_id')
            ->with(['mentee.menteeProfile', 'program'])
            ->when($request->query('program_id'), fn ($q, $id) => $q->where('program_id', $id))
            ->get();

        $availableMentors = MentorProfile::query()
            ->whereNotNull('validated_at')
            ->withCount(['user as active_pairings_count' => function ($q) {
                $q->join('mentorship_pairings', 'mentorship_pairings.mentor_id', '=', 'users.id')
                    ->where('mentorship_pairings.status', 'actif');
            }])
            ->with('user')
            ->get()
            ->filter(fn ($profile) => $profile->active_pairings_count < $profile->capacity)
            ->values();

        $suggestions = $unmatchedMentees->map(function (MentorshipPairing $pairing) use ($availableMentors) {
            $mentor = $availableMentors->first();

            return [
                'pairing_id' => $pairing->id,
                'mentee' => $pairing->mentee,
                'program' => $pairing->program,
                'suggested_mentor' => $mentor?->user,
                'score' => $mentor ? 75 : null,
            ];
        });

        return response()->json($suggestions->values());
    }
}
