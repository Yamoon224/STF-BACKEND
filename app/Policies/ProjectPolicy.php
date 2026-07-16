<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Project $project): bool
    {
        if ($user->can('pairings.manage') || $user->id === $project->mentee_id) {
            return true;
        }

        $pairing = $project->pairing;

        return $pairing && $user->id === $pairing->mentor_id;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('mentee');
    }

    public function update(User $user, Project $project): bool
    {
        return $user->id === $project->mentee_id || $user->can('pairings.manage');
    }

    public function delete(User $user, Project $project): bool
    {
        return $user->id === $project->mentee_id || $user->can('pairings.manage');
    }
}
