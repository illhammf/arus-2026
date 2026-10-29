<?php

use App\Models\Budget;
use App\Models\FinancialAccount;
use App\Models\SavingsGoal;
use App\Models\Transaction;
use App\Models\User;

it('deletes a user together with their financial data', function () {
    $user = makeUser();
    $account = FinancialAccount::factory()->for($user)->create(['opening_balance' => '1000000.00']);

    Transaction::factory()->for($user)->for($account, 'account')->expense()->create();
    Budget::factory()->for($user)->create();
    SavingsGoal::factory()->for($user)->create();

    $user->delete();

    expect(User::query()->find($user->id))->toBeNull()
        ->and(Transaction::withTrashed()->where('user_id', $user->id)->count())->toBe(0)
        ->and(FinancialAccount::query()->where('user_id', $user->id)->count())->toBe(0)
        ->and(Budget::query()->where('user_id', $user->id)->count())->toBe(0)
        ->and(SavingsGoal::query()->where('user_id', $user->id)->count())->toBe(0);
});
