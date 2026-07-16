<?php

namespace App\Policies;

use App\Models\Group;
use App\Models\User;

class GroupPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Group $group): bool
    {
        return $user->can('groups.manage') || $group->members()->whereKey($user->id)->exists();
    }

    public function create(User $user): bool
    {
        return $user->can('groups.manage');
    }

    public function update(User $user, Group $group): bool
    {
        return $user->can('groups.manage')
            || $group->members()->wherePivot('role_in_group', 'animatrice')->whereKey($user->id)->exists();
    }

    public function delete(User $user, Group $group): bool
    {
        return $user->can('groups.manage');
    }

    public function post(User $user, Group $group): bool
    {
        return $user->can('groups.manage') || $group->members()->whereKey($user->id)->exists();
    }
}
