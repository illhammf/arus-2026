<?php

use App\Models\FinancialAccount;
use App\Models\Transaction;

it('renders the dashboard with owner-scoped totals', function () {
    $user = makeUser();
    $other = makeUser();
    $account = FinancialAccount::factory()->for($user)->create(['opening_balance' => '1000000.00']);
    $otherAccount = FinancialAccount::factory()->for($other)->create(['opening_balance' => '9000000.00']);

    Transaction::factory()->for($user)->for($account, 'account')->income()->create([
        'amount' => '500000.00',
        'occurred_at' => now()->startOfMonth()->addDay(),
    ]);

    Transaction::factory()->for($other)->for($otherAccount, 'account')->income()->create([
        'amount' => '900000.00',
        'occurred_at' => now()->startOfMonth()->addDay(),
    ]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Rp1.500.000')
        ->assertSee('Rp500.000')
        ->assertDontSee('Rp9.900.000');
});

it('renders the dashboard for a previous month', function () {
    $user = makeUser();

    $this->actingAs($user)
        ->get(route('dashboard', ['month' => '2026-09']))
        ->assertOk()
        ->assertSee('September 2026');
});

it('renders the report page', function () {
    $user = makeUser();
    $account = FinancialAccount::factory()->for($user)->create();
    $category = \App\Models\Category::factory()->create(['name' => 'Makan & Minum']);

    Transaction::factory()->for($user)->for($account, 'account')->expense()->create([
        'category_id' => $category->id,
        'amount' => '150000.00',
        'occurred_at' => '2026-10-10 12:00:00',
    ]);

    $this->actingAs($user)
        ->get(route('reports.index', ['month' => '2026-10']))
        ->assertOk()
        ->assertSee('Laporan Keuangan')
        ->assertSee('Makan & Minum')
        ->assertSee('Rp150.000');
});

it('exports only the owners transactions as csv', function () {
    $user = makeUser();
    $other = makeUser();
    $account = FinancialAccount::factory()->for($user)->create();
    $otherAccount = FinancialAccount::factory()->for($other)->create();

    Transaction::factory()->for($user)->for($account, 'account')->expense()->create([
        'description' => 'Belanja Saya',
        'amount' => '150000.00',
        'occurred_at' => '2026-10-10 12:00:00',
    ]);

    Transaction::factory()->for($other)->for($otherAccount, 'account')->expense()->create([
        'description' => 'Belanja Orang Lain',
        'amount' => '999000.00',
        'occurred_at' => '2026-10-11 12:00:00',
    ]);

    $response = $this->actingAs($user)->get(route('reports.export', ['month' => '2026-10']));

    $response->assertOk();
    $response->assertHeader('content-type', 'text/csv; charset=UTF-8');

    $csv = $response->streamedContent();

    expect($csv)->toContain('Tanggal')
        ->toContain('Belanja Saya')
        ->toContain('150000.00')
        ->not->toContain('Belanja Orang Lain');
});
