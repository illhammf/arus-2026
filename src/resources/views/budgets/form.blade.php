@extends('layouts.app')

@section('title', $budget->exists ? 'Edit Anggaran' : 'Buat Anggaran')
@section('subtitle', $budget->exists ? 'Sesuaikan batas pengeluaranmu.' : 'Tentukan batas yang realistis, bukan yang menyiksa.')

@php
    use App\Enums\BudgetPeriod;
    use App\Support\Money;

    $selectedType = old('period_type', $budget->period_type?->value ?? BudgetPeriod::Monthly->value);
    $periodValue = old('period', $periodValue ?? now()->format('Y-m'));
    $amount = old('amount', $budget->exists ? (int) $budget->amount : '');
@endphp

@section('content')
    <div class="mb-5">
        <a href="{{ route('budgets.index') }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-slate-500 transition hover:text-slate-900">
            <x-heroicon-o-arrow-left class="h-4 w-4" />
            Kembali
        </a>
    </div>

    <form method="POST" action="{{ $budget->exists ? route('budgets.update', $budget) : route('budgets.store') }}" class="grid gap-5 xl:grid-cols-[1.6fr_1fr] xl:items-start">
        @csrf
        @if ($budget->exists)
            @method('PUT')
        @endif

        <div class="space-y-5">
            <section class="form-section">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-50 text-amber-600">
                        <x-heroicon-o-chart-pie class="h-5 w-5" />
                    </span>
                    <div>
                        <h2 class="text-sm font-bold text-slate-900">Informasi anggaran</h2>
                        <p class="text-xs text-slate-500">Pilih kategori dan periode yang ingin dibatasi.</p>
                    </div>
                </div>

                <div class="mt-5 grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label for="category_id" class="label">Kategori pengeluaran <span class="font-normal text-slate-400">(opsional)</span></label>
                        <select id="category_id" name="category_id" class="input @error('category_id') input-error @enderror">
                            <option value="">Semua pengeluaran</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected((string) old('category_id', $budget->category_id) === (string) $category->id)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                        <p class="hint">Kosongkan untuk membatasi total seluruh pengeluaran.</p>
                        @error('category_id')
                            <p class="error-text">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="period_type" class="label">Jenis periode</label>
                        <select id="period_type" name="period_type" data-period-type class="input @error('period_type') input-error @enderror">
                            @foreach ($periodTypes as $value => $label)
                                <option value="{{ $value }}" @selected($selectedType === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('period_type')
                            <p class="error-text">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="period" class="label">Periode</label>
                        <input id="period" name="period" type="month" value="{{ $periodValue }}" data-period-month
                               class="input @error('period') input-error @enderror">
                        <input id="period_year" type="number" value="{{ $periodValue }}" min="2000" max="2100" data-period-year
                               class="input hidden @error('period') input-error @enderror">
                        @error('period')
                            <p class="error-text">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label for="amount" class="label">Batas anggaran</label>
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-base font-bold text-slate-400">Rp</span>
                            <input id="amount" name="amount" type="text" inputmode="numeric" required data-money-input
                                   value="{{ $amount }}" placeholder="0"
                                   class="input py-3.5 pl-12 text-right text-xl font-bold money @error('amount') input-error @enderror">
                        </div>
                        @error('amount')
                            <p class="error-text">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label for="notes" class="label">Catatan <span class="font-normal text-slate-400">(opsional)</span></label>
                        <textarea id="notes" name="notes" rows="3" maxlength="2000" placeholder="Misalnya: batas makan di luar, belanja bulanan…"
                                  class="input @error('notes') input-error @enderror">{{ old('notes', $budget->notes) }}</textarea>
                        @error('notes')
                            <p class="error-text">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </section>

            @if ($budget->exists)
                <section class="card card-pad">
                    <h2 class="text-sm font-bold text-slate-900">Ringkasan saat ini</h2>
                    <div class="mt-4 grid grid-cols-3 gap-3 text-center">
                        <div class="rounded-xl bg-slate-50 p-3">
                            <p class="text-[10px] font-medium text-slate-400">Anggaran</p>
                            <p class="money mt-1 text-xs font-bold text-slate-900">{{ Money::format($budget->amount) }}</p>
                        </div>
                        <div class="rounded-xl bg-slate-50 p-3">
                            <p class="text-[10px] font-medium text-slate-400">Terpakai</p>
                            <p class="money mt-1 text-xs font-bold text-slate-900">{{ Money::format($budget->spent()) }}</p>
                        </div>
                        <div class="rounded-xl bg-slate-50 p-3">
                            <p class="text-[10px] font-medium text-slate-400">Sisa</p>
                            <p class="money mt-1 text-xs font-bold {{ $budget->isOverBudget() ? 'text-rose-600' : 'text-emerald-600' }}">{{ Money::format($budget->remaining()) }}</p>
                        </div>
                    </div>
                    <div class="progress-track mt-3">
                        <div class="progress-fill {{ $budget->isOverBudget() ? 'bg-rose-500' : 'bg-teal-500' }}" style="width: {{ min(100, $budget->usagePercentage()) }}%"></div>
                    </div>
                </section>
            @endif

            <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-between">
                <div>
                    @if ($budget->exists)
                        <button type="submit" form="delete-budget" class="btn-soft-danger">
                            <x-heroicon-o-trash class="h-4 w-4" />
                            Hapus
                        </button>
                    @endif
                </div>
                <div class="flex flex-col-reverse gap-2 sm:flex-row">
                    <a href="{{ route('budgets.index') }}" class="btn-secondary">Batal</a>
                    <button type="submit" class="btn-primary">
                        <x-heroicon-o-check class="h-4 w-4" />
                        {{ $budget->exists ? 'Simpan Perubahan' : 'Buat Anggaran' }}
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
                    <h2 class="text-sm font-bold text-slate-900">Anggaran yang sehat</h2>
                </div>
                <ul class="mt-4 space-y-3 text-xs leading-relaxed text-slate-500">
                    <li class="flex gap-2"><x-heroicon-o-check class="mt-0.5 h-3.5 w-3.5 shrink-0 text-teal-600" /> Mulai dari angka yang benar-benar kamu belanjakan bulan lalu.</li>
                    <li class="flex gap-2"><x-heroicon-o-check class="mt-0.5 h-3.5 w-3.5 shrink-0 text-teal-600" /> Buat anggaran per kategori untuk kontrol yang lebih detail.</li>
                    <li class="flex gap-2"><x-heroicon-o-check class="mt-0.5 h-3.5 w-3.5 shrink-0 text-teal-600" /> Tinjau ulang setiap awal bulan, bukan setiap kali merasa bersalah.</li>
                </ul>
            </div>

            <div class="rounded-2xl border border-amber-100 bg-amber-50/60 p-4">
                <p class="flex items-center gap-2 text-xs font-bold text-amber-800">
                    <x-heroicon-o-information-circle class="h-4 w-4" />
                    Cara perhitungan
                </p>
                <p class="mt-1.5 text-xs leading-relaxed text-amber-700/80">Arus hanya menghitung transaksi pengeluaran berstatus tercatat pada rentang tanggal periode. Transfer antar akun tidak dihitung sebagai pengeluaran.</p>
            </div>
        </aside>
    </form>

    @if ($budget->exists)
        <form id="delete-budget" method="POST" action="{{ route('budgets.destroy', $budget) }}" class="hidden" data-confirm="Hapus anggaran ini?">
            @csrf
            @method('DELETE')
        </form>
    @endif
@endsection
