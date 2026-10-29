<?php

use App\Models\Budget;
use App\Models\Category;
use App\Models\FinancialAccount;
use App\Models\Transaction;

it('creates a monthly budget and derives the period dates', function () {
    $user = makeUser();

    $this->actingAs($user)->post(route('budgets.store'), [
        'category_id' => null,
        'period_type' => 'monthly',
        'period' => '2026-10',
        'amount' => '1.500.000',
        'notes' => 'Anggaran Oktober',
    ])->assertRedirect(route('budgets.index'));

    $budget = Budget::query()->first();

    expect($budget->user_id)->toBe($user->id)
        ->and($budget->amount)->toBe('1500000.00')
        ->and($budget->period_start->toDateString())->toBe('2026-10-01')
        ->and($budget->period_end->toDateString())->toBe('2026-10-31')
        ->and($budget->notes)->toBe('Anggaran Oktober');
});

it('creates a yearly budget', function () {
    $user = makeUser();

    $this->actingAs($user)->post(route('budgets.store'), [
        'period_type' => 'yearly',
        'period' => '2026',
        'amount' => '12.000.000',
    ])->assertSessionHasNoErrors();

    $budget = Budget::query()->first();

    expect($budget->period_start->toDateString())->toBe('2026-01-01')
        ->and($budget->period_end->toDateString())->toBe('2026-12-31');
});

it('validates the budget period format and category type', function () {
    $user = makeUser();
    $incomeCategory = Category::factory()->income()->create();

    $this->actingAs($user)->post(route('budgets.store'), [
        'period_type' => 'monthly',
        'period' => '2026-13',
        'amount' => '1000000',
    ])->assertSessionHasErrors('period');

    $this->actingAs($user)->post(route('budgets.store'), [
        'category_id' => $incomeCategory->id,
        'period_type' => 'monthly',
        'period' => '2026-10',
        'amount' => '1000000',
    ])->assertSessionHasErrors('category_id');
});

it('calculates spending for the period and owner only', function () {
    $user = makeUser();
    $other = makeUser();
    $account = FinancialAccount::factory()->for($user)->create();
    $otherAccount = FinancialAccount::factory()->for($other)->create();

    $budget = Budget::factory()->for($user)->create([
        'period_type' => 'monthly',
        'period_start' => '2026-10-01',
        'period_end' => '2026-10-31',
        'amount' => '1000000.00',
    ]);

    Transaction::factory()->for($user)->for($account, 'account')->expense()->create([
        'amount' => '300000.00',
        'occurred_at' => '2026-10-15 12:00:00',
    ]);

    Transaction::factory()->for($user)->for($account, 'account')->expense()->create([
        'amount' => '100000.00',
        'occurred_at' => '2026-09-15 12:00:00',
    ]);

    Transaction::factory()->for($user)->for($account, 'account')->income()->create([
        'amount' => '500000.00',
        'occurred_at' => '2026-10-16 12:00:00',
    ]);

    Transaction::factory()->for($other)->for($otherAccount, 'account')->expense()->create([
        'amount' => '900000.00',
        'occurred_at' => '2026-10-17 12:00:00',
    ]);

    expect($budget->spent())->toBe('300000.00')
        ->and($budget->remaining())->toBe('700000.00')
        ->and($budget->usagePercentage())->toBe(30.0)
        ->and($budget->isOverBudget())->toBeFalse();
});

it('scopes spending to a category when set', function () {
    $user = makeUser();
    $account = FinancialAccount::factory()->for($user)->create();
    $food = Category::factory()->create(['name' => 'Makan']);
    $transport = Category::factory()->create(['name' => 'Transport']);

    $budget = Budget::factory()->for($user)->for($food, 'category')->create([
        'period_start' => '2026-10-01',
        'period_end' => '2026-10-31',
        'amount' => '500000.00',
    ]);

    Transaction::factory()->for($user)->for($account, 'account')->expense()->create([
        'category_id' => $food->id,
        'amount' => '200000.00',
        'occurred_at' => '2026-10-10 12:00:00',
    ]);

    Transaction::factory()->for($user)->for($account, 'account')->expense()->create([
        'category_id' => $transport->id,
        'amount' => '150000.00',
        'occurred_at' => '2026-10-11 12:00:00',
    ]);

    expect($budget->spent())->toBe('200000.00');
});

it('detects overspending', function () {
    $user = makeUser();
    $account = FinancialAccount::factory()->for($user)->create();

    $budget = Budget::factory()->for($user)->create([
        'period_start' => '2026-10-01',
        'period_end' => '2026-10-31',
        'amount' => '500000.00',
    ]);

    Transaction::factory()->for($user)->for($account, 'account')->expense()->create([
        'amount' => '750000.00',
        'occurred_at' => '2026-10-10 12:00:00',
    ]);

    expect($budget->spent())->toBe('750000.00')
        ->and($budget->remaining())->toBe('-250000.00')
        ->and($budget->usagePercentage())->toBe(100.0)
        ->and($budget->isOverBudget())->toBeTrue();
});

it('renders the budget index and forms', function () {
    $user = makeUser();
    $budget = Budget::factory()->for($user)->create([
        'period_start' => '2026-10-01',
        'period_end' => '2026-10-31',
        'amount' => '1000000.00',
    ]);

    $this->actingAs($user)->get(route('budgets.index'))->assertOk()->assertSee('Rp1.000.000');
    $this->actingAs($user)->get(route('budgets.create'))->assertOk()->assertSee('Buat Anggaran');
    $this->actingAs($user)->get(route('budgets.edit', $budget))->assertOk()->assertSee('Edit Anggaran');
});

it('updates and deletes a budget', function () {
    $user = makeUser();
    $budget = Budget::factory()->for($user)->create([
        'period_start' => '2026-10-01',
        'period_end' => '2026-10-31',
        'amount' => '1000000.00',
    ]);

    $this->actingAs($user)->put(route('budgets.update', $budget), [
        'period_type' => 'monthly',
        'period' => '2026-11',
        'amount' => '2.000.000',
    ])->assertRedirect(route('budgets.index'));

    expect($budget->fresh()->amount)->toBe('2000000.00')
        ->and($budget->fresh()->period_start->toDateString())->toBe('2026-11-01');

    $this->actingAs($user)->delete(route('budgets.destroy', $budget))->assertRedirect(route('budgets.index'));

    expect(Budget::query()->find($budget->id))->toBeNull();
});

it('forbids access to another users budget', function () {
    $owner = makeUser();
    $intruder = makeUser();
    $budget = Budget::factory()->for($owner)->create();

    $this->actingAs($intruder)->get(route('budgets.edit', $budget))->assertForbidden();
    $this->actingAs($intruder)->put(route('budgets.update', $budget), [
        'period_type' => 'monthly',
        'period' => '2026-10',
        'amount' => '1',
    ])->assertForbidden();
    $this->actingAs($intruder)->delete(route('budgets.destroy', $budget))->assertForbidden();
});
