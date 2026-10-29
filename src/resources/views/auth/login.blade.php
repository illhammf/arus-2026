@extends('layouts.guest')

@section('title', 'Masuk')
@section('eyebrow', 'Selamat datang kembali')
@section('heading', 'Masuk ke Arus')
@section('description', 'Gunakan nomor HP dan password yang kamu daftarkan.')

@section('content')
    <form method="POST" action="{{ route('login.store') }}" class="space-y-5">
        @csrf

        <div>
            <label for="phone" class="label">Nomor HP</label>
            <div class="relative">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-sm font-semibold text-slate-400">+62</span>
                <input id="phone" name="phone" type="tel" inputmode="numeric" autocomplete="tel" required autofocus
                       value="{{ old('phone') ? \App\Support\PhoneNormalizer::display(old('phone')) : '' }}"
                       placeholder="812 3456 7890"
                       class="input pl-12 @error('phone') input-error @enderror">
            </div>
            @error('phone')
                <p class="error-text"><x-heroicon-o-exclamation-circle class="mt-px h-3.5 w-3.5 shrink-0" />{{ $message }}</p>
            @enderror
        </div>

        <div>
            <div class="mb-1.5 flex items-center justify-between">
                <label for="password" class="label mb-0">Password</label>
                <span class="text-[11px] text-slate-400">Lupa password? Hubungi admin</span>
            </div>
            <div class="relative">
                <input id="password" name="password" type="password" autocomplete="current-password" required
                       placeholder="Masukkan password"
                       class="input pr-11 @error('password') input-error @enderror">
                <button type="button" data-password-toggle="#password" class="absolute inset-y-0 right-0 flex w-11 items-center justify-center text-slate-400 hover:text-slate-600" aria-label="Tampilkan password">
                    <x-heroicon-o-eye class="h-5 w-5" data-eye-open />
                    <x-heroicon-o-eye-slash class="hidden h-5 w-5" data-eye-off />
                </button>
            </div>
            @error('password')
                <p class="error-text"><x-heroicon-o-exclamation-circle class="mt-px h-3.5 w-3.5 shrink-0" />{{ $message }}</p>
            @enderror
        </div>

        <label class="flex cursor-pointer items-center gap-2.5 text-sm text-slate-600">
            <input type="checkbox" name="remember" value="1" @checked(old('remember'))
                   class="h-4 w-4 rounded border-slate-300 text-teal-600 focus:ring-teal-500">
            Ingat saya di perangkat ini
        </label>

        <button type="submit" class="btn-primary w-full py-3">
            Masuk
            <x-heroicon-o-arrow-right class="h-4 w-4" />
        </button>

        <p class="text-center text-sm text-slate-500">
            Belum punya akun?
            <a href="{{ route('register') }}" class="link">Daftar gratis</a>
        </p>
    </form>
@endsection
