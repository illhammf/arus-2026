@extends('layouts.app')

@section('title', 'Akun Keuangan')
@section('subtitle', 'Semua dompet, rekening, dan tabunganmu di satu tempat.')

@php
    use App\Support\Money;

    $activeAccounts = $accounts->where('is_archived', false);
    $archivedAccounts = $accounts->where('is_archived', true);
    $activeTotal = Money::add(...array_values(array_filter(
        array_map(fn ($account) => $balances[$account->id] ?? '0.00', $activeAccounts->all()),
        fn ($balance) => $balance !== null,
    )));
@endphp

@section('content')
    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-2 text-xs text-slate-500">
            <x-heroicon-o-information-circle class="h-4 w-4 text-slate-400" />
            Saldo dihitung dari saldo awal + seluruh transaksi tercatat.
        </div>
        <a href="{{ route('accounts.create') }}" class="btn-primary">
            <x-heroicon-o-plus class="h-4 w-4" />
            Tambah Akun
        </a>
    </div>

    <section class="relative mb-6 overflow-hidden rounded-3xl bg-gradient-to-br from-slate-900 via-slate-900 to-teal-950 p-6 text-white shadow-xl shadow-slate-900/10 sm:p-7">
        <div class="hero-grid absolute inset-0 opacity-40"></div>
        <div class="absolute -right-20 -top-24 h-64 w-64 rounded-full bg-teal-500/20 blur-3xl"></div>
        <div class="relative flex flex-wrap items-center justify-between gap-6">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total kekayaan aktif</p>
                <p class="money mt-2 text-3xl font-extrabold tracking-tight sm:text-4xl">{{ Money::format($activeTotal) }}</p>
                <p class="mt-2 text-xs text-slate-400">{{ $activeAccounts->count() }} akun aktif{{ $archivedAccounts->count() ? ' · ' . $archivedAccounts->count() . ' diarsipkan' : '' }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('transfers.create') }}" class="btn border border-white/15 bg-white/10 text-white hover:bg-white/20">
                    <x-heroicon-o-arrows-up-down class="h-4 w-4" />
                    Transfer
                </a>
                <a href="{{ route('transactions.create') }}" class="btn bg-white text-slate-900 hover:bg-slate-100">
                    <x-heroicon-o-plus class="h-4 w-4" />
                    Catat Transaksi
                </a>
            </div>
        </div>
    </section>

    @if ($accounts->isEmpty())
        <x-empty-state icon="wallet" title="Belum ada akun keuangan" description="Buat dompet, rekening bank, atau e-wallet pertamamu untuk mulai mencatat arus uang.">
            <a href="{{ route('accounts.create') }}" class="btn-primary">
                <x-heroicon-o-plus class="h-4 w-4" />
                Buat Akun Pertama
            </a>
        </x-empty-state>
    @else
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ($accounts as $account)
                @php
                    $balance = $balances[$account->id] ?? '0.00';
                    $tone = match ($account->type->value) {
                        'cash' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-600', 'bar' => 'bg-emerald-500'],
                        'bank' => ['bg' => 'bg-sky-50', 'text' => 'text-sky-600', 'bar' => 'bg-sky-500'],
                        'ewallet' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-600', 'bar' => 'bg-amber-500'],
                        'savings' => ['bg' => 'bg-violet-50', 'text' => 'text-violet-600', 'bar' => 'bg-violet-500'],
                        default => ['bg' => 'bg-slate-100', 'text' => 'text-slate-600', 'bar' => 'bg-slate-500'],
                    };
                @endphp
                <article class="card card-hover relative overflow-hidden p-5 {{ $account->is_archived ? 'opacity-70' : '' }}">
                    <div class="absolute inset-x-0 top-0 h-1 {{ $tone['bar'] }}"></div>
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex min-w-0 items-center gap-3">
                            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl {{ $tone['bg'] }} {{ $tone['text'] }}">
                                <x-dynamic-component :component="'heroicon-o-' . $account->type->icon()" class="h-5 w-5" />
                            </span>
                            <div class="min-w-0">
                                <h2 class="truncate text-sm font-bold text-slate-900">{{ $account->name }}</h2>
                                <p class="mt-0.5 text-xs text-slate-500">{{ $account->type->label() }}</p>
                            </div>
                        </div>
                        @if ($account->is_archived)
                            <span class="badge-neutral">Diarsipkan</span>
                        @else
                            <span class="badge-success">Aktif</span>
                        @endif
                    </div>

                    <div class="mt-5">
                        <p class="text-[11px] font-medium text-slate-400">Saldo saat ini</p>
                        <p class="money mt-1 text-2xl font-extrabold tracking-tight text-slate-900">{{ Money::format($balance) }}</p>
                        <p class="mt-1 text-[11px] text-slate-400">Saldo awal {{ Money::format($account->opening_balance) }}</p>
                    </div>

                    <div class="mt-5 flex items-center gap-1 border-t border-slate-100 pt-3">
                        <a href="{{ route('accounts.show', $account) }}" class="btn-ghost btn-sm">
                            <x-heroicon-o-eye class="h-4 w-4" />
                            Detail
                        </a>
                        <a href="{{ route('accounts.edit', $account) }}" class="btn-ghost btn-sm">
                            <x-heroicon-o-pencil-square class="h-4 w-4" />
                            Edit
                        </a>
                        <div class="ml-auto">
                            <form method="POST" action="{{ route('accounts.archive', $account) }}" data-confirm="Yakin ingin {{ $account->is_archived ? 'mengaktifkan kembali' : 'mengarsipkan' }} akun ini?">
                                @csrf
                                <button type="submit" class="btn-icon" title="{{ $account->is_archived ? 'Aktifkan kembali' : 'Arsipkan' }}">
                                    <x-heroicon-o-archive-box class="h-4 w-4" />
                                </button>
                            </form>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    @endif
@endsection
