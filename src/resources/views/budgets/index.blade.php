@extends('layouts.app')

@section('title', 'Anggaran')
@section('subtitle', 'Batas pengeluaran yang membantumu tetap tenang.')

@php
    use App\Enums\BudgetPeriod;
    use App\Support\Money;

    $activeBudgets = $budgets->filter(fn ($budget) => $budget->period_end->isToday() || $budget->period_end->isFuture());
    $pastBudgets = $budgets->reject(fn ($budget) => $budget->period_end->isToday() || $budget->period_end->isFuture());
@endphp

@section('content')
    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-2 text-xs text-slate-500">
            <x-heroicon-o-information-circle class="h-4 w-4 text-slate-400" />
            Anggaran otomatis menghitung pengeluaran pada periode yang dipilih.
        </div>
        <a href="{{ route('budgets.create') }}" class="btn-primary">
            <x-heroicon-o-plus class="h-4 w-4" />
            Buat Anggaran
        </a>
    </div>

    @if ($budgets->isEmpty())
        <x-empty-state icon="chart-pie" title="Belum ada anggaran" description="Tetapkan batas pengeluaran bulanan atau tahunan. Arus akan menghitung sisa anggaran secara otomatis.">
            <a href="{{ route('budgets.create') }}" class="btn-primary">
                <x-heroicon-o-plus class="h-4 w-4" />
                Buat Anggaran Pertama
            </a>
        </x-empty-state>
    @else
        @if ($activeBudgets->isNotEmpty())
            <h2 class="section-title mb-3">Anggaran berjalan</h2>
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($activeBudgets as $budget)
                    @php
                        $spent = $budget->spent();
                        $remaining = $budget->remaining();
                        $percentage = $budget->usagePercentage();
                        $over = $budget->isOverBudget();
                    @endphp
                    <article class="card card-hover p-5">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex min-w-0 items-center gap-3">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $over ? 'bg-rose-50 text-rose-600' : 'bg-amber-50 text-amber-600' }}">
                                    <x-heroicon-o-chart-pie class="h-5 w-5" />
                                </span>
                                <div class="min-w-0">
                                    <h3 class="truncate text-sm font-bold text-slate-900">{{ $budget->category?->name ?? 'Semua pengeluaran' }}</h3>
                                    <p class="mt-0.5 text-[11px] text-slate-500">
                                        {{ $budget->period_type->label() }} · {{ $budget->period_start->translatedFormat('d M') }} – {{ $budget->period_end->translatedFormat('d M Y') }}
                                    </p>
                                </div>
                            </div>
                            <span class="{{ $over ? 'badge-danger' : ($percentage >= 80 ? 'badge-warning' : 'badge-success') }}">
                                {{ $over ? 'Lewat' : number_format($percentage, 0) . '%' }}
                            </span>
                        </div>

                        <div class="mt-5 flex items-end justify-between gap-3">
                            <div>
                                <p class="text-[11px] text-slate-400">Terpakai</p>
                                <p class="money text-lg font-extrabold text-slate-900">{{ Money::format($spent) }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-[11px] text-slate-400">Anggaran</p>
                                <p class="money text-sm font-bold text-slate-600">{{ Money::format($budget->amount) }}</p>
                            </div>
                        </div>
                        <div class="progress-track mt-2 h-2.5">
                            <div class="progress-fill {{ $over ? 'bg-rose-500' : ($percentage >= 80 ? 'bg-amber-500' : 'bg-teal-500') }}" style="width: {{ min(100, $percentage) }}%"></div>
                        </div>
                        <div class="mt-2 flex items-center justify-between gap-2">
                            <p class="text-[11px] {{ $over ? 'font-semibold text-rose-600' : 'text-slate-500' }}">
                                @if ($over)
                                    Lebih {{ Money::format(ltrim($remaining, '-')) }}
                                @else
                                    Tersisa {{ Money::format($remaining) }}
                                @endif
                            </p>
                            <div class="flex items-center gap-1">
                                <a href="{{ route('budgets.edit', $budget) }}" class="btn-icon h-8 w-8" aria-label="Edit anggaran">
                                    <x-heroicon-o-pencil-square class="h-4 w-4" />
                                </a>
                                <form method="POST" action="{{ route('budgets.destroy', $budget) }}" data-confirm="Hapus anggaran ini?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-icon h-8 w-8 hover:bg-rose-50 hover:text-rose-600" aria-label="Hapus anggaran">
                                        <x-heroicon-o-trash class="h-4 w-4" />
                                    </button>
                                </form>
                            </div>
                        </div>
                        @if ($budget->notes)
                            <p class="mt-3 border-t border-slate-100 pt-2.5 text-[11px] leading-relaxed text-slate-500">{{ $budget->notes }}</p>
                        @endif
                    </article>
                @endforeach
            </div>
        @endif

        @if ($pastBudgets->isNotEmpty())
            <h2 class="section-title mb-3 {{ $activeBudgets->isNotEmpty() ? 'mt-8' : '' }}">Periode lalu</h2>
            <div class="table-wrap">
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Kategori</th>
                                <th>Periode</th>
                                <th class="text-right">Anggaran</th>
                                <th class="text-right">Terpakai</th>
                                <th>Hasil</th>
                                <th class="text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @foreach ($pastBudgets as $budget)
                                <tr>
                                    <td class="font-semibold text-slate-900">{{ $budget->category?->name ?? 'Semua pengeluaran' }}</td>
                                    <td class="text-xs text-slate-500">{{ $budget->period_start->translatedFormat('d M Y') }} – {{ $budget->period_end->translatedFormat('d M Y') }}</td>
                                    <td class="money text-right text-sm">{{ Money::format($budget->amount) }}</td>
                                    <td class="money text-right text-sm">{{ Money::format($budget->spent()) }}</td>
                                    <td>
                                        <span class="{{ $budget->isOverBudget() ? 'badge-danger' : 'badge-success' }}">
                                            {{ $budget->isOverBudget() ? 'Melebihi' : 'Tercapai' }}
                                        </span>
                                    </td>
                                    <td class="text-right">
                                        <a href="{{ route('budgets.edit', $budget) }}" class="btn-icon" aria-label="Edit">
                                            <x-heroicon-o-pencil-square class="h-4 w-4" />
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    @endif
@endsection
