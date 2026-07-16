<?php

namespace App\Policies;

use App\Models\SessionNote;
use App\Models\User;

class SessionNotePolicy
{
    public function view(User $user, SessionNote $note): bool
    {
        if ($user->can('sessions.manage') || $user->id === $note->author_id) {
            return true;
        }

        if ($note->visibility === 'privee') {
            return false;
        }

        $pairing = $note->session?->pairing;

        return $pairing && ($user->id === $pairing->mentee_id || $user->id === $pairing->mentor_id);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['mentor', 'mentee']) || $user->can('sessions.manage');
    }

    public function update(User $user, SessionNote $note): bool
    {
        return $user->id === $note->author_id || $user->can('sessions.manage');
    }

    public function delete(User $user, SessionNote $note): bool
    {
        return $user->id === $note->author_id || $user->can('sessions.manage');
    }
}
