<?php

use App\Models\FinancialAccount;
use App\Models\Transaction;

it('creates a paired transfer and keeps total wealth unchanged', function () {
    $user = makeUser();
    $source = FinancialAccount::factory()->for($user)->create(['opening_balance' => '1000000.00']);
    $destination = FinancialAccount::factory()->for($user)->create(['opening_balance' => '500000.00']);

    $response = $this->actingAs($user)->post(route('transfers.store'), [
        'source_account_id' => $source->id,
        'destination_account_id' => $destination->id,
        'amount' => '250.000',
        'occurred_at' => '2026-10-29 10:00:00',
        'description' => 'Pindah ke tabungan',
        'notes' => 'Rutin bulanan',
    ]);

    $rows = Transaction::query()->orderBy('id')->get();
    $out = $rows->firstWhere('type.value', 'transfer_out');
    $in = $rows->firstWhere('type.value', 'transfer_in');

    $response->assertRedirect(route('transactions.show', $out));

    expect($rows)->toHaveCount(2)
        ->and($out->transfer_group_id)->not->toBeNull()
        ->and($in->transfer_group_id)->toBe($out->transfer_group_id)
        ->and($out->account_id)->toBe($source->id)
        ->and($in->account_id)->toBe($destination->id)
        ->and($out->amount)->toBe('250000.00')
        ->and($in->amount)->toBe('250000.00')
        ->and($out->description)->toBe('Pindah ke tabungan')
        ->and($source->fresh()->balance())->toBe('750000.00')
        ->and($destination->fresh()->balance())->toBe('750000.00')
        ->and(\App\Support\Money::add($source->fresh()->balance(), $destination->fresh()->balance()))->toBe('1500000.00');
});

it('generates a default transfer description', function () {
    $user = makeUser();
    $source = FinancialAccount::factory()->for($user)->create(['name' => 'BCA']);
    $destination = FinancialAccount::factory()->for($user)->create(['name' => 'Dompet']);

    $this->actingAs($user)->post(route('transfers.store'), [
        'source_account_id' => $source->id,
        'destination_account_id' => $destination->id,
        'amount' => '10000',
        'occurred_at' => now()->toDateTimeString(),
    ])->assertSessionHasNoErrors();

    expect(Transaction::query()->where('type', 'transfer_out')->first()->description)->toBe('Transfer BCA ke Dompet');
});

it('rejects transfers to the same account or another users account', function () {
    $user = makeUser();
    $intruder = makeUser();
    $source = FinancialAccount::factory()->for($user)->create();
    $other = FinancialAccount::factory()->for($intruder)->create();

    $this->actingAs($user)->post(route('transfers.store'), [
        'source_account_id' => $source->id,
        'destination_account_id' => $source->id,
        'amount' => '10000',
        'occurred_at' => now()->toDateTimeString(),
    ])->assertSessionHasErrors('destination_account_id');

    $this->actingAs($user)->post(route('transfers.store'), [
        'source_account_id' => $source->id,
        'destination_account_id' => $other->id,
        'amount' => '10000',
        'occurred_at' => now()->toDateTimeString(),
    ])->assertSessionHasErrors('destination_account_id');

    expect(Transaction::query()->count())->toBe(0);
});

it('shows both sides of a transfer', function () {
    $user = makeUser();
    $source = FinancialAccount::factory()->for($user)->create();
    $destination = FinancialAccount::factory()->for($user)->create();

    $this->actingAs($user)->post(route('transfers.store'), [
        'source_account_id' => $source->id,
        'destination_account_id' => $destination->id,
        'amount' => '50000',
        'occurred_at' => now()->toDateTimeString(),
    ]);

    $out = Transaction::query()->where('type', 'transfer_out')->first();
    $in = Transaction::query()->where('type', 'transfer_in')->first();

    $this->actingAs($user)
        ->get(route('transactions.show', $out))
        ->assertOk()
        ->assertSee('Pasangan transfer')
        ->assertSee($destination->name);

    $this->actingAs($user)
        ->get(route('transactions.show', $in))
        ->assertOk()
        ->assertSee($source->name);
});

