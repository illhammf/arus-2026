<?php

use App\Enums\GoalStatus;
use App\Models\FinancialAccount;
use App\Models\SavingsContribution;
use App\Models\SavingsGoal;

it('creates a savings goal', function () {
    $user = makeUser();
    $account = FinancialAccount::factory()->for($user)->create();

    $response = $this->actingAs($user)->post(route('goals.store'), [
        'name' => 'Dana Darurat',
        'target_amount' => '10.000.000',
        'target_date' => now()->addMonths(6)->toDateString(),
        'linked_account_id' => $account->id,
        'notes' => 'Enam bulan pengeluaran',
    ]);

    $goal = SavingsGoal::query()->first();

    $response->assertRedirect(route('goals.show', $goal));

    expect($goal->user_id)->toBe($user->id)
        ->and($goal->name)->toBe('Dana Darurat')
        ->and($goal->target_amount)->toBe('10000000.00')
        ->and($goal->status)->toBe(GoalStatus::Active)
        ->and($goal->linked_account_id)->toBe($account->id)
        ->and($goal->contributedTotal())->toBe('0.00')
        ->and($goal->progressPercentage())->toBe(0.0);
});

it('validates the savings goal input', function () {
    $user = makeUser();
    $other = makeUser();
    $otherAccount = FinancialAccount::factory()->for($other)->create();

    $this->actingAs($user)->post(route('goals.store'), [
        'name' => '',
        'target_amount' => '0',
        'target_date' => now()->subDay()->toDateString(),
        'linked_account_id' => $otherAccount->id,
    ])->assertSessionHasErrors(['name', 'target_amount', 'target_date', 'linked_account_id']);
});

it('records contributions and marks the goal complete', function () {
    $user = makeUser();
    $goal = SavingsGoal::factory()->for($user)->create([
        'target_amount' => '1000000.00',
        'status' => GoalStatus::Active,
    ]);

    $this->actingAs($user)->post(route('goals.contributions.store', $goal), [
        'amount' => '400.000',
        'contributed_at' => '2026-10-01 10:00:00',
        'notes' => 'Sisihan pertama',
    ])->assertRedirect();

    expect($goal->contributedTotal())->toBe('400000.00')
        ->and($goal->progressPercentage())->toBe(40.0)
        ->and($goal->status)->toBe(GoalStatus::Active);

    $this->actingAs($user)->post(route('goals.contributions.store', $goal), [
        'amount' => '600.000',
        'contributed_at' => '2026-10-15 10:00:00',
    ])->assertRedirect();

    expect($goal->fresh()->contributedTotal())->toBe('1000000.00')
        ->and($goal->fresh()->status)->toBe(GoalStatus::Completed)
        ->and($goal->fresh()->progressPercentage())->toBe(100.0);
});

it('reverts the completed status when a contribution is deleted', function () {
    $user = makeUser();
    $goal = SavingsGoal::factory()->for($user)->create([
        'target_amount' => '1000000.00',
        'status' => GoalStatus::Completed,
    ]);

    $contribution = SavingsContribution::factory()->for($goal, 'goal')->for($user)->create([
        'amount' => '1000000.00',
        'contributed_at' => '2026-10-01 10:00:00',
    ]);

    $this->actingAs($user)
        ->delete(route('goals.contributions.destroy', [$goal, $contribution]))
        ->assertRedirect();

    expect(SavingsContribution::query()->find($contribution->id))->toBeNull()
        ->and($goal->fresh()->status)->toBe(GoalStatus::Active);
});

it('rejects a contribution that belongs to another goal', function () {
    $user = makeUser();
    $goal = SavingsGoal::factory()->for($user)->create();
    $otherGoal = SavingsGoal::factory()->for($user)->create();
    $contribution = SavingsContribution::factory()->for($otherGoal, 'goal')->for($user)->create();

    $this->actingAs($user)
        ->delete(route('goals.contributions.destroy', [$goal, $contribution]))
        ->assertNotFound();

    expect(SavingsContribution::query()->find($contribution->id))->not->toBeNull();
});

it('renders goal pages', function () {
    $user = makeUser();
    $goal = SavingsGoal::factory()->for($user)->create(['name' => 'Liburan Bali']);

    $this->actingAs($user)->get(route('goals.index'))->assertOk()->assertSee('Liburan Bali');
    $this->actingAs($user)->get(route('goals.create'))->assertOk()->assertSee('Buat Target');
    $this->actingAs($user)->get(route('goals.show', $goal))->assertOk()->assertSee('Liburan Bali');
    $this->actingAs($user)->get(route('goals.edit', $goal))->assertOk()->assertSee('Edit Target');
});

it('updates and deletes a goal', function () {
    $user = makeUser();
    $goal = SavingsGoal::factory()->for($user)->create([
        'target_amount' => '1000000.00',
        'status' => GoalStatus::Active,
    ]);

    $this->actingAs($user)->put(route('goals.update', $goal), [
        'name' => 'Liburan Jepang',
        'target_amount' => '15.000.000',
        'target_date' => now()->addYear()->toDateString(),
        'status' => 'active',
    ])->assertRedirect(route('goals.show', $goal));

    expect($goal->fresh()->name)->toBe('Liburan Jepang')
        ->and($goal->fresh()->target_amount)->toBe('15000000.00');

    $this->actingAs($user)->delete(route('goals.destroy', $goal))->assertRedirect(route('goals.index'));

    expect(SavingsGoal::query()->find($goal->id))->toBeNull();
});

it('forbids access to another users goal and contributions', function () {
    $owner = makeUser();
    $intruder = makeUser();
    $goal = SavingsGoal::factory()->for($owner)->create();
    $contribution = SavingsContribution::factory()->for($goal, 'goal')->for($owner)->create();

    $this->actingAs($intruder)->get(route('goals.show', $goal))->assertForbidden();
    $this->actingAs($intruder)->get(route('goals.edit', $goal))->assertForbidden();
    $this->actingAs($intruder)->put(route('goals.update', $goal), [
        'name' => 'Hijack',
        'target_amount' => '1',
    ])->assertForbidden();
    $this->actingAs($intruder)->delete(route('goals.destroy', $goal))->assertForbidden();

    $this->actingAs($intruder)->post(route('goals.contributions.store', $goal), [
        'amount' => '10000',
        'contributed_at' => now()->toDateTimeString(),
    ])->assertForbidden();

    $this->actingAs($intruder)->delete(route('goals.contributions.destroy', [$goal, $contribution]))->assertForbidden();

    expect(SavingsGoal::query()->find($goal->id))->not->toBeNull()
        ->and(SavingsContribution::query()->find($contribution->id))->not->toBeNull();
});
