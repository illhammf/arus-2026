@extends('layouts.app')

@section('title', 'Target Tabungan')
@section('subtitle', 'Ubah impian menjadi langkah kecil yang bisa kamu rayakan.')

@php
    use App\Enums\GoalStatus;
    use App\Support\Money;

    $activeGoals = $goals->where('status', GoalStatus::Active);
    $finishedGoals = $goals->where('status', '!=', GoalStatus::Active);
    $totalTarget = Money::add(...array_values($goals->map(fn ($goal) => $goal->target_amount)->all()));
    $totalSaved = Money::add(...array_values($goals->map(fn ($goal) => $goal->contributedTotal())->all()));
@endphp

@section('content')
    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-2 text-xs text-slate-500">
            <x-heroicon-o-information-circle class="h-4 w-4 text-slate-400" />
            Catat kontribusi kapan saja dan lihat progresnya bertambah.
        </div>
        <a href="{{ route('goals.create') }}" class="btn-primary">
            <x-heroicon-o-plus class="h-4 w-4" />
            Buat Target
        </a>
    </div>

    @if ($goals->isEmpty())
        <x-empty-state icon="flag" title="Belum ada target tabungan" description="Mulai dari hal yang paling kamu inginkan. Dana darurat, liburan, atau gadget baru — Arus bantu melacaknya.">
            <a href="{{ route('goals.create') }}" class="btn-primary">
                <x-heroicon-o-plus class="h-4 w-4" />
                Buat Target Pertama
            </a>
        </x-empty-state>
    @else
        <section class="mb-6 grid gap-4 sm:grid-cols-3">
            <div class="card card-pad">
                <p class="text-xs font-medium text-slate-500">Total target</p>
                <p class="money mt-1.5 text-xl font-extrabold text-slate-900">{{ Money::format($totalTarget) }}</p>
                <p class="mt-1 text-[11px] text-slate-400">{{ $goals->count() }} target</p>
            </div>
            <div class="card card-pad">
                <p class="text-xs font-medium text-slate-500">Sudah terkumpul</p>
                <p class="money mt-1.5 text-xl font-extrabold text-emerald-600">{{ Money::format($totalSaved) }}</p>
                <p class="mt-1 text-[11px] text-slate-400">{{ $activeGoals->count() }} target berjalan</p>
            </div>
            <div class="card card-pad">
                <p class="text-xs font-medium text-slate-500">Tercapai</p>
                <p class="mt-1.5 text-xl font-extrabold text-slate-900">{{ $finishedGoals->where('status', GoalStatus::Completed)->count() }} target</p>
                <p class="mt-1 text-[11px] text-slate-400">Teruskan langkah kecilmu</p>
            </div>
        </section>

        @if ($activeGoals->isNotEmpty())
            <h2 class="section-title mb-3">Sedang berjalan</h2>
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($activeGoals as $goal)
                    @php
                        $percentage = $goal->progressPercentage();
                        $remaining = $goal->remaining();
                        $circumference = 2 * M_PI * 34;
                        $dash = ($percentage / 100) * $circumference;
                    @endphp
                    <article class="card card-hover group relative overflow-hidden p-5">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex min-w-0 items-center gap-3">
                                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-teal-50 text-teal-600">
                                    <x-heroicon-o-flag class="h-5 w-5" />
                                </span>
                                <div class="min-w-0">
                                    <h3 class="truncate text-sm font-bold text-slate-900">{{ $goal->name }}</h3>
                                    <p class="mt-0.5 text-[11px] text-slate-500">
                                        {{ $goal->target_date ? $goal->target_date->translatedFormat('d M Y') : 'Tanpa tenggat' }}
                                    </p>
                                </div>
                            </div>
                            <span class="badge-brand">{{ number_format($percentage, 0) }}%</span>
                        </div>

                        <div class="mt-5 flex items-center gap-4">
                            <div class="relative h-20 w-20 shrink-0">
                                <svg viewBox="0 0 80 80" class="h-20 w-20 -rotate-90">
                                    <circle cx="40" cy="40" r="34" fill="none" stroke="#f1f5f9" stroke-width="7" />
                                    <circle cx="40" cy="40" r="34" fill="none" stroke="#0d9488" stroke-width="7"
                                            stroke-linecap="round" stroke-dasharray="{{ $dash }} {{ $circumference }}" />
                                </svg>
                                <div class="absolute inset-0 flex flex-col items-center justify-center">
                                    <span class="text-sm font-extrabold text-slate-900">{{ number_format($percentage, 0) }}%</span>
                                    <span class="text-[9px] text-slate-400">tercapai</span>
                                </div>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-[11px] text-slate-400">Terkumpul</p>
                                <p class="money text-sm font-bold text-slate-900">{{ Money::format($goal->contributedTotal()) }}</p>
                                <p class="mt-1.5 text-[11px] text-slate-400">Dari {{ Money::format($goal->target_amount) }}</p>
                            </div>
                        </div>

                        <div class="mt-4 flex items-center justify-between border-t border-slate-100 pt-3">
                            <p class="text-[11px] text-slate-500">Sisa <span class="font-semibold text-slate-700">{{ Money::format($remaining) }}</span></p>
                            <a href="{{ route('goals.show', $goal) }}" class="inline-flex items-center gap-1 text-xs font-semibold text-teal-700 hover:text-teal-800">
                                Buka target
                                <x-heroicon-o-arrow-right class="h-3.5 w-3.5" />
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif

        @if ($finishedGoals->isNotEmpty())
            <h2 class="section-title mb-3 {{ $activeGoals->isNotEmpty() ? 'mt-8' : '' }}">Selesai & dibatalkan</h2>
            <div class="table-wrap">
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Target</th>
                                <th>Tenggat</th>
                                <th class="text-right">Terkumpul</th>
                                <th>Status</th>
                                <th class="text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @foreach ($finishedGoals as $goal)
                                <tr>
                                    <td class="font-semibold text-slate-900">{{ $goal->name }}</td>
                                    <td class="text-xs text-slate-500">{{ $goal->target_date?->translatedFormat('d M Y') ?? '—' }}</td>
                                    <td class="money text-right text-sm">{{ Money::format($goal->contributedTotal()) }}</td>
                                    <td><span class="{{ $goal->status === GoalStatus::Completed ? 'badge-success' : 'badge-neutral' }}">{{ $goal->status->label() }}</span></td>
                                    <td class="text-right">
                                        <a href="{{ route('goals.show', $goal) }}" class="btn-icon" aria-label="Detail">
                                            <x-heroicon-o-eye class="h-4 w-4" />
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
