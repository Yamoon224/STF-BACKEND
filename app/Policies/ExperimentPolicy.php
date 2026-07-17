<?php

namespace App\Policies;

use App\Models\Experiment;
use App\Models\User;

class ExperimentPolicy
{
    public function create(User $user): bool
    {
        return $user->can('programs.manage');
    }

    public function update(User $user, Experiment $experiment): bool
    {
        return $user->can('programs.manage');
    }

    public function delete(User $user, Experiment $experiment): bool
    {
        return $user->can('programs.manage');
    }
}
