@extends('layouts.guest')

@section('title', 'Daftar')
@section('eyebrow', 'Mulai perjalananmu')
@section('heading', 'Buat akun Arus')
@section('description', 'Cukup beberapa data. Dompet utamamu akan dibuat otomatis.')

@section('content')
    <form method="POST" action="{{ route('register.store') }}" class="space-y-4">
        @csrf

        <div>
            <label for="name" class="label">Nama lengkap</label>
            <input id="name" name="name" type="text" autocomplete="name" required autofocus
                   value="{{ old('name') }}" placeholder="Contoh: Rani Putri"
                   class="input @error('name') input-error @enderror">
            @error('name')
                <p class="error-text"><x-heroicon-o-exclamation-circle class="mt-px h-3.5 w-3.5 shrink-0" />{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="phone" class="label">Nomor HP</label>
            <div class="relative">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-sm font-semibold text-slate-400">+62</span>
                <input id="phone" name="phone" type="tel" inputmode="numeric" autocomplete="tel" required
                       value="{{ old('phone') ? \App\Support\PhoneNormalizer::display(old('phone')) : '' }}"
                       placeholder="812 3456 7890"
                       class="input pl-12 @error('phone') input-error @enderror">
            </div>
            <p class="hint">Nomor ini akan menjadi identitas masuk kamu.</p>
            @error('phone')
                <p class="error-text"><x-heroicon-o-exclamation-circle class="mt-px h-3.5 w-3.5 shrink-0" />{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="email" class="label">Email <span class="font-normal text-slate-400">(opsional)</span></label>
            <input id="email" name="email" type="email" autocomplete="email"
                   value="{{ old('email') }}" placeholder="nama@email.com"
                   class="input @error('email') input-error @enderror">
            <p class="hint">Berguna untuk pemulihan akun di masa mendatang.</p>
            @error('email')
                <p class="error-text"><x-heroicon-o-exclamation-circle class="mt-px h-3.5 w-3.5 shrink-0" />{{ $message }}</p>
            @enderror
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="password" class="label">Password</label>
                <div class="relative">
                    <input id="password" name="password" type="password" autocomplete="new-password" required
                           placeholder="Minimal 8 karakter"
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
            <div>
                <label for="password_confirmation" class="label">Ulangi password</label>
                <div class="relative">
                    <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required
                           placeholder="Ketik ulang password"
                           class="input pr-11">
                    <button type="button" data-password-toggle="#password_confirmation" class="absolute inset-y-0 right-0 flex w-11 items-center justify-center text-slate-400 hover:text-slate-600" aria-label="Tampilkan password">
                        <x-heroicon-o-eye class="h-5 w-5" data-eye-open />
                        <x-heroicon-o-eye-slash class="hidden h-5 w-5" data-eye-off />
                    </button>
                </div>
            </div>
        </div>

        <div>
            <label class="flex cursor-pointer items-start gap-2.5 text-sm text-slate-600">
                <input type="checkbox" name="terms" value="1" required @checked(old('terms'))
                       class="mt-0.5 h-4 w-4 rounded border-slate-300 text-teal-600 focus:ring-teal-500">
                <span>Saya menyetujui syarat penggunaan dan kebijakan privasi Arus.</span>
            </label>
            @error('terms')
                <p class="error-text"><x-heroicon-o-exclamation-circle class="mt-px h-3.5 w-3.5 shrink-0" />{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="btn-primary w-full py-3">
            Buat akun
            <x-heroicon-o-arrow-right class="h-4 w-4" />
        </button>

        <p class="text-center text-sm text-slate-500">
            Sudah punya akun?
            <a href="{{ route('login') }}" class="link">Masuk di sini</a>
        </p>
    </form>
@endsection
