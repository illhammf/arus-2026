<?php

use App\Models\FinancialAccount;
use App\Models\SavingsGoal;
use App\Models\Transaction;
use App\Models\User;

it('lets a super admin open the admin dashboard', function () {
    $admin = makeSuperAdmin();

    $this->actingAs($admin)
        ->get('/admin')
        ->assertOk()
        ->assertSee('Arus');
});

it('lets a super admin open every resource list and create page', function () {
    $admin = makeSuperAdmin();
    $user = makeUser();
    $account = FinancialAccount::factory()->for($user)->create();
    Transaction::factory()->for($user)->for($account, 'account')->create();
    SavingsGoal::factory()->for($user)->create();

    $paths = [
        '/admin/users',
        '/admin/users/create',
        '/admin/financial-accounts',
        '/admin/financial-accounts/create',
        '/admin/categories',
        '/admin/categories/create',
        '/admin/transactions',
        '/admin/transactions/create',
        '/admin/budgets',
        '/admin/budgets/create',
        '/admin/savings-goals',
        '/admin/savings-goals/create',
    ];

    foreach ($paths as $path) {
        $this->actingAs($admin)
            ->get($path)
            ->assertSuccessful();
    }
});

it('lets a super admin open edit pages for existing records', function () {
    $admin = makeSuperAdmin();
    $user = makeUser();
    $account = FinancialAccount::factory()->for($user)->create();
    $transaction = Transaction::factory()->for($user)->for($account, 'account')->create();
    $goal = SavingsGoal::factory()->for($user)->create();

    $this->actingAs($admin)->get('/admin/users/' . $user->id . '/edit')->assertSuccessful();
    $this->actingAs($admin)->get('/admin/financial-accounts/' . $account->id . '/edit')->assertSuccessful();
    $this->actingAs($admin)->get('/admin/transactions/' . $transaction->id . '/edit')->assertSuccessful();
    $this->actingAs($admin)->get('/admin/savings-goals/' . $goal->id . '/edit')->assertSuccessful();
});

it('does not let a regular user access the admin panel', function () {
    $user = makeUser();

    $this->actingAs($user)->get('/admin')->assertForbidden();
});

it('redirects guests to the admin login page', function () {
    $this->get('/admin')->assertRedirect('/admin/login');
});

it('keeps the admin panel limited to active super admins', function () {
    \Spatie\Permission\Models\Role::findOrCreate('super_admin', 'web');

    $inactive = User::factory()->create(['status' => \App\Enums\UserStatus::Suspended]);
    $inactive->assignRole('super_admin');

    expect($inactive->canAccessPanel(\Filament\Facades\Filament::getPanel('admin')))->toBeFalse();
});
