<?php

use App\Models\FinancialAccount;
use App\Models\User;

it('renders the public pages', function () {
    $this->get('/')->assertOk()->assertSee('Arus');
    $this->get('/login')->assertOk()->assertSee('Masuk ke Arus');
    $this->get('/register')->assertOk()->assertSee('Buat akun Arus');
});

it('registers a user with a normalized phone, default role and wallet', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'Rani Putri',
        'phone' => '0812-3456-7890',
        'email' => 'Rani@Example.com',
        'password' => 'rahasia123',
        'password_confirmation' => 'rahasia123',
        'terms' => '1',
    ]);

    $response->assertRedirect(route('dashboard'));
    $this->assertAuthenticated();

    $user = User::query()->where('phone', '+6281234567890')->first();

    expect($user)->not->toBeNull()
        ->and($user->name)->toBe('Rani Putri')
        ->and($user->email)->toBe('rani@example.com')
        ->and($user->currency)->toBe('IDR')
        ->and($user->timezone)->toBe('Asia/Jakarta')
        ->and($user->hasRole('user'))->toBeTrue()
        ->and($user->financialAccounts()->count())->toBe(1);

    $wallet = FinancialAccount::query()->where('user_id', $user->id)->first();

    expect($wallet->name)->toBe('Dompet Utama')
        ->and($wallet->balance())->toBe('0.00');
});

it('rejects invalid or duplicate registration data', function () {
    $existing = makeUser(['phone' => '+628111111111']);

    $this->post(route('register.store'), [
        'name' => 'X',
        'phone' => '12345',
        'password' => 'short',
        'password_confirmation' => 'different',
    ])->assertSessionHasErrors(['name', 'phone', 'password', 'terms']);

    $this->post(route('register.store'), [
        'name' => 'Budi',
        'phone' => '08111111111',
        'email' => $existing->email,
        'password' => 'rahasia123',
        'password_confirmation' => 'rahasia123',
        'terms' => '1',
    ])->assertSessionHasErrors(['phone', 'email']);
});

it('logs in using any common phone format', function () {
    $user = makeUser(['phone' => '+6281234567890', 'password' => 'rahasia123']);

    $this->post(route('login.store'), [
        'phone' => '0812 3456 7890',
        'password' => 'rahasia123',
    ])->assertRedirect(route('dashboard'));

    $this->assertAuthenticatedAs($user);
    expect($user->fresh()->last_login_at)->not->toBeNull();
});

it('rejects wrong passwords and inactive accounts', function () {
    $user = makeUser(['phone' => '+6281234567890', 'password' => 'rahasia123']);

    $this->post(route('login.store'), [
        'phone' => '081234567890',
        'password' => 'salah-password',
    ])->assertSessionHasErrors('phone');

    $this->assertGuest();

    $inactive = makeUser([
        'phone' => '+628999999999',
        'password' => 'rahasia123',
        'status' => \App\Enums\UserStatus::Suspended,
    ]);

    $this->post(route('login.store'), [
        'phone' => '08999999999',
        'password' => 'rahasia123',
    ])->assertSessionHasErrors('phone');

    $this->assertGuest();
});

it('logs out an authenticated user', function () {
    $user = makeUser();

    $this->actingAs($user)
        ->post(route('logout'))
        ->assertRedirect(route('home'));

    $this->assertGuest();
});

it('redirects authenticated users away from guest pages', function () {
    $user = makeUser();

    $this->actingAs($user)->get(route('login'))->assertRedirect(route('dashboard'));
    $this->actingAs($user)->get(route('register'))->assertRedirect(route('dashboard'));
});

it('requires authentication for private pages', function () {
    $this->get(route('dashboard'))->assertRedirect(route('login'));
    $this->get(route('accounts.index'))->assertRedirect(route('login'));
    $this->get(route('transactions.index'))->assertRedirect(route('login'));
});
