@extends('layouts.app')

@section('title', 'Transaksi')
@section('subtitle', 'Semua pemasukan, pengeluaran, dan transfermu.')

@php
    use App\Support\Money;

    $hasFilters = collect($filters)->filter(fn ($value) => filled($value))->isNotEmpty();
@endphp

@section('content')
    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex flex-wrap items-center gap-2">
            <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700">
                <x-heroicon-o-arrow-down-left class="h-3.5 w-3.5" />
                Masuk bulan ini {{ Money::format($income) }}
            </span>
            <span class="inline-flex items-center gap-1.5 rounded-full bg-rose-50 px-3 py-1.5 text-xs font-semibold text-rose-700">
                <x-heroicon-o-arrow-up-right class="h-3.5 w-3.5" />
                Keluar bulan ini {{ Money::format($expense) }}
            </span>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('transfers.create') }}" class="btn-secondary btn-sm">
                <x-heroicon-o-arrows-up-down class="h-4 w-4" />
                Transfer
            </a>
            <a href="{{ route('transactions.create') }}" class="btn-primary btn-sm">
                <x-heroicon-o-plus class="h-4 w-4" />
                Transaksi Baru
            </a>
        </div>
    </div>

    <section class="card card-pad mb-5">
        <div class="flex items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <x-heroicon-o-funnel class="h-4 w-4 text-slate-400" />
                <h2 class="text-sm font-bold text-slate-900">Cari & filter</h2>
            </div>
            @if ($hasFilters)
                <a href="{{ route('transactions.index') }}" class="inline-flex items-center gap-1 text-xs font-semibold text-slate-500 hover:text-slate-900">
                    <x-heroicon-o-x-mark class="h-3.5 w-3.5" />
                    Reset
                </a>
            @endif
        </div>

        <form method="GET" action="{{ route('transactions.index') }}" class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-6">
            <div class="lg:col-span-2">
                <label for="search" class="label">Pencarian</label>
                <div class="relative">
                    <x-heroicon-o-magnifying-glass class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                    <input id="search" name="search" type="search" value="{{ $filters['search'] ?? '' }}" placeholder="Deskripsi atau catatan…" class="input pl-9">
                </div>
            </div>
            <div>
                <label for="type" class="label">Jenis</label>
                <select id="type" name="type" class="input">
                    <option value="">Semua jenis</option>
                    @foreach ($types as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['type'] ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="account_id" class="label">Akun</label>
                <select id="account_id" name="account_id" class="input">
                    <option value="">Semua akun</option>
                    @foreach ($accounts as $account)
                        <option value="{{ $account->id }}" @selected((string) ($filters['account_id'] ?? '') === (string) $account->id)>{{ $account->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="category_id" class="label">Kategori</label>
                <select id="category_id" name="category_id" class="input">
                    <option value="">Semua kategori</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected((string) ($filters['category_id'] ?? '') === (string) $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="btn-primary w-full">
                    <x-heroicon-o-magnifying-glass class="h-4 w-4" />
                    Terapkan
                </button>
            </div>
            <div>
                <label for="from" class="label">Dari tanggal</label>
                <input id="from" name="from" type="date" value="{{ $filters['from'] ?? '' }}" class="input">
            </div>
            <div>
                <label for="to" class="label">Sampai tanggal</label>
                <input id="to" name="to" type="date" value="{{ $filters['to'] ?? '' }}" class="input">
            </div>
        </form>
    </section>

    @if ($transactions->isEmpty())
        <x-empty-state icon="receipt-percent" title="Tidak ada transaksi" description="{{ $hasFilters ? 'Coba ubah kata kunci atau filter yang kamu gunakan.' : 'Mulai catat pemasukan atau pengeluaran pertamamu untuk melihat riwayat di sini.' }}">
            @if ($hasFilters)
                <a href="{{ route('transactions.index') }}" class="btn-secondary">Hapus filter</a>
            @else
                <a href="{{ route('transactions.create') }}" class="btn-primary">
                    <x-heroicon-o-plus class="h-4 w-4" />
                    Catat transaksi
                </a>
            @endif
        </x-empty-state>
    @else
        {{-- Desktop table --}}
        <div class="table-wrap hidden md:block">
            <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
                <p class="text-xs font-semibold text-slate-500">{{ number_format($transactions->total(), 0, ',', '.') }} transaksi ditemukan</p>
                <p class="text-[11px] text-slate-400">Halaman {{ $transactions->currentPage() }} dari {{ $transactions->lastPage() }}</p>
            </div>
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Deskripsi</th>
                            <th>Kategori</th>
                            <th>Akun</th>
                            <th>Jenis</th>
                            <th class="text-right">Nominal</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @foreach ($transactions as $transaction)
                            @php
                                $type = $transaction->type;
                                $badge = match ($type->value) {
                                    'income' => 'badge-success',
                                    'expense' => 'badge-danger',
                                    'transfer_out' => 'badge-warning',
                                    'transfer_in' => 'badge-info',
                                    default => 'badge-neutral',
                                };
                                $negative = in_array($type->value, ['expense', 'transfer_out'], true);
                            @endphp
                            <tr>
                                <td class="text-xs text-slate-500">{{ $transaction->occurred_at?->translatedFormat('d M Y') }}<span class="block text-[10px] text-slate-400">{{ $transaction->occurred_at?->format('H:i') }}</span></td>
                                <td>
                                    <a href="{{ route('transactions.show', $transaction) }}" class="font-semibold text-slate-900 hover:text-teal-700">{{ $transaction->description }}</a>
                                    @if ($transaction->notes)
                                        <p class="mt-0.5 max-w-xs truncate text-[11px] text-slate-400">{{ $transaction->notes }}</p>
                                    @endif
                                </td>
                                <td>
                                    @if ($transaction->category)
                                        <span class="inline-flex items-center gap-1.5 text-xs text-slate-600">
                                            <span class="h-1.5 w-1.5 rounded-full" style="background-color: {{ $transaction->category->color ?? '#94a3b8' }}"></span>
                                            {{ $transaction->category->name }}
                                        </span>
                                    @else
                                        <span class="text-xs text-slate-400">—</span>
                                    @endif
                                </td>
                                <td class="text-xs">{{ $transaction->account?->name }}</td>
                                <td><span class="{{ $badge }}">{{ $transaction->type->label() }}</span></td>
                                <td class="money text-right text-sm font-bold {{ $negative ? 'text-slate-900' : 'text-emerald-600' }}">
                                    {{ $negative ? '−' : '+' }}{{ Money::format($transaction->amount, $transaction->currency) }}
                                </td>
                                <td class="text-right">
                                    <a href="{{ route('transactions.show', $transaction) }}" class="btn-icon" aria-label="Lihat detail">
                                        <x-heroicon-o-eye class="h-4 w-4" />
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="border-t border-slate-100 px-4 py-3">
                {{ $transactions->links() }}
            </div>
        </div>

        {{-- Mobile list --}}
        <div class="space-y-3 md:hidden">
            <div class="flex items-center justify-between px-1">
                <p class="text-xs font-semibold text-slate-500">{{ number_format($transactions->total(), 0, ',', '.') }} transaksi</p>
                <p class="text-[11px] text-slate-400">Hal. {{ $transactions->currentPage() }}/{{ $transactions->lastPage() }}</p>
            </div>
            <div class="card divide-y divide-slate-100 p-2">
                @foreach ($transactions as $transaction)
                    <x-transaction-item :transaction="$transaction" class="-mx-1" />
                @endforeach
            </div>
            {{ $transactions->links() }}
        </div>
    @endif
@endsection
