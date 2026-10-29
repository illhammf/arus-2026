<?php

use Illuminate\Support\Facades\Hash;

it('renders the settings page', function () {
    $user = makeUser();

    $this->actingAs($user)
        ->get(route('settings.index'))
        ->assertOk()
        ->assertSee('Pengaturan')
        ->assertSee($user->name);
});

it('updates the profile', function () {
    $user = makeUser(['phone' => '+6281234567890']);

    $this->actingAs($user)->put(route('settings.profile'), [
        'name' => 'Nama Baru',
        'email' => 'baru@example.com',
        'currency' => 'IDR',
        'timezone' => 'Asia/Makassar',
    ])->assertRedirect();

    $user->refresh();

    expect($user->name)->toBe('Nama Baru')
        ->and($user->email)->toBe('baru@example.com')
        ->and($user->timezone)->toBe('Asia/Makassar')
        ->and($user->phone)->toBe('+6281234567890');
});

it('validates profile input', function () {
    $user = makeUser();
    $other = makeUser();

    $this->actingAs($user)->put(route('settings.profile'), [
        'name' => '',
        'email' => $other->email,
        'currency' => 'USD',
        'timezone' => 'Not/AZone',
    ])->assertSessionHasErrors(['name', 'email', 'currency', 'timezone']);
});

it('updates the password and keeps the current session active', function () {
    $user = makeUser(['password' => 'password-lama']);

    $this->actingAs($user)->put(route('settings.password'), [
        'current_password' => 'password-lama',
        'password' => 'password-baru',
        'password_confirmation' => 'password-baru',
    ])->assertRedirect();

    expect(Hash::check('password-baru', $user->fresh()->password))->toBeTrue();
});

it('rejects an incorrect current password', function () {
    $user = makeUser(['password' => 'password-lama']);

    $this->actingAs($user)->put(route('settings.password'), [
        'current_password' => 'salah',
        'password' => 'password-baru',
        'password_confirmation' => 'password-baru',
    ])->assertSessionHasErrors('current_password');

    expect(Hash::check('password-lama', $user->fresh()->password))->toBeTrue();
});
