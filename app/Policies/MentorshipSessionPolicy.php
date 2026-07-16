<?php

namespace App\Policies;

use App\Models\MentorshipSession;
use App\Models\User;

class MentorshipSessionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('sessions.manage') || $user->hasAnyRole(['mentor', 'mentee']);
    }

    public function view(User $user, MentorshipSession $session): bool
    {
        return $this->belongsToPairing($user, $session) || $user->can('sessions.manage');
    }

    public function create(User $user): bool
    {
        return $user->can('sessions.manage') || $user->hasRole('mentor');
    }

    public function update(User $user, MentorshipSession $session): bool
    {
        return $user->can('sessions.manage') || $this->belongsToPairing($user, $session);
    }

    public function delete(User $user, MentorshipSession $session): bool
    {
        return $user->can('sessions.manage');
    }

    protected function belongsToPairing(User $user, MentorshipSession $session): bool
    {
        $pairing = $session->pairing;

        return $pairing && ($user->id === $pairing->mentee_id || $user->id === $pairing->mentor_id);
    }
}
