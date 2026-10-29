@extends('layouts.app')

@section('title', $account->exists ? 'Edit Akun' : 'Tambah Akun')
@section('subtitle', $account->exists ? 'Perbarui informasi akun keuanganmu.' : 'Buat wadah baru untuk arus uangmu.')

@php
    use App\Enums\AccountType;
    use App\Support\Money;

    $selectedType = old('type', $account->type?->value ?? AccountType::Cash->value);
    $openingBalance = old('opening_balance', $account->exists ? (int) $account->opening_balance : '');
    $openingDate = old('opening_balance_date', $account->opening_balance_date?->format('Y-m-d') ?? now()->format('Y-m-d'));
@endphp

@section('content')
    <div class="mb-5">
        <a href="{{ $account->exists ? route('accounts.show', $account) : route('accounts.index') }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-slate-500 transition hover:text-slate-900">
            <x-heroicon-o-arrow-left class="h-4 w-4" />
            Kembali
        </a>
    </div>

    <form method="POST" action="{{ $account->exists ? route('accounts.update', $account) : route('accounts.store') }}" class="grid gap-5 xl:grid-cols-[1.6fr_1fr] xl:items-start">
        @csrf
        @if ($account->exists)
            @method('PUT')
        @endif

        <div class="space-y-5">
            <section class="form-section">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-teal-50 text-teal-600">
                        <x-heroicon-o-wallet class="h-5 w-5" />
                    </span>
                    <div>
                        <h2 class="text-sm font-bold text-slate-900">Informasi akun</h2>
                        <p class="text-xs text-slate-500">Beri nama yang mudah kamu kenali.</p>
                    </div>
                </div>

                <div class="mt-5 grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label for="name" class="label">Nama akun</label>
                        <input id="name" name="name" type="text" required maxlength="100"
                               value="{{ old('name', $account->name) }}" placeholder="Contoh: BCA Utama, Dompet Harian"
                               class="input @error('name') input-error @enderror">
                        @error('name')
                            <p class="error-text">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <span class="label">Jenis akun</span>
                        <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
                            @foreach (AccountType::cases() as $type)
                                <label class="relative cursor-pointer">
                                    <input type="radio" name="type" value="{{ $type->value }}" class="peer sr-only" @checked($selectedType === $type->value)>
                                    <span class="flex items-center gap-2 rounded-xl border border-slate-200 bg-white p-3 text-xs font-semibold text-slate-600 transition peer-checked:border-teal-500 peer-checked:bg-teal-50 peer-checked:text-teal-700 peer-focus-visible:ring-2 peer-focus-visible:ring-teal-500 hover:border-slate-300">
                                        <x-dynamic-component :component="'heroicon-o-' . $type->icon()" class="h-4 w-4 shrink-0" />
                                        <span class="truncate">{{ $type->label() }}</span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        @error('type')
                            <p class="error-text">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="currency" class="label">Mata uang</label>
                        <select id="currency" name="currency" class="input @error('currency') input-error @enderror">
                            <option value="IDR" @selected(old('currency', $account->currency ?? 'IDR') === 'IDR')>IDR — Rupiah Indonesia</option>
                        </select>
                        @error('currency')
                            <p class="error-text">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="color" class="label">Warna penanda</label>
                        <div class="flex items-center gap-3">
                            <input id="color" name="color" type="color" value="{{ old('color', $account->color ?? '#0d9488') }}"
                                   class="h-10 w-14 cursor-pointer rounded-lg border border-slate-300 bg-white p-1">
                            <span class="text-xs text-slate-500">Dipakai sebagai aksen visual akun.</span>
                        </div>
                        @error('color')
                            <p class="error-text">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </section>

            <section class="form-section">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-50 text-amber-600">
                        <x-heroicon-o-banknotes class="h-5 w-5" />
                    </span>
                    <div>
                        <h2 class="text-sm font-bold text-slate-900">Saldo awal</h2>
                        <p class="text-xs text-slate-500">Jumlah uang yang sudah ada di akun ini saat mulai dicatat.</p>
                    </div>
                </div>

                <div class="mt-5 grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="opening_balance" class="label">Saldo awal</label>
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-sm font-semibold text-slate-400">Rp</span>
                            <input id="opening_balance" name="opening_balance" type="text" inputmode="numeric" required data-money-input
                                   value="{{ $openingBalance }}" placeholder="0"
                                   class="input pl-11 text-right money @error('opening_balance') input-error @enderror">
                        </div>
                        @error('opening_balance')
                            <p class="error-text">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="opening_balance_date" class="label">Tanggal saldo awal</label>
                        <input id="opening_balance_date" name="opening_balance_date" type="date" value="{{ $openingDate }}"
                               class="input @error('opening_balance_date') input-error @enderror">
                        @error('opening_balance_date')
                            <p class="error-text">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mt-4 rounded-xl bg-slate-50 p-3.5 text-xs leading-relaxed text-slate-500">
                    <p class="font-semibold text-slate-700">Contoh</p>
                    <p class="mt-0.5">Jika saat ini saldo BCA-mu Rp2.500.000, isi saldo awal Rp2.500.000. Saldo akan bertambah atau berkurang otomatis dari transaksi berikutnya.</p>
                </div>
            </section>

            <section class="form-section">
                <label for="notes" class="label">Catatan <span class="font-normal text-slate-400">(opsional)</span></label>
                <textarea id="notes" name="notes" rows="3" maxlength="2000" placeholder="Misalnya: rekening gaji, dompet untuk belanja harian…"
                          class="input @error('notes') input-error @enderror">{{ old('notes', $account->notes) }}</textarea>
                @error('notes')
                    <p class="error-text">{{ $message }}</p>
                @enderror
            </section>

            <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                <a href="{{ $account->exists ? route('accounts.show', $account) : route('accounts.index') }}" class="btn-secondary">Batal</a>
                <button type="submit" class="btn-primary">
                    <x-heroicon-o-check class="h-4 w-4" />
                    {{ $account->exists ? 'Simpan Perubahan' : 'Buat Akun' }}
                </button>
            </div>
        </div>

        <aside class="space-y-4">
            <div class="card card-pad">
                <div class="flex items-center gap-3">
                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-slate-900 text-white">
                        <x-heroicon-o-light-bulb class="h-5 w-5" />
                    </span>
                    <h2 class="text-sm font-bold text-slate-900">Tips mengatur akun</h2>
                </div>
                <ul class="mt-4 space-y-3 text-xs leading-relaxed text-slate-500">
                    <li class="flex gap-2">
                        <x-heroicon-o-check class="mt-0.5 h-3.5 w-3.5 shrink-0 text-teal-600" />
                        Pisahkan akun berdasarkan fungsi: kebutuhan harian, tabungan, dan darurat.
                    </li>
                    <li class="flex gap-2">
                        <x-heroicon-o-check class="mt-0.5 h-3.5 w-3.5 shrink-0 text-teal-600" />
                        Gunakan nama yang spesifik agar mudah dibedakan saat mencatat transaksi.
                    </li>
                    <li class="flex gap-2">
                        <x-heroicon-o-check class="mt-0.5 h-3.5 w-3.5 shrink-0 text-teal-600" />
                        Akun yang tidak dipakai lagi bisa diarsipkan tanpa kehilangan riwayat.
                    </li>
                </ul>
            </div>

            @if ($account->exists)
                <div class="card card-pad">
                    <p class="text-xs font-semibold text-slate-500">Saldo saat ini</p>
                    <p class="money mt-1 text-2xl font-extrabold text-slate-900">{{ Money::format($account->balance()) }}</p>
                    <p class="mt-1 text-[11px] text-slate-400">{{ $account->transactions()->count() }} transaksi tercatat</p>
                </div>
            @endif

            <div class="rounded-2xl border border-teal-100 bg-teal-50/60 p-4">
                <p class="flex items-center gap-2 text-xs font-bold text-teal-800">
                    <x-heroicon-o-shield-check class="h-4 w-4" />
                    Data milikmu
                </p>
                <p class="mt-1.5 text-xs leading-relaxed text-teal-700/80">Akun ini hanya dapat dilihat dan diubah oleh kamu. Setiap akses diperiksa di server.</p>
            </div>
        </aside>
    </form>
@endsection
