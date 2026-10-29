<?php

namespace App\Policies;

use App\Models\FinancialAccount;
use App\Models\User;

class FinancialAccountPolicy
{
    public function before(User $user): ?bool
    {
        return $user->isSuperAdmin() ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, FinancialAccount $account): bool
    {
        return $account->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, FinancialAccount $account): bool
    {
        return $account->user_id === $user->id;
    }

    public function delete(User $user, FinancialAccount $account): bool
    {
        return $account->user_id === $user->id;
    }

    public function restore(User $user, FinancialAccount $account): bool
    {
        return $account->user_id === $user->id;
    }

    public function forceDelete(User $user, FinancialAccount $account): bool
    {
        return $account->user_id === $user->id;
    }
}
