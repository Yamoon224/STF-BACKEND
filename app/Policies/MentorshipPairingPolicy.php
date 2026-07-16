<?php

namespace App\Policies;

use App\Models\MentorshipPairing;
use App\Models\User;

class MentorshipPairingPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('pairings.manage') || $user->hasAnyRole(['mentor', 'mentee']);
    }

    public function view(User $user, MentorshipPairing $pairing): bool
    {
        return $user->can('pairings.manage')
            || $user->id === $pairing->mentee_id
            || $user->id === $pairing->mentor_id;
    }

    public function create(User $user): bool
    {
        return $user->can('pairings.manage') || $user->can('matching.manage');
    }

    public function update(User $user, MentorshipPairing $pairing): bool
    {
        return $user->can('pairings.manage') || $user->can('matching.manage');
    }

    public function delete(User $user, MentorshipPairing $pairing): bool
    {
        return $user->can('pairings.manage');
    }
}
