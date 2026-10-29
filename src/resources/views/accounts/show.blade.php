@extends('layouts.app')

@section('title', $account->name)
@section('subtitle', $account->type->label() . ' · ' . ($account->is_archived ? 'Diarsipkan' : 'Aktif'))

@php
    use App\Support\Money;

    $tone = match ($account->type->value) {
        'cash' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-600'],
        'bank' => ['bg' => 'bg-sky-50', 'text' => 'text-sky-600'],
        'ewallet' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-600'],
        'savings' => ['bg' => 'bg-violet-50', 'text' => 'text-violet-600'],
        default => ['bg' => 'bg-slate-100', 'text' => 'text-slate-600'],
    };
@endphp

@section('content')
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('accounts.index') }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-slate-500 transition hover:text-slate-900">
            <x-heroicon-o-arrow-left class="h-4 w-4" />
            Semua Akun
        </a>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('transactions.create', ['account_id' => $account->id]) }}" class="btn-primary btn-sm">
                <x-heroicon-o-plus class="h-4 w-4" />
                Catat Transaksi
            </a>
            <a href="{{ route('transfers.create', ['source_account_id' => $account->id]) }}" class="btn-secondary btn-sm">
                <x-heroicon-o-arrows-up-down class="h-4 w-4" />
                Transfer
            </a>
            <a href="{{ route('accounts.edit', $account) }}" class="btn-secondary btn-sm">
                <x-heroicon-o-pencil-square class="h-4 w-4" />
                Edit
            </a>
        </div>
    </div>

    <section class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-slate-900 via-slate-900 to-teal-950 p-6 text-white shadow-xl shadow-slate-900/10 sm:p-7">
        <div class="hero-grid absolute inset-0 opacity-40"></div>
        <div class="absolute -right-16 -top-24 h-64 w-64 rounded-full bg-teal-500/20 blur-3xl"></div>
        <div class="relative flex flex-wrap items-start justify-between gap-6">
            <div class="flex items-start gap-4">
                <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-white/10 text-white ring-1 ring-white/20 backdrop-blur">
                    <x-dynamic-component :component="'heroicon-o-' . $account->type->icon()" class="h-7 w-7" />
                </span>
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 class="text-xl font-extrabold tracking-tight">{{ $account->name }}</h2>
                        @if ($account->is_archived)
                            <span class="rounded-full bg-white/10 px-2.5 py-0.5 text-[10px] font-semibold text-slate-300">Diarsipkan</span>
                        @else
                            <span class="rounded-full bg-emerald-400/15 px-2.5 py-0.5 text-[10px] font-semibold text-emerald-300">Aktif</span>
                        @endif
                    </div>
                    <p class="mt-1 text-sm text-slate-400">{{ $account->type->label() }} · {{ $account->currency }}</p>
                    <p class="mt-1 text-xs text-slate-500">Dibuat {{ $account->created_at->translatedFormat('d F Y') }}</p>
                </div>
            </div>
            <div class="text-left sm:text-right">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Saldo saat ini</p>
                <p class="money mt-1 text-3xl font-extrabold tracking-tight sm:text-4xl">{{ Money::format($balance) }}</p>
                <p class="mt-1 text-xs text-slate-400">Saldo awal {{ Money::format($account->opening_balance) }}</p>
            </div>
        </div>

        <div class="relative mt-6 grid grid-cols-2 gap-3 sm:max-w-md">
            <div class="rounded-xl border border-white/10 bg-white/5 p-3 backdrop-blur">
                <p class="flex items-center gap-1.5 text-[10px] font-medium text-slate-400">
                    <x-heroicon-o-arrow-down-left class="h-3.5 w-3.5 text-emerald-300" /> Total masuk
                </p>
                <p class="money mt-1 text-sm font-bold text-emerald-300">{{ Money::format($incoming) }}</p>
            </div>
            <div class="rounded-xl border border-white/10 bg-white/5 p-3 backdrop-blur">
                <p class="flex items-center gap-1.5 text-[10px] font-medium text-slate-400">
                    <x-heroicon-o-arrow-up-right class="h-3.5 w-3.5 text-rose-300" /> Total keluar
                </p>
                <p class="money mt-1 text-sm font-bold text-rose-200">{{ Money::format($outgoing) }}</p>
            </div>
        </div>
    </section>

    <div class="mt-5 grid gap-5 xl:grid-cols-[1.7fr_1fr] xl:items-start">
        <section class="card card-pad">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-bold text-slate-900">Riwayat transaksi</h2>
                    <p class="mt-0.5 text-xs text-slate-500">Semua aktivitas pada akun ini</p>
                </div>
                <a href="{{ route('transactions.index', ['account_id' => $account->id]) }}" class="text-xs font-semibold text-teal-700 hover:text-teal-800">Filter lengkap</a>
            </div>

            @if ($transactions->isEmpty())
                <div class="mt-4">
                    <x-empty-state icon="receipt-percent" title="Belum ada transaksi" description="Akun ini belum memiliki riwayat. Catat transaksi pertamamu untuk melihatnya di sini.">
                        <a href="{{ route('transactions.create', ['account_id' => $account->id]) }}" class="btn-primary btn-sm">
                            <x-heroicon-o-plus class="h-4 w-4" />
                            Catat transaksi
                        </a>
                    </x-empty-state>
                </div>
            @else
                <div class="mt-3 divide-y divide-slate-100">
                    @foreach ($transactions as $transaction)
                        <x-transaction-item :transaction="$transaction" :show-account="false" class="-mx-2" />
                    @endforeach
                </div>
                <div class="mt-5">
                    {{ $transactions->links() }}
                </div>
            @endif
        </section>

        <aside class="space-y-4">
            <div class="card card-pad">
                <h2 class="text-sm font-bold text-slate-900">Detail akun</h2>
                <dl class="mt-4 space-y-3 text-sm">
                    <div class="flex items-start justify-between gap-3">
                        <dt class="text-slate-500">Jenis</dt>
                        <dd class="text-right font-semibold text-slate-900">{{ $account->type->label() }}</dd>
                    </div>
                    <div class="flex items-start justify-between gap-3">
                        <dt class="text-slate-500">Mata uang</dt>
                        <dd class="text-right font-semibold text-slate-900">{{ $account->currency }}</dd>
                    </div>
                    <div class="flex items-start justify-between gap-3">
                        <dt class="text-slate-500">Tanggal saldo awal</dt>
                        <dd class="text-right font-semibold text-slate-900">{{ $account->opening_balance_date?->translatedFormat('d M Y') ?? '-' }}</dd>
                    </div>
                    <div class="flex items-start justify-between gap-3">
                        <dt class="text-slate-500">Jumlah transaksi</dt>
                        <dd class="text-right font-semibold text-slate-900">{{ $transactions->total() }}</dd>
                    </div>
                </dl>

                @if ($account->notes)
                    <div class="mt-4 rounded-xl bg-slate-50 p-3.5">
                        <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Catatan</p>
                        <p class="mt-1 text-xs leading-relaxed text-slate-600">{{ $account->notes }}</p>
                    </div>
                @endif
            </div>

            <div class="card card-pad">
                <h2 class="text-sm font-bold text-slate-900">Kelola akun</h2>
                <p class="mt-1 text-xs leading-relaxed text-slate-500">Arsipkan akun agar tidak muncul di pilihan transaksi baru, tanpa menghapus riwayat.</p>
                <form method="POST" action="{{ route('accounts.archive', $account) }}" class="mt-4" data-confirm="Yakin ingin {{ $account->is_archived ? 'mengaktifkan kembali' : 'mengarsipkan' }} akun ini?">
                    @csrf
                    <button type="submit" class="btn-secondary w-full">
                        <x-heroicon-o-archive-box class="h-4 w-4" />
                        {{ $account->is_archived ? 'Aktifkan Kembali' : 'Arsipkan Akun' }}
                    </button>
                </form>

                @if (! $account->transactions()->exists())
                    <form method="POST" action="{{ route('accounts.destroy', $account) }}" class="mt-2" data-confirm="Hapus akun ini secara permanen? Tindakan ini tidak dapat dibatalkan.">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-soft-danger w-full">
                            <x-heroicon-o-trash class="h-4 w-4" />
                            Hapus Akun
                        </button>
                    </form>
                @else
                    <p class="mt-2 rounded-lg bg-slate-50 p-2.5 text-[11px] leading-relaxed text-slate-500">Akun dengan transaksi tidak dapat dihapus. Arsipkan saja untuk menjaga riwayat tetap utuh.</p>
                @endif
            </div>
        </aside>
    </div>
@endsection
