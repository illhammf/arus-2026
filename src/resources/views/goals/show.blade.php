@extends('layouts.app')

@section('title', $goal->name)
@section('subtitle', 'Target tabungan · ' . $goal->status->label())

@php
    use App\Enums\GoalStatus;
    use App\Support\Money;

    $circumference = 2 * M_PI * 52;
    $dash = ($percentage / 100) * $circumference;
    $tone = match ($goal->status) {
        GoalStatus::Completed => ['gradient' => 'from-emerald-600 to-teal-800', 'badge' => 'bg-emerald-400/15 text-emerald-300'],
        GoalStatus::Cancelled => ['gradient' => 'from-slate-700 to-slate-900', 'badge' => 'bg-white/10 text-slate-300'],
        default => ['gradient' => 'from-violet-700 to-slate-900', 'badge' => 'bg-violet-400/15 text-violet-200'],
    };
@endphp

@section('content')
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('goals.index') }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-slate-500 transition hover:text-slate-900">
            <x-heroicon-o-arrow-left class="h-4 w-4" />
            Semua Target
        </a>
        <a href="{{ route('goals.edit', $goal) }}" class="btn-secondary btn-sm">
            <x-heroicon-o-pencil-square class="h-4 w-4" />
            Edit Target
        </a>
    </div>

    <section class="relative overflow-hidden rounded-3xl bg-gradient-to-br {{ $tone['gradient'] }} p-6 text-white shadow-xl shadow-slate-900/10 sm:p-8">
        <div class="hero-grid absolute inset-0 opacity-30"></div>
        <div class="absolute -right-20 -top-24 h-64 w-64 rounded-full bg-white/10 blur-3xl"></div>
        <div class="relative flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-5">
                <div class="relative h-32 w-32 shrink-0">
                    <svg viewBox="0 0 120 120" class="h-32 w-32 -rotate-90">
                        <circle cx="60" cy="60" r="52" fill="none" stroke="rgba(255,255,255,0.12)" stroke-width="9" />
                        <circle cx="60" cy="60" r="52" fill="none" stroke="rgba(255,255,255,0.9)" stroke-width="9"
                                stroke-linecap="round" stroke-dasharray="{{ $dash }} {{ $circumference }}" />
                    </svg>
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <span class="text-2xl font-extrabold">{{ number_format($percentage, 0) }}%</span>
                        <span class="text-[10px] font-medium text-white/60">tercapai</span>
                    </div>
                </div>
                <div>
                    <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider {{ $tone['badge'] }}">
                        <x-heroicon-o-flag class="h-3 w-3" />
                        {{ $goal->status->label() }}
                    </span>
                    <h2 class="mt-2 text-2xl font-extrabold tracking-tight sm:text-3xl">{{ $goal->name }}</h2>
                    @if ($goal->target_date)
                        <p class="mt-1 flex items-center gap-1.5 text-sm text-white/70">
                            <x-heroicon-o-calendar-days class="h-4 w-4" />
                            Tenggat {{ $goal->target_date->translatedFormat('d F Y') }}
                        </p>
                    @else
                        <p class="mt-1 text-sm text-white/70">Tanpa tenggat waktu</p>
                    @endif
                    @if ($goal->linkedAccount)
                        <p class="mt-1 flex items-center gap-1.5 text-xs text-white/60">
                            <x-heroicon-o-wallet class="h-3.5 w-3.5" />
                            {{ $goal->linkedAccount->name }}
                        </p>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3 sm:text-right">
                <div class="rounded-xl border border-white/10 bg-white/5 p-3 backdrop-blur">
                    <p class="text-[10px] font-medium text-white/60">Terkumpul</p>
                    <p class="money mt-1 text-sm font-bold">{{ Money::format($contributed) }}</p>
                </div>
                <div class="rounded-xl border border-white/10 bg-white/5 p-3 backdrop-blur">
                    <p class="text-[10px] font-medium text-white/60">Target</p>
                    <p class="money mt-1 text-sm font-bold">{{ Money::format($goal->target_amount) }}</p>
                </div>
                <div class="col-span-2 rounded-xl border border-white/10 bg-white/5 p-3 backdrop-blur">
                    <p class="text-[10px] font-medium text-white/60">Sisa yang perlu dikumpulkan</p>
                    <p class="money mt-1 text-base font-extrabold">{{ Money::format($remaining) }}</p>
                </div>
            </div>
        </div>
    </section>

    <div class="mt-5 grid gap-5 xl:grid-cols-[1.35fr_1fr] xl:items-start">
        <section class="card card-pad">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-bold text-slate-900">Riwayat kontribusi</h2>
                    <p class="mt-0.5 text-xs text-slate-500">{{ $goal->contributions->count() }} kontribusi tercatat</p>
                </div>
                <span class="badge-brand">{{ Money::format($contributed) }}</span>
            </div>

            @if ($goal->contributions->isEmpty())
                <div class="mt-4">
                    <x-empty-state icon="banknotes" title="Belum ada kontribusi" description="Tambahkan kontribusi pertama untuk mulai melihat progres target ini." />
                </div>
            @else
                <div class="mt-4 space-y-1">
                    @foreach ($goal->contributions as $contribution)
                        <div class="group flex items-center gap-3 rounded-xl px-2 py-2.5 transition hover:bg-slate-50">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600">
                                <x-heroicon-o-plus class="h-4 w-4" />
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-semibold text-slate-900">{{ Money::format($contribution->amount) }}</p>
                                <p class="mt-0.5 text-[11px] text-slate-500">{{ $contribution->contributed_at->translatedFormat('d M Y · H:i') }}{{ $contribution->notes ? ' · ' . $contribution->notes : '' }}</p>
                            </div>
                            <form method="POST" action="{{ route('goals.contributions.destroy', [$goal, $contribution]) }}" data-confirm="Hapus kontribusi ini?">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-icon opacity-0 transition group-hover:opacity-100 hover:bg-rose-50 hover:text-rose-600" aria-label="Hapus kontribusi">
                                    <x-heroicon-o-trash class="h-4 w-4" />
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        <aside class="space-y-4">
            <div class="card card-pad">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-teal-50 text-teal-600">
                        <x-heroicon-o-plus class="h-5 w-5" />
                    </span>
                    <div>
                        <h2 class="text-sm font-bold text-slate-900">Tambah kontribusi</h2>
                        <p class="text-xs text-slate-500">Sisihkan sedikit untuk target ini.</p>
                    </div>
                </div>

                @if ($goal->status === GoalStatus::Cancelled)
                    <p class="mt-4 rounded-xl bg-slate-50 p-3 text-xs text-slate-500">Target ini dibatalkan. Aktifkan kembali melalui edit target untuk menambah kontribusi.</p>
                @else
                    <form method="POST" action="{{ route('goals.contributions.store', $goal) }}" class="mt-4 space-y-3">
                        @csrf
                        <div>
                            <label for="amount" class="label">Nominal</label>
                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-sm font-semibold text-slate-400">Rp</span>
                                <input id="amount" name="amount" type="text" inputmode="numeric" required data-money-input
                                       value="{{ old('amount') }}" placeholder="0"
                                       class="input pl-11 text-right money @error('amount') input-error @enderror">
                            </div>
                            @error('amount')
                                <p class="error-text">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="contributed_at" class="label">Tanggal</label>
                            <input id="contributed_at" name="contributed_at" type="datetime-local" required
                                   value="{{ old('contributed_at', now()->format('Y-m-d\TH:i')) }}"
                                   class="input @error('contributed_at') input-error @enderror">
                            @error('contributed_at')
                                <p class="error-text">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="contribution_notes" class="label">Catatan <span class="font-normal text-slate-400">(opsional)</span></label>
                            <input id="contribution_notes" name="notes" type="text" maxlength="1000" value="{{ old('notes') }}"
                                   placeholder="Contoh: sisihan gaji"
                                   class="input @error('notes') input-error @enderror">
                            @error('notes')
                                <p class="error-text">{{ $message }}</p>
                            @enderror
                        </div>
                        <button type="submit" class="btn-primary w-full">
                            <x-heroicon-o-plus class="h-4 w-4" />
                            Simpan Kontribusi
                        </button>
                    </form>
                @endif
            </div>

            @if ($goal->notes)
                <div class="card card-pad">
                    <h2 class="text-sm font-bold text-slate-900">Catatan target</h2>
                    <p class="mt-2 whitespace-pre-line text-sm leading-relaxed text-slate-600">{{ $goal->notes }}</p>
                </div>
            @endif

            <div class="rounded-2xl border border-violet-100 bg-violet-50/60 p-4">
                <p class="flex items-center gap-2 text-xs font-bold text-violet-800">
                    <x-heroicon-o-sparkles class="h-4 w-4" />
                    {{ $goal->isCompleted() ? 'Target tercapai!' : 'Terus melangkah' }}
                </p>
                <p class="mt-1.5 text-xs leading-relaxed text-violet-700/80">
                    {{ $goal->isCompleted() ? 'Selamat! Kamu berhasil mencapai target ini. Kontribusi tetap bisa dicatat jika ingin menambah dana.' : 'Konsistensi lebih penting daripada nominal besar. Setiap kontribusi membawa kamu lebih dekat.' }}
                </p>
            </div>
        </aside>
    </div>
@endsection
