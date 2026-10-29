<?php

namespace App\Policies;

use App\Models\SavingsContribution;
use App\Models\User;

class SavingsContributionPolicy
{
    public function before(User $user): ?bool
    {
        return $user->isSuperAdmin() ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, SavingsContribution $contribution): bool
    {
        return $contribution->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, SavingsContribution $contribution): bool
    {
        return $contribution->user_id === $user->id;
    }

    public function delete(User $user, SavingsContribution $contribution): bool
    {
        return $contribution->user_id === $user->id;
    }
}
