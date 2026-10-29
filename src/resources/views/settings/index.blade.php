@extends('layouts.app')

@section('title', 'Pengaturan')
@section('subtitle', 'Kelola profil, keamanan, dan preferensi akunmu.')

@php
    $user = auth()->user();
    $timezones = [
        'Asia/Jakarta' => 'WIB — Asia/Jakarta',
        'Asia/Makassar' => 'WITA — Asia/Makassar',
        'Asia/Jayapura' => 'WIT — Asia/Jayapura',
        'Asia/Singapore' => 'Asia/Singapore',
        'Asia/Kuala_Lumpur' => 'Asia/Kuala Lumpur',
        'UTC' => 'UTC',
    ];
@endphp

@section('content')
    <div class="grid gap-5 xl:grid-cols-[1.5fr_1fr] xl:items-start">
        <div class="space-y-5">
            <section class="form-section">
                <div class="flex items-center gap-3">
                    <span class="avatar h-11 w-11 text-sm">{{ \Illuminate\Support\Str::of($user->name)->explode(' ')->filter()->map(fn ($word) => mb_substr($word, 0, 1))->take(2)->implode('') }}</span>
                    <div>
                        <h2 class="text-sm font-bold text-slate-900">Profil</h2>
                        <p class="text-xs text-slate-500">Nama dan kontak yang digunakan di Arus.</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('settings.profile') }}" class="mt-5 space-y-4">
                    @csrf
                    @method('PUT')

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label for="name" class="label">Nama lengkap</label>
                            <input id="name" name="name" type="text" required maxlength="100" value="{{ old('name', $user->name) }}"
                                   class="input @error('name') input-error @enderror">
                            @error('name')
                                <p class="error-text">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="email" class="label">Email <span class="font-normal text-slate-400">(opsional)</span></label>
                            <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" placeholder="nama@email.com"
                                   class="input @error('email') input-error @enderror">
                            @error('email')
                                <p class="error-text">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="label">Nomor HP</label>
                            <input type="text" value="{{ \App\Support\PhoneNormalizer::display($user->phone) }}" disabled
                                   class="input cursor-not-allowed bg-slate-50 text-slate-500">
                            <p class="hint">Hubungi admin jika perlu mengubah nomor HP.</p>
                        </div>
                        <div>
                            <label for="currency" class="label">Mata uang</label>
                            <select id="currency" name="currency" class="input @error('currency') input-error @enderror">
                                <option value="IDR" @selected(old('currency', $user->currency ?? 'IDR') === 'IDR')>IDR — Rupiah Indonesia</option>
                            </select>
                            @error('currency')
                                <p class="error-text">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="timezone" class="label">Zona waktu</label>
                            <select id="timezone" name="timezone" class="input @error('timezone') input-error @enderror">
                                @foreach ($timezones as $value => $label)
                                    <option value="{{ $value }}" @selected(old('timezone', $user->timezone ?? 'Asia/Jakarta') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('timezone')
                                <p class="error-text">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="btn-primary">
                            <x-heroicon-o-check class="h-4 w-4" />
                            Simpan Profil
                        </button>
                    </div>
                </form>
            </section>

            <section class="form-section">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-rose-50 text-rose-600">
                        <x-heroicon-o-lock-closed class="h-5 w-5" />
                    </span>
                    <div>
                        <h2 class="text-sm font-bold text-slate-900">Keamanan</h2>
                        <p class="text-xs text-slate-500">Ganti password dan akhiri sesi di perangkat lain.</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('settings.password') }}" class="mt-5 space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="current_password" class="label">Password saat ini</label>
                        <div class="relative">
                            <input id="current_password" name="current_password" type="password" required autocomplete="current-password"
                                   class="input pr-11 @error('current_password') input-error @enderror">
                            <button type="button" data-password-toggle="#current_password" class="absolute inset-y-0 right-0 flex w-11 items-center justify-center text-slate-400 hover:text-slate-600" aria-label="Tampilkan password">
                                <x-heroicon-o-eye class="h-5 w-5" data-eye-open />
                                <x-heroicon-o-eye-slash class="hidden h-5 w-5" data-eye-off />
                            </button>
                        </div>
                        @error('current_password')
                            <p class="error-text">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="password" class="label">Password baru</label>
                            <div class="relative">
                                <input id="password" name="password" type="password" required autocomplete="new-password" placeholder="Minimal 8 karakter"
                                       class="input pr-11 @error('password') input-error @enderror">
                                <button type="button" data-password-toggle="#password" class="absolute inset-y-0 right-0 flex w-11 items-center justify-center text-slate-400 hover:text-slate-600" aria-label="Tampilkan password">
                                    <x-heroicon-o-eye class="h-5 w-5" data-eye-open />
                                    <x-heroicon-o-eye-slash class="hidden h-5 w-5" data-eye-off />
                                </button>
                            </div>
                            @error('password')
                                <p class="error-text">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="password_confirmation" class="label">Ulangi password baru</label>
                            <div class="relative">
                                <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" placeholder="Ketik ulang"
                                       class="input pr-11">
                                <button type="button" data-password-toggle="#password_confirmation" class="absolute inset-y-0 right-0 flex w-11 items-center justify-center text-slate-400 hover:text-slate-600" aria-label="Tampilkan password">
                                    <x-heroicon-o-eye class="h-5 w-5" data-eye-open />
                                    <x-heroicon-o-eye-slash class="hidden h-5 w-5" data-eye-off />
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-xl bg-amber-50 p-3 text-xs leading-relaxed text-amber-700">
                        Setelah password diubah, semua sesi lain akan diakhiri. Kamu tetap masuk di perangkat ini.
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="btn-primary">
                            <x-heroicon-o-shield-check class="h-4 w-4" />
                            Perbarui Password
                        </button>
                    </div>
                </form>
            </section>
        </div>

        <aside class="space-y-4">
            <div class="card card-pad">
                <h2 class="text-sm font-bold text-slate-900">Informasi akun</h2>
                <dl class="mt-4 space-y-3 text-sm">
                    <div class="flex items-start justify-between gap-3">
                        <dt class="text-slate-500">Nomor HP</dt>
                        <dd class="text-right font-semibold text-slate-900">{{ \App\Support\PhoneNormalizer::display($user->phone) }}</dd>
                    </div>
                    <div class="flex items-start justify-between gap-3">
                        <dt class="text-slate-500">Status</dt>
                        <dd class="text-right"><span class="badge-success">{{ $user->status?->label() ?? 'Aktif' }}</span></dd>
                    </div>
                    <div class="flex items-start justify-between gap-3">
                        <dt class="text-slate-500">Peran</dt>
                        <dd class="text-right font-semibold text-slate-900">{{ $user->roles->pluck('name')->map(fn ($role) => \Illuminate\Support\Str::headline($role))->implode(', ') ?: 'Pengguna' }}</dd>
                    </div>
                    <div class="flex items-start justify-between gap-3">
                        <dt class="text-slate-500">Bergabung</dt>
                        <dd class="text-right font-semibold text-slate-900">{{ $user->created_at->translatedFormat('d M Y') }}</dd>
                    </div>
                    <div class="flex items-start justify-between gap-3">
                        <dt class="text-slate-500">Login terakhir</dt>
                        <dd class="text-right font-semibold text-slate-900">{{ $user->last_login_at?->translatedFormat('d M Y, H:i') ?? '—' }}</dd>
                    </div>
                </dl>
            </div>

            <div class="rounded-2xl border border-teal-100 bg-teal-50/60 p-4">
                <p class="flex items-center gap-2 text-xs font-bold text-teal-800">
                    <x-heroicon-o-shield-check class="h-4 w-4" />
                    Keamanan data
                </p>
                <p class="mt-1.5 text-xs leading-relaxed text-teal-700/80">Data keuanganmu terikat ke akun ini dan tidak dapat diakses pengguna lain. Jangan bagikan password kepada siapa pun.</p>
            </div>

            <div class="card card-pad">
                <h2 class="text-sm font-bold text-slate-900">Butuh bantuan?</h2>
                <p class="mt-1.5 text-xs leading-relaxed text-slate-500">Untuk pemulihan akun atau perubahan nomor HP, hubungi administrator Arus. Fitur verifikasi OTP akan tersedia setelah penyedia layanan dipilih.</p>
            </div>
        </aside>
    </div>
@endsection
