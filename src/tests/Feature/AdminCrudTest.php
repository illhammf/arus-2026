<?php

use App\Filament\Admin\Resources\BudgetResource\Pages\CreateBudget;
use App\Filament\Admin\Resources\CategoryResource\Pages\CreateCategory;
use App\Filament\Admin\Resources\FinancialAccountResource\Pages\CreateFinancialAccount;
use App\Filament\Admin\Resources\SavingsGoalResource\Pages\CreateSavingsGoal;
use App\Filament\Admin\Resources\SavingsGoalResource\Pages\EditSavingsGoal;
use App\Filament\Admin\Resources\SavingsGoalResource\RelationManagers\ContributionsRelationManager;
use App\Filament\Admin\Resources\TransactionResource\Pages\CreateTransaction;
use App\Filament\Admin\Resources\UserResource\Pages\CreateUser;
use App\Models\Budget;
use App\Models\Category;
use App\Models\FinancialAccount;
use App\Models\SavingsGoal;
use App\Models\Transaction;
use App\Models\User;
use Filament\Facades\Filament;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->admin = makeSuperAdmin();
    $this->actingAs($this->admin);
    Filament::setCurrentPanel(Filament::getPanel('admin'));
});

it('creates a user from the admin panel', function () {
    Livewire::test(CreateUser::class)
        ->fillForm([
            'name' => 'Pengguna Baru',
            'phone' => '081234567891',
            'email' => 'baru@example.com',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
            'status' => 'active',
            'currency' => 'IDR',
            'timezone' => 'Asia/Jakarta',
            'roles' => [Role::findOrCreate('user', 'web')->id],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(User::query()->where('phone', '+6281234567891')->exists())->toBeTrue();
});

it('creates a financial account from the admin panel', function () {
    $owner = makeUser();

    Livewire::test(CreateFinancialAccount::class)
        ->fillForm([
            'user_id' => $owner->id,
            'name' => 'BCA Admin',
            'type' => 'bank',
            'currency' => 'IDR',
            'opening_balance' => 1500000,
            'opening_balance_date' => '2026-10-01',
            'color' => '#0d9488',
            'is_archived' => false,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $account = FinancialAccount::query()->where('name', 'BCA Admin')->first();

    expect($account)->not->toBeNull()
        ->and($account->user_id)->toBe($owner->id)
        ->and($account->opening_balance)->toBe('1500000.00');
});

it('creates a category from the admin panel', function () {
    $owner = makeUser();

    Livewire::test(CreateCategory::class)
        ->fillForm([
            'name' => 'Kategori Admin',
            'slug' => 'kategori-admin',
            'type' => 'expense',
            'user_id' => $owner->id,
            'icon' => 'shopping-bag',
            'color' => '#0d9488',
            'is_system' => false,
            'is_active' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Category::query()->where('slug', 'kategori-admin')->exists())->toBeTrue();
});

it('creates a budget from the admin panel', function () {
    $owner = makeUser();

    Livewire::test(CreateBudget::class)
        ->fillForm([
            'user_id' => $owner->id,
            'period_type' => 'monthly',
            'period_start' => '2026-10-01',
            'period_end' => '2026-10-31',
            'amount' => 1000000,
            'notes' => 'Anggaran admin',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $budget = Budget::query()->first();

    expect($budget)->not->toBeNull()
        ->and($budget->amount)->toBe('1000000.00');
});

it('creates a transaction from the admin panel', function () {
    $owner = makeUser();
    $account = FinancialAccount::factory()->for($owner)->create();

    Livewire::test(CreateTransaction::class)
        ->fillForm([
            'user_id' => $owner->id,
            'account_id' => $account->id,
            'type' => 'expense',
            'status' => 'posted',
            'amount' => 250000,
            'occurred_at' => '2026-10-29 12:00:00',
            'description' => 'Pengeluaran admin',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $transaction = Transaction::query()->first();

    expect($transaction)->not->toBeNull()
        ->and($transaction->user_id)->toBe($owner->id)
        ->and($transaction->amount)->toBe('250000.00');
});

it('creates a savings goal and a contribution from the admin panel', function () {
    $owner = makeUser();
    $account = FinancialAccount::factory()->for($owner)->create();

    Livewire::test(CreateSavingsGoal::class)
        ->fillForm([
            'user_id' => $owner->id,
            'name' => 'Target Admin',
            'target_amount' => 5000000,
            'target_date' => '2027-01-01',
            'status' => 'active',
            'linked_account_id' => $account->id,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $goal = SavingsGoal::query()->first();
    expect($goal)->not->toBeNull();

    Livewire::test(ContributionsRelationManager::class, [
        'ownerRecord' => $goal,
        'pageClass' => EditSavingsGoal::class,
    ])
        ->callTableAction('create', data: [
            'amount' => 500000,
            'contributed_at' => '2026-10-29 10:00:00',
            'notes' => 'Kontribusi admin',
        ])
        ->assertHasNoTableActionErrors();

    expect($goal->fresh()->contributedTotal())->toBe('500000.00');
});
