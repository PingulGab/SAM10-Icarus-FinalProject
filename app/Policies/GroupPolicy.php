<?php

namespace App\Policies;

use App\Models\Group;
use App\Models\User;

class GroupPolicy
{
    public function view(User $user, Group $group): bool
    {
        return $group->user_id === $user->id;
    }

    public function update(User $user, Group $group): bool
    {
        return $group->user_id === $user->id;
    }

    public function delete(User $user, Group $group): bool
    {
        return $group->user_id === $user->id;
    }
}