it('renders and updates the transfer edit form', function () {
    $user = makeUser();
    $source = FinancialAccount::factory()->for($user)->create();
    $destination = FinancialAccount::factory()->for($user)->create();
    $third = FinancialAccount::factory()->for($user)->create();

    $this->actingAs($user)->post(route('transfers.store'), [
        'source_account_id' => $source->id,
        'destination_account_id' => $destination->id,
        'amount' => '50000',
        'occurred_at' => '2026-10-29 10:00:00',
        'description' => 'Transfer awal',
    ]);

    $out = Transaction::query()->where('type', 'transfer_out')->first();

    $this->actingAs($user)
        ->get(route('transfers.edit', $out))
        ->assertOk()
        ->assertSee('Edit Transfer')
        ->assertSee('Transfer awal');

    $this->actingAs($user)->put(route('transfers.update', $out), [
        'source_account_id' => $third->id,
        'destination_account_id' => $destination->id,
        'amount' => '75.000',
        'occurred_at' => '2026-10-30 11:00:00',
        'description' => 'Transfer revisi',
    ])->assertRedirect(route('transactions.show', $out));

    $rows = Transaction::query()->where('transfer_group_id', $out->transfer_group_id)->get();
    $updatedOut = $rows->firstWhere('type.value', 'transfer_out');
    $updatedIn = $rows->firstWhere('type.value', 'transfer_in');

    expect($updatedOut->account_id)->toBe($third->id)
        ->and($updatedIn->account_id)->toBe($destination->id)
        ->and($updatedOut->amount)->toBe('75000.00')
        ->and($updatedIn->amount)->toBe('75000.00')
        ->and($updatedOut->description)->toBe('Transfer revisi')
        ->and($updatedIn->description)->toBe('Transfer revisi');
});

it('deletes both rows when either side of a transfer is removed', function () {
    $user = makeUser();
    $source = FinancialAccount::factory()->for($user)->create(['opening_balance' => '1000000.00']);
    $destination = FinancialAccount::factory()->for($user)->create(['opening_balance' => '0.00']);

    $this->actingAs($user)->post(route('transfers.store'), [
        'source_account_id' => $source->id,
        'destination_account_id' => $destination->id,
        'amount' => '250000',
        'occurred_at' => now()->toDateTimeString(),
    ]);

    $in = Transaction::query()->where('type', 'transfer_in')->first();

    $this->actingAs($user)
        ->delete(route('transactions.destroy', $in))
        ->assertRedirect(route('transactions.index'));

    expect(Transaction::query()->count())->toBe(0)
        ->and($source->fresh()->balance())->toBe('1000000.00')
        ->and($destination->fresh()->balance())->toBe('0.00');
});

it('forbids editing another users transfer', function () {
    $owner = makeUser();
    $intruder = makeUser();
    $source = FinancialAccount::factory()->for($owner)->create();
    $destination = FinancialAccount::factory()->for($owner)->create();

    $this->actingAs($owner)->post(route('transfers.store'), [
        'source_account_id' => $source->id,
        'destination_account_id' => $destination->id,
        'amount' => '10000',
        'occurred_at' => now()->toDateTimeString(),
    ]);

    $out = Transaction::query()->where('type', 'transfer_out')->first();

    $this->actingAs($intruder)->get(route('transfers.edit', $out))->assertForbidden();
    $this->actingAs($intruder)->put(route('transfers.update', $out), [
        'source_account_id' => $source->id,
        'destination_account_id' => $destination->id,
        'amount' => '1',
        'occurred_at' => now()->toDateTimeString(),
    ])->assertForbidden();
});

it('renders transfer forms safely when fewer than two accounts exist', function () {
    $user = makeUser();
    FinancialAccount::factory()->for($user)->create();

    $this->actingAs($user)
        ->get(route('transfers.create'))
        ->assertOk()
        ->assertSee('minimal dua akun');
});
