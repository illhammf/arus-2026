@extends('layouts.app')

@section('title', $transaction ? 'Edit Transfer' : 'Transfer Antar Akun')
@section('subtitle', 'Pindahkan uang tanpa menghitungnya sebagai pengeluaran.')

@php
    use App\Support\Money;

    $selectedSource = old('source_account_id', $sourceAccountId ?? $accounts->first()?->id);
    $selectedDestination = old('destination_account_id', $destinationAccountId);
    $occurredAt = old('occurred_at', $transaction?->occurred_at?->format('Y-m-d\TH:i') ?? now()->format('Y-m-d\TH:i'));
    $amount = old('amount', $transaction ? (int) $transaction->amount : '');
@endphp

@section('content')
    <div class="mb-5">
        <a href="{{ $transaction ? route('transactions.show', $transaction) : route('transactions.index') }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-slate-500 transition hover:text-slate-900">
            <x-heroicon-o-arrow-left class="h-4 w-4" />
            Kembali
        </a>
    </div>

    <div class="grid gap-5 xl:grid-cols-[1.6fr_1fr] xl:items-start">
        <form method="POST" action="{{ $transaction ? route('transfers.update', $transaction) : route('transfers.store') }}" class="space-y-5">
            @csrf
            @if ($transaction)
                @method('PUT')
            @endif

            <section class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-sky-700 via-blue-800 to-slate-900 p-6 text-white shadow-xl shadow-blue-900/10">
                <div class="hero-grid absolute inset-0 opacity-30"></div>
                <div class="relative flex items-center gap-4">
                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-white/10 ring-1 ring-white/20 backdrop-blur">
                        <x-heroicon-o-arrows-right-left class="h-6 w-6" />
                    </span>
                    <div>
                        <h2 class="text-lg font-extrabold tracking-tight">Pindahkan uang dengan rapi</h2>
                        <p class="mt-1 text-xs leading-relaxed text-sky-100/80">Transfer dicatat sebagai dua sisi — keluar dari akun sumber dan masuk ke akun tujuan — sehingga total kekayaanmu tidak berubah.</p>
                    </div>
                </div>
            </section>

            <section class="form-section">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-sky-50 text-sky-600">
                        <x-heroicon-o-wallet class="h-5 w-5" />
                    </span>
                    <div>
                        <h2 class="text-sm font-bold text-slate-900">Akun sumber & tujuan</h2>
                        <p class="text-xs text-slate-500">Pilih dari dan ke mana uang berpindah.</p>
                    </div>
                </div>

                <div class="mt-5 grid gap-3 sm:grid-cols-[1fr_auto_1fr] sm:items-end">
                    <div>
                        <label for="source_account_id" class="label">Dari akun</label>
                        <select id="source_account_id" name="source_account_id" required data-source-account class="input @error('source_account_id') input-error @enderror">
                            <option value="">Pilih akun sumber</option>
                            @foreach ($accounts as $account)
                                <option value="{{ $account->id }}" @selected((string) $selectedSource === (string) $account->id)>{{ $account->name }} — {{ $account->type->label() }}</option>
                            @endforeach
                        </select>
                        @error('source_account_id')
                            <p class="error-text">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="hidden pb-2.5 sm:block">
                        <span class="flex h-9 w-9 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                            <x-heroicon-o-arrow-right class="h-4 w-4" />
                        </span>
                    </div>

                    <div>
                        <label for="destination_account_id" class="label">Ke akun</label>
                        <select id="destination_account_id" name="destination_account_id" required data-destination-account class="input @error('destination_account_id') input-error @enderror">
                            <option value="">Pilih akun tujuan</option>
                            @foreach ($accounts as $account)
                                <option value="{{ $account->id }}" @selected((string) $selectedDestination === (string) $account->id)>{{ $account->name }} — {{ $account->type->label() }}</option>
                            @endforeach
                        </select>
                        @error('destination_account_id')
                            <p class="error-text">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                @if ($accounts->count() < 2)
                    <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 p-3.5 text-xs text-amber-800">
                        Kamu memerlukan minimal dua akun aktif untuk melakukan transfer.
                        <a href="{{ route('accounts.create') }}" class="font-bold underline">Buat akun baru</a>.
                    </div>
                @endif
            </section>

            <section class="form-section">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label for="amount" class="label">Nominal transfer</label>
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-base font-bold text-slate-400">Rp</span>
                            <input id="amount" name="amount" type="text" inputmode="numeric" required data-money-input
                                   value="{{ $amount }}" placeholder="0"
                                   class="input py-3.5 pl-12 text-right text-xl font-bold money @error('amount') input-error @enderror">
                        </div>
                        @error('amount')
                            <p class="error-text">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="occurred_at" class="label">Tanggal & waktu</label>
                        <input id="occurred_at" name="occurred_at" type="datetime-local" required value="{{ $occurredAt }}"
                               class="input @error('occurred_at') input-error @enderror">
                        @error('occurred_at')
                            <p class="error-text">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="description" class="label">Deskripsi <span class="font-normal text-slate-400">(opsional)</span></label>
                        <input id="description" name="description" type="text" maxlength="255" value="{{ old('description', $transaction?->description) }}"
                               placeholder="Contoh: Pindah dana ke tabungan"
                               class="input @error('description') input-error @enderror">
                        @error('description')
                            <p class="error-text">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label for="notes" class="label">Catatan <span class="font-normal text-slate-400">(opsional)</span></label>
                        <textarea id="notes" name="notes" rows="3" maxlength="2000" placeholder="Alasan atau detail transfer…"
                                  class="input @error('notes') input-error @enderror">{{ old('notes', $transaction?->notes) }}</textarea>
                        @error('notes')
                            <p class="error-text">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </section>

            <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                <a href="{{ $transaction ? route('transactions.show', $transaction) : route('transactions.index') }}" class="btn-secondary">Batal</a>
                <button type="submit" class="btn-primary" @disabled($accounts->count() < 2)>
                    <x-heroicon-o-paper-airplane class="h-4 w-4" />
                    {{ $transaction ? 'Simpan Perubahan' : 'Catat Transfer' }}
                </button>
            </div>
        </form>

        <aside class="space-y-4">
            <div class="card card-pad">
                <div class="flex items-center gap-3">
                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-slate-900 text-white">
                        <x-heroicon-o-light-bulb class="h-5 w-5" />
                    </span>
                    <h2 class="text-sm font-bold text-slate-900">Kenapa pakai transfer?</h2>
                </div>
                <ul class="mt-4 space-y-3 text-xs leading-relaxed text-slate-500">
                    <li class="flex gap-2"><x-heroicon-o-check class="mt-0.5 h-3.5 w-3.5 shrink-0 text-teal-600" /> Total kekayaan tidak berubah — hanya posisi uang yang berpindah.</li>
                    <li class="flex gap-2"><x-heroicon-o-check class="mt-0.5 h-3.5 w-3.5 shrink-0 text-teal-600" /> Laporan pengeluaran tetap akurat karena transfer tidak dihitung sebagai belanja.</li>
                    <li class="flex gap-2"><x-heroicon-o-check class="mt-0.5 h-3.5 w-3.5 shrink-0 text-teal-600" /> Kedua sisi tercatat otomatis dan selalu bisa diedit bersama.</li>
                </ul>
            </div>

            <div class="card card-pad">
                <h3 class="text-sm font-bold text-slate-900">Akun aktif</h3>
                <div class="mt-3 space-y-2">
                    @foreach ($accounts as $account)
                        <div class="flex items-center justify-between gap-3 rounded-lg bg-slate-50 px-3 py-2">
                            <span class="truncate text-xs font-semibold text-slate-700">{{ $account->name }}</span>
                            <span class="money text-[11px] font-medium text-slate-500">{{ Money::format($account->balance()) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="rounded-2xl border border-sky-100 bg-sky-50/60 p-4">
                <p class="flex items-center gap-2 text-xs font-bold text-sky-800">
                    <x-heroicon-o-information-circle class="h-4 w-4" />
                    Transfer bukan pengeluaran
                </p>
                <p class="mt-1.5 text-xs leading-relaxed text-sky-700/80">Jika kamu ingin mencatat belanja atau pemasukan, gunakan menu Transaksi. Menu ini khusus perpindahan antar akunmu.</p>
            </div>
        </aside>
    </div>
@endsection
