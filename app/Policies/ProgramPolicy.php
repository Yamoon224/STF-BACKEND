<?php

namespace App\Policies;

use App\Models\Program;
use App\Models\User;

class ProgramPolicy
{
    public function create(User $user): bool
    {
        return $user->can('programs.manage');
    }

    public function update(User $user, Program $program): bool
    {
        return $user->can('programs.manage');
    }

    public function delete(User $user, Program $program): bool
    {
        return $user->can('programs.manage');
    }
}
