@extends('layouts.app')

@section('title', 'Dashboard')
@section('subtitle', 'Ringkasan arus keuanganmu bulan ini.')

@php
    use App\Support\Money;
    use Carbon\Carbon;

    $user = auth()->user();
    $firstName = \Illuminate\Support\Str::of($user->name)->explode(' ')->first();
    $period = Carbon::createFromFormat('Y-m', $month);
    $previousMonth = $period->copy()->subMonth()->format('Y-m');
    $nextMonth = $period->copy()->addMonth()->format('Y-m');
    $expenseCategories = collect($summary['expense_by_category']);
    $expenseTotal = (float) $expenseCategories->sum(fn ($category) => (float) $category['total']);
    $palette = ['#0d9488', '#f59e0b', '#0ea5e9', '#ec4899', '#8b5cf6', '#f43f5e'];
    $circumference = 2 * M_PI * 40;
    $offset = 0;
    $maxDaily = max(1, ...$summary['daily_series']['income'], ...$summary['daily_series']['expense']);
@endphp

@section('content')
    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-sm text-slate-500">Halo, <span class="font-semibold text-slate-900">{{ $firstName }}</span> 👋</p>
            <p class="mt-0.5 text-xs text-slate-400">{{ now()->translatedFormat('l, d F Y') }}</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('dashboard', ['month' => $previousMonth]) }}" class="btn-icon border border-slate-200 bg-white" aria-label="Bulan sebelumnya">
                <x-heroicon-o-chevron-left class="h-4 w-4" />
            </a>
            <form method="GET" action="{{ route('dashboard') }}" class="relative">
                <x-heroicon-o-calendar-days class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                <input type="month" name="month" value="{{ $month }}" onchange="this.form.submit()"
                       class="input py-2 pl-9 pr-3 text-xs font-semibold">
            </form>
            <a href="{{ route('dashboard', ['month' => $nextMonth]) }}" class="btn-icon border border-slate-200 bg-white" aria-label="Bulan berikutnya">
                <x-heroicon-o-chevron-right class="h-4 w-4" />
            </a>
        </div>
    </div>

    <div class="grid gap-5 xl:grid-cols-[1.55fr_1fr]">
        {{-- Balance hero --}}
        <section class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-teal-700 via-teal-800 to-slate-900 p-6 text-white shadow-xl shadow-teal-900/15 sm:p-7">
            <div class="hero-grid absolute inset-0 opacity-40"></div>
            <div class="absolute -right-16 -top-20 h-64 w-64 rounded-full bg-teal-400/20 blur-3xl"></div>
            <div class="absolute -bottom-24 left-1/3 h-56 w-56 rounded-full bg-emerald-400/10 blur-3xl"></div>

            <div class="relative flex h-full flex-col">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-teal-100/80">Total saldo semua akun</p>
                        <p class="money mt-2 text-3xl font-extrabold tracking-tight sm:text-4xl">{{ Money::format($summary['total_balance']) }}</p>
                        <p class="mt-1.5 inline-flex items-center gap-1.5 text-xs text-teal-100/80">
                            <x-heroicon-o-wallet class="h-3.5 w-3.5" />
                            {{ $summary['accounts']->count() }} akun aktif
                        </p>
                    </div>
                    <span class="hidden rounded-full border border-white/15 bg-white/10 px-3 py-1 text-[11px] font-semibold text-teal-50 sm:inline-flex">
                        {{ $monthLabel }}
                    </span>
                </div>

                <div class="mt-6 grid grid-cols-2 gap-3">
                    <div class="rounded-2xl border border-white/10 bg-white/5 p-3.5 backdrop-blur">
                        <p class="flex items-center gap-1.5 text-[11px] font-medium text-teal-100/80">
                            <x-heroicon-o-arrow-down-left class="h-3.5 w-3.5 text-emerald-300" />
                            Pemasukan
                        </p>
                        <p class="money mt-1.5 text-lg font-bold text-emerald-300">{{ Money::format($summary['income']) }}</p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/5 p-3.5 backdrop-blur">
                        <p class="flex items-center gap-1.5 text-[11px] font-medium text-teal-100/80">
                            <x-heroicon-o-arrow-up-right class="h-3.5 w-3.5 text-rose-300" />
                            Pengeluaran
                        </p>
                        <p class="money mt-1.5 text-lg font-bold text-rose-200">{{ Money::format($summary['expense']) }}</p>
                    </div>
                </div>

                <div class="mt-5 flex flex-wrap items-center gap-2">
                    <a href="{{ route('transactions.create') }}" class="inline-flex items-center gap-1.5 rounded-xl bg-white px-3.5 py-2 text-xs font-bold text-teal-800 shadow-sm transition hover:bg-teal-50">
                        <x-heroicon-o-plus class="h-4 w-4" />
                        Catat transaksi
                    </a>
                    <a href="{{ route('transfers.create') }}" class="inline-flex items-center gap-1.5 rounded-xl border border-white/15 bg-white/10 px-3.5 py-2 text-xs font-bold text-white backdrop-blur transition hover:bg-white/20">
                        <x-heroicon-o-arrows-up-down class="h-4 w-4" />
                        Transfer
                    </a>
                    <a href="{{ route('reports.index', ['month' => $month]) }}" class="ml-auto hidden items-center gap-1 text-xs font-semibold text-teal-100 transition hover:text-white sm:inline-flex">
                        Lihat laporan
                        <x-heroicon-o-arrow-right class="h-3.5 w-3.5" />
                    </a>
                </div>
            </div>
        </section>

        {{-- Accounts --}}
        <section class="card card-pad">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-bold text-slate-900">Akun keuangan</h2>
                    <p class="mt-0.5 text-xs text-slate-500">Saldo terkini per akun</p>
                </div>
                <a href="{{ route('accounts.index') }}" class="text-xs font-semibold text-teal-700 hover:text-teal-800">Kelola</a>
            </div>

            @if ($summary['accounts']->isEmpty())
                <div class="mt-5 rounded-xl border border-dashed border-slate-300 p-5 text-center">
                    <p class="text-sm text-slate-500">Belum ada akun aktif.</p>
                    <a href="{{ route('accounts.create') }}" class="link mt-2 inline-block text-xs">Buat akun pertama</a>
                </div>
            @else
                <div class="mt-4 space-y-2.5">
                    @foreach ($summary['accounts']->take(4) as $account)
                        @php
                            $balance = $summary['account_balances'][$account->id] ?? '0.00';
                            $accountTone = match ($account->type->value) {
                                'cash' => 'bg-emerald-50 text-emerald-600',
                                'bank' => 'bg-sky-50 text-sky-600',
                                'ewallet' => 'bg-amber-50 text-amber-600',
                                'savings' => 'bg-violet-50 text-violet-600',
                                default => 'bg-slate-100 text-slate-600',
                            };
                        @endphp
                        <a href="{{ route('accounts.show', $account) }}" class="flex items-center gap-3 rounded-xl border border-slate-100 p-2.5 transition hover:border-teal-200 hover:bg-teal-50/30">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg {{ $accountTone }}">
                                <x-dynamic-component :component="'heroicon-o-' . $account->type->icon()" class="h-5 w-5" />
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-semibold text-slate-900">{{ $account->name }}</p>
                                <p class="text-[11px] text-slate-500">{{ $account->type->label() }}</p>
                            </div>
                            <p class="money text-sm font-bold text-slate-900">{{ Money::format($balance) }}</p>
                        </a>
                    @endforeach
                </div>

                @if ($summary['accounts']->count() > 4)
                    <a href="{{ route('accounts.index') }}" class="mt-3 block text-center text-xs font-semibold text-slate-500 hover:text-teal-700">
                        +{{ $summary['accounts']->count() - 4 }} akun lainnya
                    </a>
                @endif
            @endif
        </section>
    </div>

    {{-- Stat row --}}
    <div class="mt-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-stat-card label="Pemasukan bulan ini" :value="Money::format($summary['income'])" icon="arrow-down-left" tone="emerald" :hint="$monthLabel" href="{{ route('transactions.index', ['type' => 'income']) }}" />
        <x-stat-card label="Pengeluaran bulan ini" :value="Money::format($summary['expense'])" icon="arrow-up-right" tone="rose" :hint="$monthLabel" href="{{ route('transactions.index', ['type' => 'expense']) }}" />
        <x-stat-card label="Arus bersih" :value="Money::format($summary['net'])" icon="scale" tone="teal" hint="Pemasukan − pengeluaran" href="{{ route('reports.index', ['month' => $month]) }}" />
        <x-stat-card label="Anggaran aktif" :value="$summary['budgets']->count() . ' anggaran'" icon="chart-pie" tone="amber" hint="Lihat sisa anggaran" href="{{ route('budgets.index') }}" />
    </div>

    {{-- Chart & category --}}
    <div class="mt-5 grid gap-5 xl:grid-cols-[1.55fr_1fr]">
        <section class="card card-pad">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-sm font-bold text-slate-900">Arus harian</h2>
                    <p class="mt-0.5 text-xs text-slate-500">Pemasukan dan pengeluaran sepanjang {{ $monthLabel }}</p>
                </div>
                <div class="flex items-center gap-4 text-[11px] font-medium text-slate-500">
                    <span class="inline-flex items-center gap-1.5"><span class="h-2 w-2 rounded-full bg-teal-500"></span> Masuk</span>
                    <span class="inline-flex items-center gap-1.5"><span class="h-2 w-2 rounded-full bg-rose-400"></span> Keluar</span>
                </div>
            </div>

            <div class="mt-6 flex h-44 items-end gap-1 sm:gap-1.5">
                @foreach ($summary['daily_series']['labels'] as $index => $label)
                    @php
                        $incomeValue = $summary['daily_series']['income'][$index] ?? 0;
                        $expenseValue = $summary['daily_series']['expense'][$index] ?? 0;
                    @endphp
                    <div class="group relative flex h-full flex-1 items-end justify-center gap-px" title="{{ $label }} {{ $monthLabel }}: masuk {{ Money::format($incomeValue) }}, keluar {{ Money::format($expenseValue) }}">
                        <div class="w-full max-w-[6px] rounded-t bg-teal-500/80 transition group-hover:bg-teal-500" style="height: {{ max(2, ($incomeValue / $maxDaily) * 100) }}%"></div>
                        <div class="w-full max-w-[6px] rounded-t bg-rose-400/80 transition group-hover:bg-rose-500" style="height: {{ max(2, ($expenseValue / $maxDaily) * 100) }}%"></div>
                    </div>
                @endforeach
            </div>
            <div class="mt-2 flex justify-between text-[10px] font-medium text-slate-400">
                <span>1</span>
                <span>{{ $period->daysInMonth }}</span>
            </div>
        </section>

        <section class="card card-pad">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-bold text-slate-900">Pengeluaran per kategori</h2>
                    <p class="mt-0.5 text-xs text-slate-500">Ke mana uangmu pergi</p>
                </div>
                <a href="{{ route('reports.index', ['month' => $month]) }}" class="text-xs font-semibold text-teal-700 hover:text-teal-800">Detail</a>
            </div>

            @if ($expenseCategories->isEmpty())
                <div class="mt-6 flex flex-col items-center justify-center py-8 text-center">
                    <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                        <x-heroicon-o-chart-pie class="h-6 w-6" />
                    </span>
                    <p class="mt-3 text-sm font-semibold text-slate-700">Belum ada pengeluaran</p>
                    <p class="mt-1 text-xs text-slate-500">Catat pengeluaran pertamamu bulan ini.</p>
                    <a href="{{ route('transactions.create', ['type' => 'expense']) }}" class="btn-secondary btn-sm mt-4">Catat pengeluaran</a>
                </div>
            @else
                <div class="mt-4 flex items-center gap-5">
                    <div class="relative h-28 w-28 shrink-0">
                        <svg viewBox="0 0 100 100" class="h-28 w-28 -rotate-90">
                            <circle cx="50" cy="50" r="40" fill="none" stroke="#f1f5f9" stroke-width="12" />
                            @foreach ($expenseCategories->take(6) as $index => $category)
                                @php
                                    $percentage = $expenseTotal > 0 ? ((float) $category['total'] / $expenseTotal) : 0;
                                    $dash = $percentage * $circumference;
                                    $color = preg_match('/^#[0-9a-f]{6}$/i', (string) $category['color']) ? $category['color'] : $palette[$index % count($palette)];
                                @endphp
                                <circle cx="50" cy="50" r="40" fill="none"
                                        stroke="{{ $color }}"
                                        stroke-width="12"
                                        stroke-dasharray="{{ $dash }} {{ $circumference }}"
                                        stroke-dashoffset="{{ -$offset }}"
                                        stroke-linecap="round" />
                                @php $offset += $dash; @endphp
                            @endforeach
                        </svg>
                        <div class="absolute inset-0 flex flex-col items-center justify-center">
                            <span class="text-[10px] font-medium text-slate-400">Total</span>
                            <span class="money text-xs font-extrabold text-slate-900">{{ Money::format($summary['expense']) }}</span>
                        </div>
                    </div>
                    <div class="min-w-0 flex-1 space-y-2">
                        @foreach ($expenseCategories->take(5) as $index => $category)
                            @php
                                $percentage = $expenseTotal > 0 ? ((float) $category['total'] / $expenseTotal) * 100 : 0;
                                $color = preg_match('/^#[0-9a-f]{6}$/i', (string) $category['color']) ? $category['color'] : $palette[$index % count($palette)];
                            @endphp
                            <div class="flex items-center gap-2">
                                <span class="h-2 w-2 shrink-0 rounded-full" style="background-color: {{ $color }}"></span>
                                <p class="min-w-0 flex-1 truncate text-xs text-slate-600">{{ $category['name'] }}</p>
                                <p class="text-xs font-semibold text-slate-900">{{ number_format($percentage, 0) }}%</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </section>
    </div>

    {{-- Budgets & goals --}}
    <div class="mt-5 grid gap-5 xl:grid-cols-2">
        <section class="card card-pad">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-bold text-slate-900">Anggaran berjalan</h2>
                    <p class="mt-0.5 text-xs text-slate-500">Sisa uang untuk kategori pengeluaran</p>
                </div>
                <a href="{{ route('budgets.index') }}" class="text-xs font-semibold text-teal-700 hover:text-teal-800">Semua</a>
            </div>

            @if ($summary['budgets']->isEmpty())
                <div class="mt-5 rounded-xl border border-dashed border-slate-300 p-5 text-center">
                    <p class="text-sm text-slate-500">Belum ada anggaran untuk periode ini.</p>
                    <a href="{{ route('budgets.create') }}" class="link mt-2 inline-block text-xs">Buat anggaran</a>
                </div>
            @else
                <div class="mt-4 space-y-4">
                    @foreach ($summary['budgets']->take(4) as $row)
                        <div>
                            <div class="flex items-center justify-between gap-2">
                                <p class="truncate text-xs font-semibold text-slate-700">{{ $row['budget']->category?->name ?? 'Semua pengeluaran' }}</p>
                                <p class="text-[11px] font-medium {{ $row['over'] ? 'text-rose-600' : 'text-slate-500' }}">
                                    {{ Money::format($row['spent']) }} / {{ Money::format($row['budget']->amount) }}
                                </p>
                            </div>
                            <div class="progress-track mt-1.5">
                                <div class="progress-fill {{ $row['over'] ? 'bg-rose-500' : ($row['percentage'] >= 80 ? 'bg-amber-500' : 'bg-teal-500') }}" style="width: {{ min(100, $row['percentage']) }}%"></div>
                            </div>
                            <p class="mt-1 text-[10px] {{ $row['over'] ? 'font-semibold text-rose-600' : 'text-slate-400' }}">
                                @if ($row['over'])
                                    Melebihi anggaran {{ Money::format(ltrim($row['remaining'], '-')) }}
                                @else
                                    Tersisa {{ Money::format($row['remaining']) }}
                                @endif
                            </p>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        <section class="card card-pad">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-bold text-slate-900">Target tabungan</h2>
                    <p class="mt-0.5 text-xs text-slate-500">Sedikit demi sedikit, pasti sampai</p>
                </div>
                <a href="{{ route('goals.index') }}" class="text-xs font-semibold text-teal-700 hover:text-teal-800">Semua</a>
            </div>

            @if ($summary['goals']->isEmpty())
                <div class="mt-5 rounded-xl border border-dashed border-slate-300 p-5 text-center">
                    <p class="text-sm text-slate-500">Belum ada target aktif.</p>
                    <a href="{{ route('goals.create') }}" class="link mt-2 inline-block text-xs">Buat target</a>
                </div>
            @else
                <div class="mt-4 space-y-4">
                    @foreach ($summary['goals'] as $goal)
                        @php $percentage = $goal->progressPercentage(); @endphp
                        <a href="{{ route('goals.show', $goal) }}" class="block rounded-xl border border-slate-100 p-3 transition hover:border-teal-200 hover:bg-teal-50/30">
                            <div class="flex items-center justify-between gap-2">
                                <p class="truncate text-xs font-semibold text-slate-700">{{ $goal->name }}</p>
                                <span class="badge-brand">{{ number_format($percentage, 0) }}%</span>
                            </div>
                            <div class="progress-track mt-2">
                                <div class="progress-fill bg-gradient-to-r from-teal-500 to-emerald-500" style="width: {{ min(100, $percentage) }}%"></div>
                            </div>
                            <p class="mt-1 text-[10px] text-slate-400">{{ Money::format($goal->contributedTotal()) }} dari {{ Money::format($goal->target_amount) }}</p>
                        </a>
                    @endforeach
                </div>
            @endif
        </section>
    </div>

    {{-- Recent transactions --}}
    <section class="card card-pad mt-5">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-sm font-bold text-slate-900">Transaksi terbaru</h2>
                <p class="mt-0.5 text-xs text-slate-500">Aktivitas terakhir di semua akun</p>
            </div>
            <a href="{{ route('transactions.index') }}" class="text-xs font-semibold text-teal-700 hover:text-teal-800">Lihat semua</a>
        </div>

        @if ($summary['recent_transactions']->isEmpty())
            <div class="mt-4">
                <x-empty-state icon="receipt-percent" title="Belum ada transaksi" description="Mulai catat pemasukan atau pengeluaran pertamamu untuk melihat ringkasan di sini.">
                    <a href="{{ route('transactions.create') }}" class="btn-primary btn-sm">
                        <x-heroicon-o-plus class="h-4 w-4" />
                        Catat transaksi
                    </a>
                </x-empty-state>
            </div>
        @else
            <div class="mt-3 divide-y divide-slate-100">
                @foreach ($summary['recent_transactions'] as $transaction)
                    <x-transaction-item :transaction="$transaction" class="-mx-2" />
                @endforeach
            </div>
        @endif
    </section>
@endsection
