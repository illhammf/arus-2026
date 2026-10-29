@extends('layouts.app')

@section('title', 'Detail Transaksi')
@section('subtitle', $transaction->occurred_at?->translatedFormat('l, d F Y · H:i'))

@php
    use App\Support\Money;

    $type = $transaction->type;
    $isNegative = in_array($type->value, ['expense', 'transfer_out'], true);
    $meta = match ($type->value) {
        'income' => ['bg' => 'from-emerald-600 to-teal-700', 'badge' => 'badge-success', 'icon' => 'arrow-down-left', 'label' => 'Pemasukan'],
        'expense' => ['bg' => 'from-rose-600 to-rose-800', 'badge' => 'badge-danger', 'icon' => 'arrow-up-right', 'label' => 'Pengeluaran'],
        'transfer_out' => ['bg' => 'from-amber-500 to-orange-700', 'badge' => 'badge-warning', 'icon' => 'arrows-right-left', 'label' => 'Transfer keluar'],
        'transfer_in' => ['bg' => 'from-sky-600 to-blue-800', 'badge' => 'badge-info', 'icon' => 'arrows-right-left', 'label' => 'Transfer masuk'],
        default => ['bg' => 'from-slate-700 to-slate-900', 'badge' => 'badge-neutral', 'icon' => 'receipt-percent', 'label' => 'Transaksi'],
    };
@endphp

