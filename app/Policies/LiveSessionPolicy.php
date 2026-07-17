<?php

namespace App\Policies;

use App\Models\LiveSession;
use App\Models\User;

class LiveSessionPolicy
{
    public function create(User $user): bool
    {
        return $user->can('programs.manage');
    }

    public function update(User $user, LiveSession $liveSession): bool
    {
        return $user->can('programs.manage');
    }

    public function delete(User $user, LiveSession $liveSession): bool
    {
        return $user->can('programs.manage');
    }
}
