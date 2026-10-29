@extends('layouts.app')

@section('title', 'Laporan Keuangan')
@section('subtitle', 'Lihat pola, bukan sekadar angka.')

@php
    use App\Support\Money;
    use Carbon\Carbon;

    $period = Carbon::createFromFormat('Y-m', $month);
    $maxTrend = max(1, ...$trend['income'], ...$trend['expense']);
    $expenseTotal = (float) collect($expenseCategories)->sum(fn ($category) => (float) $category['total']);
    $incomeTotal = (float) collect($incomeCategories)->sum(fn ($category) => (float) $category['total']);
    $dailyMax = max(1, ...$summary['daily_series']['income'], ...$summary['daily_series']['expense']);
    $palette = ['#0d9488', '#f59e0b', '#0ea5e9', '#ec4899', '#8b5cf6', '#f43f5e', '#84cc16'];
    $savingsRate = (float) $summary['income'] > 0 ? ((float) $summary['net'] / (float) $summary['income']) * 100 : 0;
@endphp

@section('content')
    <div class="mb-5 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
        <div class="flex items-center gap-2">
            <form method="GET" action="{{ route('reports.index') }}">
                <input type="month" name="month" value="{{ $month }}" onchange="this.form.submit()" class="input py-2 text-xs font-semibold">
            </form>
            <span class="text-xs text-slate-400">·</span>
            <span class="text-xs font-medium text-slate-500">{{ $monthLabel }}</span>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="window.print()" class="btn-secondary btn-sm">
                <x-heroicon-o-printer class="h-4 w-4" />
                Cetak
            </button>
            <a href="{{ route('reports.export', ['month' => $month]) }}" class="btn-primary btn-sm">
                <x-heroicon-o-arrow-down-tray class="h-4 w-4" />
                Ekspor CSV
            </a>
        </div>
    </div>

    <section class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-slate-900 via-slate-900 to-teal-950 p-6 text-white shadow-xl shadow-slate-900/10 sm:p-7">
        <div class="hero-grid absolute inset-0 opacity-40"></div>
        <div class="absolute -right-16 -top-24 h-64 w-64 rounded-full bg-teal-500/20 blur-3xl"></div>
        <div class="relative grid gap-6 sm:grid-cols-3 sm:items-end">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Arus bersih {{ $monthLabel }}</p>
                <p class="money mt-2 text-3xl font-extrabold tracking-tight sm:text-4xl">{{ Money::format($summary['net']) }}</p>
                <p class="mt-1 text-xs text-slate-400">Dari pemasukan {{ Money::format($summary['income']) }} dan pengeluaran {{ Money::format($summary['expense']) }}</p>
            </div>
            <div class="grid grid-cols-2 gap-3 sm:col-span-2">
                <div class="rounded-xl border border-white/10 bg-white/5 p-3.5 backdrop-blur">
                    <p class="flex items-center gap-1.5 text-[10px] font-medium text-slate-400"><x-heroicon-o-arrow-down-left class="h-3.5 w-3.5 text-emerald-300" /> Pemasukan</p>
                    <p class="money mt-1.5 text-base font-bold text-emerald-300">{{ Money::format($summary['income']) }}</p>
                </div>
                <div class="rounded-xl border border-white/10 bg-white/5 p-3.5 backdrop-blur">
                    <p class="flex items-center gap-1.5 text-[10px] font-medium text-slate-400"><x-heroicon-o-arrow-up-right class="h-3.5 w-3.5 text-rose-300" /> Pengeluaran</p>
                    <p class="money mt-1.5 text-base font-bold text-rose-200">{{ Money::format($summary['expense']) }}</p>
                </div>
                <div class="rounded-xl border border-white/10 bg-white/5 p-3.5 backdrop-blur">
                    <p class="text-[10px] font-medium text-slate-400">Rasio tabungan</p>
                    <p class="mt-1.5 text-base font-bold text-white">{{ number_format($savingsRate, 1) }}%</p>
                </div>
                <div class="rounded-xl border border-white/10 bg-white/5 p-3.5 backdrop-blur">
                    <p class="text-[10px] font-medium text-slate-400">Transaksi bulan ini</p>
                    <p class="mt-1.5 text-base font-bold text-white">{{ number_format($summary['transaction_count'], 0, ',', '.') }}</p>
                </div>
            </div>
        </div>
    </section>

    <div class="mt-5 grid gap-5 xl:grid-cols-[1.55fr_1fr]">
        {{-- Trend --}}
        <section class="card card-pad">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-sm font-bold text-slate-900">Tren 6 bulan</h2>
                    <p class="mt-0.5 text-xs text-slate-500">Pemasukan dan pengeluaran per bulan</p>
                </div>
                <div class="flex items-center gap-4 text-[11px] font-medium text-slate-500">
                    <span class="inline-flex items-center gap-1.5"><span class="h-2 w-2 rounded-full bg-teal-500"></span> Masuk</span>
                    <span class="inline-flex items-center gap-1.5"><span class="h-2 w-2 rounded-full bg-rose-400"></span> Keluar</span>
                </div>
            </div>

            <div class="mt-6 flex h-52 items-end gap-3 sm:gap-5">
                @foreach ($trend['labels'] as $index => $label)
                    @php
                        $incomeValue = $trend['income'][$index] ?? 0;
                        $expenseValue = $trend['expense'][$index] ?? 0;
                    @endphp
                    <div class="flex h-full flex-1 flex-col items-center justify-end gap-2">
                        <div class="flex h-full w-full items-end justify-center gap-1 sm:gap-1.5" title="{{ $label }}: masuk {{ Money::format($incomeValue) }}, keluar {{ Money::format($expenseValue) }}">
                            <div class="w-full max-w-[18px] rounded-t-md bg-teal-500/90 transition hover:bg-teal-600" style="height: {{ max(3, ($incomeValue / $maxTrend) * 100) }}%"></div>
                            <div class="w-full max-w-[18px] rounded-t-md bg-rose-400/90 transition hover:bg-rose-500" style="height: {{ max(3, ($expenseValue / $maxTrend) * 100) }}%"></div>
                        </div>
                        <span class="text-[10px] font-medium text-slate-500">{{ $label }}</span>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- Daily --}}
        <section class="card card-pad">
            <div>
                <h2 class="text-sm font-bold text-slate-900">Arus harian</h2>
                <p class="mt-0.5 text-xs text-slate-500">Pola pengeluaran sepanjang {{ $monthLabel }}</p>
            </div>
            <div class="mt-6 flex h-40 items-end gap-1">
                @foreach ($summary['daily_series']['labels'] as $index => $label)
                    @php
                        $incomeValue = $summary['daily_series']['income'][$index] ?? 0;
                        $expenseValue = $summary['daily_series']['expense'][$index] ?? 0;
                    @endphp
                    <div class="flex h-full flex-1 items-end justify-center gap-px" title="{{ $label }}: masuk {{ Money::format($incomeValue) }}, keluar {{ Money::format($expenseValue) }}">
                        <div class="w-full max-w-[5px] rounded-t bg-teal-500/70" style="height: {{ max(2, ($incomeValue / $dailyMax) * 100) }}%"></div>
                        <div class="w-full max-w-[5px] rounded-t bg-rose-400/70" style="height: {{ max(2, ($expenseValue / $dailyMax) * 100) }}%"></div>
                    </div>
                @endforeach
            </div>
            <p class="mt-2 text-[10px] text-slate-400">1 – {{ $period->daysInMonth }} {{ $period->translatedFormat('F') }}</p>
        </section>
    </div>

    <div class="mt-5 grid gap-5 xl:grid-cols-2">
        {{-- Expense categories --}}
        <section class="card card-pad">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-bold text-slate-900">Pengeluaran per kategori</h2>
                    <p class="mt-0.5 text-xs text-slate-500">Total {{ Money::format($summary['expense']) }}</p>
                </div>
                <span class="badge-danger">{{ $expenseCategories->count() }} kategori</span>
            </div>

            @if ($expenseCategories->isEmpty())
                <div class="mt-4">
                    <x-empty-state icon="chart-pie" title="Belum ada data pengeluaran" description="Catat pengeluaran bulan ini untuk melihat rinciannya." />
                </div>
            @else
                <div class="mt-5 space-y-4">
                    @foreach ($expenseCategories as $index => $category)
                        @php
                            $percentage = $expenseTotal > 0 ? ((float) $category['total'] / $expenseTotal) * 100 : 0;
                            $color = preg_match('/^#[0-9a-f]{6}$/i', (string) $category['color']) ? $category['color'] : $palette[$index % count($palette)];
                        @endphp
                        <div>
                            <div class="flex items-center justify-between gap-2">
                                <span class="inline-flex min-w-0 items-center gap-2 text-xs font-semibold text-slate-700">
                                    <span class="h-2.5 w-2.5 shrink-0 rounded-full" style="background-color: {{ $color }}"></span>
                                    <span class="truncate">{{ $category['name'] }}</span>
                                </span>
                                <span class="money text-xs font-bold text-slate-900">{{ Money::format($category['total']) }}</span>
                            </div>
                            <div class="mt-1.5 flex items-center gap-2">
                                <div class="h-1.5 flex-1 overflow-hidden rounded-full bg-slate-100">
                                    <div class="h-full rounded-full" style="width: {{ $percentage }}%; background-color: {{ $color }}"></div>
                                </div>
                                <span class="w-10 text-right text-[10px] font-medium text-slate-400">{{ number_format($percentage, 0) }}%</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        {{-- Income categories --}}
        <section class="card card-pad">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-bold text-slate-900">Pemasukan per kategori</h2>
                    <p class="mt-0.5 text-xs text-slate-500">Total {{ Money::format($summary['income']) }}</p>
                </div>
                <span class="badge-success">{{ $incomeCategories->count() }} kategori</span>
            </div>

            @if ($incomeCategories->isEmpty())
                <div class="mt-4">
                    <x-empty-state icon="banknotes" title="Belum ada data pemasukan" description="Catat pemasukan bulan ini untuk melihat sumbernya." />
                </div>
            @else
                <div class="mt-5 space-y-4">
                    @foreach ($incomeCategories as $index => $category)
                        @php
                            $percentage = $incomeTotal > 0 ? ((float) $category['total'] / $incomeTotal) * 100 : 0;
                            $color = preg_match('/^#[0-9a-f]{6}$/i', (string) $category['color']) ? $category['color'] : $palette[$index % count($palette)];
                        @endphp
                        <div>
                            <div class="flex items-center justify-between gap-2">
                                <span class="inline-flex min-w-0 items-center gap-2 text-xs font-semibold text-slate-700">
                                    <span class="h-2.5 w-2.5 shrink-0 rounded-full" style="background-color: {{ $color }}"></span>
                                    <span class="truncate">{{ $category['name'] }}</span>
                                </span>
                                <span class="money text-xs font-bold text-slate-900">{{ Money::format($category['total']) }}</span>
                            </div>
                            <div class="mt-1.5 flex items-center gap-2">
                                <div class="h-1.5 flex-1 overflow-hidden rounded-full bg-slate-100">
                                    <div class="h-full rounded-full" style="width: {{ $percentage }}%; background-color: {{ $color }}"></div>
                                </div>
                                <span class="w-10 text-right text-[10px] font-medium text-slate-400">{{ number_format($percentage, 0) }}%</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>
    </div>

    @if ($summary['budgets']->isNotEmpty())
        <section class="card card-pad mt-5">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-bold text-slate-900">Kinerja anggaran</h2>
                    <p class="mt-0.5 text-xs text-slate-500">Anggaran yang berjalan pada periode ini</p>
                </div>
                <a href="{{ route('budgets.index') }}" class="text-xs font-semibold text-teal-700 hover:text-teal-800">Kelola</a>
            </div>
            <div class="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($summary['budgets'] as $row)
                    <div class="rounded-2xl border border-slate-100 p-4">
                        <div class="flex items-center justify-between gap-2">
                            <p class="truncate text-xs font-semibold text-slate-700">{{ $row['budget']->category?->name ?? 'Semua pengeluaran' }}</p>
                            <span class="{{ $row['over'] ? 'badge-danger' : ($row['percentage'] >= 80 ? 'badge-warning' : 'badge-success') }}">{{ number_format($row['percentage'], 0) }}%</span>
                        </div>
                        <div class="progress-track mt-2">
                            <div class="progress-fill {{ $row['over'] ? 'bg-rose-500' : ($row['percentage'] >= 80 ? 'bg-amber-500' : 'bg-teal-500') }}" style="width: {{ min(100, $row['percentage']) }}%"></div>
                        </div>
                        <p class="mt-1.5 text-[10px] text-slate-500">{{ Money::format($row['spent']) }} dari {{ Money::format($row['budget']->amount) }}</p>
                    </div>
                @endforeach
            </div>
        </section>
    @endif
@endsection
