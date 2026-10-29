@extends('layouts.app')

@section('title', $goal->exists ? 'Edit Target' : 'Buat Target Tabungan')
@section('subtitle', $goal->exists ? 'Sesuaikan target dan tenggatmu.' : 'Mulai dari impian, lalu pecah menjadi langkah kecil.')

@php
    use App\Support\Money;

    $targetDate = old('target_date', $goal->target_date?->format('Y-m-d'));
    $amount = old('target_amount', $goal->exists ? (int) $goal->target_amount : '');
@endphp

@section('content')
    <div class="mb-5">
        <a href="{{ $goal->exists ? route('goals.show', $goal) : route('goals.index') }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-slate-500 transition hover:text-slate-900">
            <x-heroicon-o-arrow-left class="h-4 w-4" />
            Kembali
        </a>
    </div>

    <form method="POST" action="{{ $goal->exists ? route('goals.update', $goal) : route('goals.store') }}" class="grid gap-5 xl:grid-cols-[1.6fr_1fr] xl:items-start">
        @csrf
        @if ($goal->exists)
            @method('PUT')
        @endif

        <div class="space-y-5">
            <section class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-violet-700 via-violet-800 to-slate-900 p-6 text-white shadow-xl shadow-violet-900/10">
                <div class="hero-grid absolute inset-0 opacity-30"></div>
                <div class="relative flex items-center gap-4">
                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-white/10 ring-1 ring-white/20 backdrop-blur">
                        <x-heroicon-o-flag class="h-6 w-6" />
                    </span>
                    <div>
                        <h2 class="text-lg font-extrabold tracking-tight">Satu target, satu langkah</h2>
                        <p class="mt-1 text-xs leading-relaxed text-violet-100/80">Target yang jelas membuat menabung terasa lebih ringan. Kamu bisa menambah kontribusi kapan saja.</p>
                    </div>
                </div>
            </section>

            <section class="form-section">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label for="name" class="label">Nama target</label>
                        <input id="name" name="name" type="text" required maxlength="100" value="{{ old('name', $goal->name) }}"
                               placeholder="Contoh: Dana Darurat, Liburan ke Bali"
                               class="input @error('name') input-error @enderror">
                        @error('name')
                            <p class="error-text">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="target_amount" class="label">Nominal target</label>
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-sm font-semibold text-slate-400">Rp</span>
                            <input id="target_amount" name="target_amount" type="text" inputmode="numeric" required data-money-input
                                   value="{{ $amount }}" placeholder="0"
                                   class="input pl-11 text-right money @error('target_amount') input-error @enderror">
                        </div>
                        @error('target_amount')
                            <p class="error-text">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="target_date" class="label">Tenggat <span class="font-normal text-slate-400">(opsional)</span></label>
                        <input id="target_date" name="target_date" type="date" value="{{ $targetDate }}"
                               class="input @error('target_date') input-error @enderror">
                        @error('target_date')
                            <p class="error-text">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label for="linked_account_id" class="label">Akun terkait <span class="font-normal text-slate-400">(opsional)</span></label>
                        <select id="linked_account_id" name="linked_account_id" class="input @error('linked_account_id') input-error @enderror">
                            <option value="">Tidak terkait akun tertentu</option>
                            @foreach ($accounts as $account)
                                <option value="{{ $account->id }}" @selected((string) old('linked_account_id', $goal->linked_account_id) === (string) $account->id)>{{ $account->name }} — {{ $account->type->label() }}</option>
                            @endforeach
                        </select>
                        <p class="hint">Penanda saja — kontribusi tetap dicatat terpisah dari saldo akun.</p>
                        @error('linked_account_id')
                            <p class="error-text">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label for="notes" class="label">Catatan <span class="font-normal text-slate-400">(opsional)</span></label>
                        <textarea id="notes" name="notes" rows="3" maxlength="2000" placeholder="Kenapa target ini penting untukmu?"
                                  class="input @error('notes') input-error @enderror">{{ old('notes', $goal->notes) }}</textarea>
                        @error('notes')
                            <p class="error-text">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </section>

            <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-between">
                <div>
                    @if ($goal->exists)
                        <button type="submit" form="delete-goal" class="btn-soft-danger">
                            <x-heroicon-o-trash class="h-4 w-4" />
                            Hapus Target
                        </button>
                    @endif
                </div>
                <div class="flex flex-col-reverse gap-2 sm:flex-row">
                    <a href="{{ $goal->exists ? route('goals.show', $goal) : route('goals.index') }}" class="btn-secondary">Batal</a>
                    <button type="submit" class="btn-primary">
                        <x-heroicon-o-check class="h-4 w-4" />
                        {{ $goal->exists ? 'Simpan Perubahan' : 'Buat Target' }}
                    </button>
                </div>
            </div>
        </div>

        <aside class="space-y-4">
            <div class="card card-pad">
                <div class="flex items-center gap-3">
                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-slate-900 text-white">
                        <x-heroicon-o-light-bulb class="h-5 w-5" />
                    </span>
                    <h2 class="text-sm font-bold text-slate-900">Tips mencapai target</h2>
                </div>
                <ul class="mt-4 space-y-3 text-xs leading-relaxed text-slate-500">
                    <li class="flex gap-2"><x-heroicon-o-check class="mt-0.5 h-3.5 w-3.5 shrink-0 text-teal-600" /> Tentukan tenggat yang realistis, bukan yang paling cepat.</li>
                    <li class="flex gap-2"><x-heroicon-o-check class="mt-0.5 h-3.5 w-3.5 shrink-0 text-teal-600" /> Sisihkan nominal tetap setiap bulan agar progresnya konsisten.</li>
                    <li class="flex gap-2"><x-heroicon-o-check class="mt-0.5 h-3.5 w-3.5 shrink-0 text-teal-600" /> Rayakan pencapaian kecil — menabung juga soal kebiasaan.</li>
                </ul>
            </div>

            @if ($goal->exists)
                <div class="card card-pad">
                    <p class="text-xs font-semibold text-slate-500">Progres saat ini</p>
                    <p class="money mt-1 text-2xl font-extrabold text-slate-900">{{ Money::format($goal->contributedTotal()) }}</p>
                    <div class="progress-track mt-3">
                        <div class="progress-fill bg-gradient-to-r from-violet-500 to-fuchsia-500" style="width: {{ min(100, $goal->progressPercentage()) }}%"></div>
                    </div>
                    <p class="mt-2 text-[11px] text-slate-500">{{ number_format($goal->progressPercentage(), 0) }}% dari {{ Money::format($goal->target_amount) }}</p>
                </div>
            @endif

            <div class="rounded-2xl border border-violet-100 bg-violet-50/60 p-4">
                <p class="flex items-center gap-2 text-xs font-bold text-violet-800">
                    <x-heroicon-o-sparkles class="h-4 w-4" />
                    Fleksibel
                </p>
                <p class="mt-1.5 text-xs leading-relaxed text-violet-700/80">Target dapat diubah kapan saja. Jika keadaan berubah, sesuaikan tanpa rasa bersalah.</p>
            </div>
        </aside>
    </form>

    @if ($goal->exists)
        <form id="delete-goal" method="POST" action="{{ route('goals.destroy', $goal) }}" class="hidden" data-confirm="Hapus target ini beserta seluruh kontribusinya? Tindakan ini tidak dapat dibatalkan.">
            @csrf
            @method('DELETE')
        </form>
    @endif
@endsection