@section('content')
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('transactions.index') }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-slate-500 transition hover:text-slate-900">
            <x-heroicon-o-arrow-left class="h-4 w-4" />
            Semua Transaksi
        </a>
        <div class="flex items-center gap-2">
            <a href="{{ $transaction->isTransfer() ? route('transfers.edit', $transaction) : route('transactions.edit', $transaction) }}" class="btn-secondary btn-sm">
                <x-heroicon-o-pencil-square class="h-4 w-4" />
                Edit
            </a>
            <form method="POST" action="{{ route('transactions.destroy', $transaction) }}" data-confirm="Hapus transaksi ini? {{ $transaction->isTransfer() ? 'Kedua sisi transfer akan dihapus.' : 'Saldo akun akan disesuaikan.' }}">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-soft-danger btn-sm">
                    <x-heroicon-o-trash class="h-4 w-4" />
                    Hapus
                </button>
            </form>
        </div>
    </div>

    <div class="grid gap-5 xl:grid-cols-[1.35fr_1fr] xl:items-start">
        <div class="space-y-5">
            <section class="relative overflow-hidden rounded-3xl bg-gradient-to-br {{ $meta['bg'] }} p-6 text-white shadow-xl shadow-slate-900/10 sm:p-8">
                <div class="hero-grid absolute inset-0 opacity-30"></div>
                <div class="absolute -right-16 -top-20 h-56 w-56 rounded-full bg-white/10 blur-3xl"></div>
                <div class="relative">
                    <div class="flex items-center justify-between gap-3">
                        <span class="inline-flex items-center gap-2 rounded-full bg-white/15 px-3 py-1.5 text-xs font-semibold backdrop-blur">
                            <x-dynamic-component :component="'heroicon-o-' . $meta['icon']" class="h-4 w-4" />
                            {{ $meta['label'] }}
                        </span>
                        <span class="text-xs font-medium text-white/70">{{ $transaction->status->label() }}</span>
                    </div>

                    <p class="money mt-8 text-4xl font-extrabold tracking-tight sm:text-5xl">
                        {{ $isNegative ? '−' : '+' }}{{ Money::format($transaction->amount, $transaction->currency) }}
                    </p>
                    <p class="mt-3 text-lg font-semibold text-white/90">{{ $transaction->description }}</p>
                    <p class="mt-1 text-sm text-white/60">{{ $transaction->occurred_at?->translatedFormat('l, d F Y · H:i') }}</p>

                    <div class="mt-6 flex items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 rounded-lg bg-white/10 px-2.5 py-1.5 text-xs font-medium backdrop-blur">
                            <x-heroicon-o-wallet class="h-3.5 w-3.5" />
                            {{ $transaction->account?->name }}
                        </span>
                        @if ($transaction->category)
                            <span class="inline-flex items-center gap-1.5 rounded-lg bg-white/10 px-2.5 py-1.5 text-xs font-medium backdrop-blur">
                                <span class="h-2 w-2 rounded-full" style="background-color: {{ $transaction->category->color ?? '#e2e8f0' }}"></span>
                                {{ $transaction->category->name }}
                            </span>
                        @endif
                    </div>
                </div>
            </section>

            @if ($pair)
                <section class="card card-pad">
                    <div class="flex items-center gap-3">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-sky-50 text-sky-600">
                            <x-heroicon-o-arrows-right-left class="h-5 w-5" />
                        </span>
                        <div>
                            <h2 class="text-sm font-bold text-slate-900">Pasangan transfer</h2>
                            <p class="text-xs text-slate-500">Sisi lain dari perpindahan uang ini</p>
                        </div>
                    </div>
                    <a href="{{ route('transactions.show', $pair) }}" class="mt-4 flex items-center gap-3 rounded-xl border border-slate-100 p-3 transition hover:border-teal-200 hover:bg-teal-50/30">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100 text-slate-600">
                            <x-heroicon-o-wallet class="h-5 w-5" />
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-semibold text-slate-900">{{ $pair->account?->name }}</p>
                            <p class="text-xs text-slate-500">{{ $pair->type->label() }} · {{ $pair->occurred_at?->translatedFormat('d M Y') }}</p>
                        </div>
                        <p class="money text-sm font-bold {{ $pair->type->value === 'transfer_in' ? 'text-emerald-600' : 'text-slate-900' }}">
                            {{ $pair->type->value === 'transfer_in' ? '+' : '−' }}{{ Money::format($pair->amount, $pair->currency) }}
                        </p>
                    </a>
                </section>
            @endif

            @if ($transaction->notes)
                <section class="card card-pad">
                    <h2 class="text-sm font-bold text-slate-900">Catatan</h2>
                    <p class="mt-2 whitespace-pre-line text-sm leading-relaxed text-slate-600">{{ $transaction->notes }}</p>
                </section>
            @endif
        </div>

        <aside class="space-y-4">
            <div class="card card-pad">
                <h2 class="text-sm font-bold text-slate-900">Rincian</h2>
                <dl class="mt-4 space-y-3.5 text-sm">
                    <div class="flex items-start justify-between gap-4">
                        <dt class="text-slate-500">Jenis</dt>
                        <dd class="text-right"><span class="{{ $meta['badge'] }}">{{ $type->label() }}</span></dd>
                    </div>
                    <div class="flex items-start justify-between gap-4">
                        <dt class="text-slate-500">Akun</dt>
                        <dd class="text-right font-semibold text-slate-900">
                            <a href="{{ route('accounts.show', $transaction->account) }}" class="hover:text-teal-700">{{ $transaction->account?->name }}</a>
                        </dd>
                    </div>
                    <div class="flex items-start justify-between gap-4">
                        <dt class="text-slate-500">Kategori</dt>
                        <dd class="text-right font-semibold text-slate-900">{{ $transaction->category?->name ?? 'Tanpa kategori' }}</dd>
                    </div>
                    <div class="flex items-start justify-between gap-4">
                        <dt class="text-slate-500">Tanggal</dt>
                        <dd class="text-right font-semibold text-slate-900">{{ $transaction->occurred_at?->translatedFormat('d M Y') }}</dd>
                    </div>
                    <div class="flex items-start justify-between gap-4">
                        <dt class="text-slate-500">Waktu</dt>
                        <dd class="text-right font-semibold text-slate-900">{{ $transaction->occurred_at?->format('H:i') }} WIB</dd>
                    </div>
                    <div class="flex items-start justify-between gap-4">
                        <dt class="text-slate-500">Mata uang</dt>
                        <dd class="text-right font-semibold text-slate-900">{{ $transaction->currency }}</dd>
                    </div>
                    <div class="flex items-start justify-between gap-4">
                        <dt class="text-slate-500">Status</dt>
                        <dd class="text-right font-semibold text-slate-900">{{ $transaction->status->label() }}</dd>
                    </div>
                    <div class="flex items-start justify-between gap-4">
                        <dt class="text-slate-500">Dicatat</dt>
                        <dd class="text-right font-semibold text-slate-900">{{ $transaction->created_at->translatedFormat('d M Y, H:i') }}</dd>
                    </div>
                </dl>
            </div>

            <div class="rounded-2xl border border-teal-100 bg-teal-50/60 p-4">
                <p class="flex items-center gap-2 text-xs font-bold text-teal-800">
                    <x-heroicon-o-shield-check class="h-4 w-4" />
                    Transaksi aman
                </p>
                <p class="mt-1.5 text-xs leading-relaxed text-teal-700/80">Hanya kamu yang dapat melihat detail transaksi ini. Semua perubahan saldo tercatat dari data transaksi, bukan angka manual.</p>
            </div>
        </aside>
    </div>
@endsection
