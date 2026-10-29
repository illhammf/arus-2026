<?php

namespace App\Services;

use App\Models\FinancialAccount;
use App\Models\Transaction;
use App\Models\User;
use App\Support\Money;

class FinancialAccountService
{
    /**
     * Return account balances keyed by account id using one aggregate query.
     *
     * @return array<int, string>
     */
    public function balancesFor(User $user, bool $includeArchived = false): array
    {
        $accounts = $user->financialAccounts()
            ->when(! $includeArchived, fn ($query) => $query->where('is_archived', false))
            ->get();

        $accountIds = $accounts->pluck('id');

        $movements = Transaction::query()
            ->whereIn('account_id', $accountIds)
            ->posted()
            ->selectRaw("account_id, COALESCE(SUM(CASE WHEN type IN ('income', 'transfer_in') THEN amount ELSE -amount END), 0) as total")
            ->groupBy('account_id')
            ->pluck('total', 'account_id');

        $balances = [];

        foreach ($accounts as $account) {
            $balances[$account->id] = Money::add(
                $account->opening_balance,
                $movements[$account->id] ?? 0,
            );
        }

        return $balances;
    }

    public function totalBalance(User $user): string
    {
        return Money::add(...array_values($this->balancesFor($user)));
    }

    public function balance(FinancialAccount $account): string
    {
        return $account->balance();
    }
}
