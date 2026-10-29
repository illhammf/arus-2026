<?php

use App\Models\FinancialAccount;

it('lists accounts with balances', function () {
    $user = makeUser();
    $account = FinancialAccount::factory()->for($user)->create([
        'opening_balance' => '1500000.00',
    ]);

    $this->actingAs($user)
        ->get(route('accounts.index'))
        ->assertOk()
        ->assertSee($account->name)
        ->assertSee('Rp1.500.000');
});

it('creates an account and normalizes money input', function () {
    $user = makeUser();

    $response = $this->actingAs($user)->post(route('accounts.store'), [
        'name' => 'BCA Utama',
        'type' => 'bank',
        'currency' => 'idr',
        'opening_balance' => '2.500.000',
        'opening_balance_date' => '2026-10-01',
        'color' => '#0d9488',
        'notes' => 'Rekening gaji',
    ]);

    $account = FinancialAccount::query()->where('name', 'BCA Utama')->first();

    $response->assertRedirect(route('accounts.show', $account));

    expect($account)->not->toBeNull()
        ->and($account->user_id)->toBe($user->id)
        ->and($account->type->value)->toBe('bank')
        ->and($account->currency)->toBe('IDR')
        ->and($account->opening_balance)->toBe('2500000.00')
        ->and($account->balance())->toBe('2500000.00');
});

it('validates account input', function () {
    $user = makeUser();

    $this->actingAs($user)->post(route('accounts.store'), [
        'name' => '',
        'type' => 'invalid',
        'currency' => 'USD',
        'opening_balance' => '-1',
    ])->assertSessionHasErrors(['name', 'type', 'currency', 'opening_balance']);
});

it('prevents duplicate account names for the same user but allows other users', function () {
    $user = makeUser();
    FinancialAccount::factory()->for($user)->create(['name' => 'Dompet']);

    $this->actingAs($user)->post(route('accounts.store'), [
        'name' => 'Dompet',
        'type' => 'cash',
        'currency' => 'IDR',
        'opening_balance' => '0',
    ])->assertSessionHasErrors('name');

    $other = makeUser();

    $this->actingAs($other)->post(route('accounts.store'), [
        'name' => 'Dompet',
        'type' => 'cash',
        'currency' => 'IDR',
        'opening_balance' => '0',
    ])->assertSessionHasNoErrors();

    expect(FinancialAccount::query()->where('name', 'Dompet')->count())->toBe(2);
});

it('shows an account with incoming and outgoing totals', function () {
    $user = makeUser();
    $account = FinancialAccount::factory()->for($user)->create(['opening_balance' => '0']);

    \App\Models\Transaction::factory()->for($user)->for($account, 'account')->income()->create(['amount' => '500000']);
    \App\Models\Transaction::factory()->for($user)->for($account, 'account')->expense()->create(['amount' => '125000']);

    $this->actingAs($user)
        ->get(route('accounts.show', $account))
        ->assertOk()
        ->assertSee($account->name)
        ->assertSee('Rp375.000')
        ->assertSee('Rp500.000')
        ->assertSee('Rp125.000');
});

it('forbids access to another users account', function () {
    $owner = makeUser();
    $intruder = makeUser();
    $account = FinancialAccount::factory()->for($owner)->create();

    $this->actingAs($intruder)->get(route('accounts.show', $account))->assertForbidden();
    $this->actingAs($intruder)->get(route('accounts.edit', $account))->assertForbidden();
    $this->actingAs($intruder)->put(route('accounts.update', $account), [
        'name' => 'Hijack',
        'type' => 'cash',
        'currency' => 'IDR',
        'opening_balance' => '0',
    ])->assertForbidden();
    $this->actingAs($intruder)->delete(route('accounts.destroy', $account))->assertForbidden();
    $this->actingAs($intruder)->post(route('accounts.archive', $account))->assertForbidden();

    expect($account->fresh()->name)->not->toBe('Hijack');
});

it('archives and restores an account', function () {
    $user = makeUser();
    $account = FinancialAccount::factory()->for($user)->create(['is_archived' => false]);

    $this->actingAs($user)
        ->post(route('accounts.archive', $account))
        ->assertRedirect();

    expect($account->fresh()->is_archived)->toBeTrue();

    $this->actingAs($user)->post(route('accounts.archive', $account));

    expect($account->fresh()->is_archived)->toBeFalse();
});

it('does not delete an account that has transactions', function () {
    $user = makeUser();
    $account = FinancialAccount::factory()->for($user)->create();

    \App\Models\Transaction::factory()->for($user)->for($account, 'account')->create();

    $this->actingAs($user)
        ->delete(route('accounts.destroy', $account))
        ->assertRedirect()
        ->assertSessionHas('error');

    expect($account->fresh())->not->toBeNull();
});

it('deletes an empty account', function () {
    $user = makeUser();
    $account = FinancialAccount::factory()->for($user)->create();

    $this->actingAs($user)
        ->delete(route('accounts.destroy', $account))
        ->assertRedirect(route('accounts.index'));

    expect(FinancialAccount::query()->find($account->id))->toBeNull();
});
