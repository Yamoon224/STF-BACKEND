<?php

namespace App\Policies;

use App\Models\Cohort;
use App\Models\User;

class CohortPolicy
{
    public function create(User $user): bool
    {
        return $user->can('cohorts.manage');
    }

    public function update(User $user, Cohort $cohort): bool
    {
        return $user->can('cohorts.manage');
    }

    public function delete(User $user, Cohort $cohort): bool
    {
        return $user->can('cohorts.manage');
    }
}
