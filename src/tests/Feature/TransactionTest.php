<?php

use App\Enums\TransactionType;
use App\Models\Category;
use App\Models\FinancialAccount;
use App\Models\Transaction;

it('creates an expense and reduces the account balance', function () {
    $user = makeUser();
    $account = FinancialAccount::factory()->for($user)->create(['opening_balance' => '1000000.00']);

    $response = $this->actingAs($user)->post(route('transactions.store'), [
        'type' => 'expense',
        'account_id' => $account->id,
        'category_id' => null,
        'amount' => '250.000',
        'occurred_at' => '2026-10-29 12:30:00',
        'description' => 'Makan siang',
        'notes' => 'Nasi padang',
    ]);

    $transaction = Transaction::query()->latest('id')->first();

    $response->assertRedirect(route('transactions.show', $transaction));

    expect($transaction->user_id)->toBe($user->id)
        ->and($transaction->account_id)->toBe($account->id)
        ->and($transaction->type)->toBe(TransactionType::Expense)
        ->and($transaction->status->value)->toBe('posted')
        ->and($transaction->amount)->toBe('250000.00')
        ->and($transaction->currency)->toBe('IDR')
        ->and($transaction->description)->toBe('Makan siang')
        ->and($account->fresh()->balance())->toBe('750000.00');
});

it('creates income and increases the account balance', function () {
    $user = makeUser();
    $account = FinancialAccount::factory()->for($user)->create(['opening_balance' => '0.00']);
    $category = Category::factory()->income()->create(['name' => 'Gaji']);

    $this->actingAs($user)->post(route('transactions.store'), [
        'type' => 'income',
        'account_id' => $account->id,
        'category_id' => $category->id,
        'amount' => '8.500.000',
        'occurred_at' => '2026-10-25 09:00:00',
    ])->assertSessionHasNoErrors();

    $transaction = Transaction::query()->latest('id')->first();

    expect($transaction->type)->toBe(TransactionType::Income)
        ->and($transaction->description)->toBe('Gaji')
        ->and($account->fresh()->balance())->toBe('8500000.00');
});

it('accepts decimal money with comma separators', function () {
    $user = makeUser();
    $account = FinancialAccount::factory()->for($user)->create(['opening_balance' => '0.00']);

    $this->actingAs($user)->post(route('transactions.store'), [
        'type' => 'expense',
        'account_id' => $account->id,
        'amount' => '1.000,50',
        'occurred_at' => '2026-10-29 12:30:00',
        'description' => 'Belanja',
    ])->assertSessionHasNoErrors();

    expect(Transaction::query()->latest('id')->first()->amount)->toBe('1000.50');
});

it('rejects another users account', function () {
    $owner = makeUser();
    $intruder = makeUser();
    $account = FinancialAccount::factory()->for($owner)->create();

    $this->actingAs($intruder)->post(route('transactions.store'), [
        'type' => 'expense',
        'account_id' => $account->id,
        'amount' => '10000',
        'occurred_at' => now()->toDateTimeString(),
        'description' => 'Nope',
    ])->assertSessionHasErrors('account_id');

    expect(Transaction::query()->count())->toBe(0);
});

it('rejects archived accounts and mismatched category types', function () {
    $user = makeUser();
    $archived = FinancialAccount::factory()->for($user)->create(['is_archived' => true]);
    $account = FinancialAccount::factory()->for($user)->create();
    $expenseCategory = Category::factory()->create();

    $this->actingAs($user)->post(route('transactions.store'), [
        'type' => 'expense',
        'account_id' => $archived->id,
        'amount' => '10000',
        'occurred_at' => now()->toDateTimeString(),
    ])->assertSessionHasErrors('account_id');

    $this->actingAs($user)->post(route('transactions.store'), [
        'type' => 'income',
        'account_id' => $account->id,
        'category_id' => $expenseCategory->id,
        'amount' => '10000',
        'occurred_at' => now()->toDateTimeString(),
    ])->assertSessionHasErrors('category_id');
});

it('updates a transaction and recalculates the balance', function () {
    $user = makeUser();
    $account = FinancialAccount::factory()->for($user)->create(['opening_balance' => '1000000.00']);
    $transaction = Transaction::factory()->for($user)->for($account, 'account')->expense()->create([
        'amount' => '250000.00',
    ]);

    $this->actingAs($user)->put(route('transactions.update', $transaction), [
        'type' => 'expense',
        'account_id' => $account->id,
        'amount' => '400.000',
        'occurred_at' => '2026-10-20 10:00:00',
        'description' => 'Belanja bulanan',
    ])->assertRedirect(route('transactions.show', $transaction));

    expect($transaction->fresh()->amount)->toBe('400000.00')
        ->and($transaction->fresh()->description)->toBe('Belanja bulanan')
        ->and($account->fresh()->balance())->toBe('600000.00');
});

it('deletes a transaction and restores the balance', function () {
    $user = makeUser();
    $account = FinancialAccount::factory()->for($user)->create(['opening_balance' => '1000000.00']);
    $transaction = Transaction::factory()->for($user)->for($account, 'account')->expense()->create([
        'amount' => '250000.00',
    ]);

    expect($account->fresh()->balance())->toBe('750000.00');

    $this->actingAs($user)
        ->delete(route('transactions.destroy', $transaction))
        ->assertRedirect(route('transactions.index'));

    expect(Transaction::query()->find($transaction->id))->toBeNull()
        ->and($account->fresh()->balance())->toBe('1000000.00');
});

it('filters the transaction list by owner, type and search', function () {
    $user = makeUser();
    $other = makeUser();
    $account = FinancialAccount::factory()->for($user)->create();

    Transaction::factory()->for($user)->for($account, 'account')->income()->create(['description' => 'Gaji Oktober']);
    Transaction::factory()->for($user)->for($account, 'account')->expense()->create(['description' => 'Makan siang']);
    Transaction::factory()->for($other)->create(['description' => 'Gaji Orang Lain']);

    $this->actingAs($user)
        ->get(route('transactions.index', ['type' => 'income', 'search' => 'Gaji']))
        ->assertOk()
        ->assertSee('Gaji Oktober')
        ->assertDontSee('Makan siang')
        ->assertDontSee('Gaji Orang Lain');
});

it('forbids access to another users transaction', function () {
    $owner = makeUser();
    $intruder = makeUser();
    $transaction = Transaction::factory()->for($owner)->create();
    $intruderAccount = FinancialAccount::factory()->for($intruder)->create();

    $this->actingAs($intruder)->get(route('transactions.show', $transaction))->assertForbidden();
    $this->actingAs($intruder)->get(route('transactions.edit', $transaction))->assertForbidden();
    $this->actingAs($intruder)->put(route('transactions.update', $transaction), [
        'type' => 'expense',
        'account_id' => $intruderAccount->id,
        'amount' => '1',
        'occurred_at' => now()->toDateTimeString(),
    ])->assertForbidden();
    $this->actingAs($intruder)->delete(route('transactions.destroy', $transaction))->assertForbidden();
});

it('renders the create and edit forms', function () {
    $user = makeUser();
    $account = FinancialAccount::factory()->for($user)->create();
    $transaction = Transaction::factory()->for($user)->for($account, 'account')->create();

    $this->actingAs($user)->get(route('transactions.create'))->assertOk()->assertSee('Catat Transaksi');
    $this->actingAs($user)->get(route('transactions.edit', $transaction))->assertOk()->assertSee('Edit Transaksi');
});
