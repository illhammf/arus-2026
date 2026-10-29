<?php

namespace App\Policies;

use App\Models\SavingsGoal;
use App\Models\User;

class SavingsGoalPolicy
{
    public function before(User $user): ?bool
    {
        return $user->isSuperAdmin() ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, SavingsGoal $goal): bool
    {
        return $goal->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, SavingsGoal $goal): bool
    {
        return $goal->user_id === $user->id;
    }

    public function delete(User $user, SavingsGoal $goal): bool
    {
        return $goal->user_id === $user->id;
    }

    public function restore(User $user, SavingsGoal $goal): bool
    {
        return $goal->user_id === $user->id;
    }

    public function forceDelete(User $user, SavingsGoal $goal): bool
    {
        return $goal->user_id === $user->id;
    }
}
