@extends('layouts.app')

@section('title', $transaction->exists ? 'Edit Transaksi' : 'Catat Transaksi')
@section('subtitle', $transaction->exists ? 'Perbarui detail transaksi ini.' : 'Setiap catatan kecil membantumu melihat gambaran besar.')

@php
    use App\Enums\TransactionType;
    use App\Support\Money;

    $selectedType = old('type', $transaction->type?->value ?? TransactionType::Expense->value);
    $selectedAccount = old('account_id', $transaction->account_id ?? $accounts->first()?->id);
    $selectedCategory = old('category_id', $transaction->category_id);
    $occurredAt = old('occurred_at', $transaction->occurred_at?->format('Y-m-d\TH:i') ?? now()->format('Y-m-d\TH:i'));
    $amount = old('amount', $transaction->exists ? (int) $transaction->amount : '');
@endphp

@section('content')
    <div class="mb-5">
        <a href="{{ $transaction->exists ? route('transactions.show', $transaction) : route('transactions.index') }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-slate-500 transition hover:text-slate-900">
            <x-heroicon-o-arrow-left class="h-4 w-4" />
            Kembali
        </a>
    </div>

    <form method="POST" action="{{ $transaction->exists ? route('transactions.update', $transaction) : route('transactions.store') }}" class="grid gap-5 xl:grid-cols-[1.6fr_1fr] xl:items-start">
        @csrf
        @if ($transaction->exists)
            @method('PUT')
        @endif

        <div class="space-y-5">
            <section class="form-section">
                <span class="label">Jenis transaksi</span>
                <div class="grid grid-cols-2 gap-3">
                    <label class="relative cursor-pointer">
                        <input type="radio" name="type" value="expense" class="peer sr-only" data-transaction-type @checked($selectedType === 'expense')>
                        <span class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-3.5 transition peer-checked:border-rose-500 peer-checked:bg-rose-50/70 peer-focus-visible:ring-2 peer-focus-visible:ring-rose-400 hover:border-slate-300">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-rose-100 text-rose-600">
                                <x-heroicon-o-arrow-up-right class="h-5 w-5" />
                            </span>
                            <span>
                                <span class="block text-sm font-bold text-slate-900">Pengeluaran</span>
                                <span class="block text-[11px] text-slate-500">Uang keluar</span>
                            </span>
                        </span>
                    </label>
                    <label class="relative cursor-pointer">
                        <input type="radio" name="type" value="income" class="peer sr-only" data-transaction-type @checked($selectedType === 'income')>
                        <span class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-3.5 transition peer-checked:border-emerald-500 peer-checked:bg-emerald-50/70 peer-focus-visible:ring-2 peer-focus-visible:ring-emerald-400 hover:border-slate-300">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600">
                                <x-heroicon-o-arrow-down-left class="h-5 w-5" />
                            </span>
                            <span>
                                <span class="block text-sm font-bold text-slate-900">Pemasukan</span>
                                <span class="block text-[11px] text-slate-500">Uang masuk</span>
                            </span>
                        </span>
                    </label>
                </div>
                @error('type')
                    <p class="error-text">{{ $message }}</p>
                @enderror
            </section>

            <section class="form-section">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-teal-50 text-teal-600">
                        <x-heroicon-o-banknotes class="h-5 w-5" />
                    </span>
                    <div>
                        <h2 class="text-sm font-bold text-slate-900">Detail transaksi</h2>
                        <p class="text-xs text-slate-500">Isi nominal, akun, dan kategori.</p>
                    </div>
                </div>

                <div class="mt-5 grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label for="amount" class="label">Nominal</label>
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-base font-bold text-slate-400">Rp</span>
                            <input id="amount" name="amount" type="text" inputmode="numeric" required data-money-input autofocus
                                   value="{{ $amount }}" placeholder="0"
                                   class="input py-3.5 pl-12 text-right text-xl font-bold money @error('amount') input-error @enderror">
                        </div>
                        @error('amount')
                            <p class="error-text">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="account_id" class="label">Akun</label>
                        <select id="account_id" name="account_id" required class="input @error('account_id') input-error @enderror">
                            <option value="">Pilih akun</option>
                            @foreach ($accounts as $account)
                                <option value="{{ $account->id }}" @selected((string) $selectedAccount === (string) $account->id)>{{ $account->name }} — {{ $account->type->label() }}</option>
                            @endforeach
                        </select>
                        @error('account_id')
                            <p class="error-text">{{ $message }}</p>
                        @enderror
                        @if ($accounts->isEmpty())
                            <p class="hint text-amber-600">Belum ada akun aktif. <a href="{{ route('accounts.create') }}" class="underline">Buat akun dulu</a>.</p>
                        @endif
                    </div>

                    <div>
                        <label for="category_id" class="label">Kategori <span class="font-normal text-slate-400">(opsional)</span></label>
                        <select id="category_id" name="category_id" class="input @error('category_id') input-error @enderror">
                            <option value="">Tanpa kategori</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" data-category-type="{{ $category->type->value }}" @selected((string) $selectedCategory === (string) $category->id)>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <p class="error-text">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="occurred_at" class="label">Tanggal & waktu</label>
                        <input id="occurred_at" name="occurred_at" type="datetime-local" required value="{{ $occurredAt }}"
                               class="input @error('occurred_at') input-error @enderror">
                        @error('occurred_at')
                            <p class="error-text">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label for="description" class="label">Deskripsi <span class="font-normal text-slate-400">(opsional)</span></label>
                        <input id="description" name="description" type="text" maxlength="255" value="{{ old('description', $transaction->description) }}"
                               placeholder="Contoh: Makan siang, gaji bulan ini…"
                               class="input @error('description') input-error @enderror">
                        @error('description')
                            <p class="error-text">{{ $message }}</p>
                        @enderror
                        <p class="hint">Jika dikosongkan, nama kategori akan digunakan sebagai deskripsi.</p>
                    </div>

                    <div class="sm:col-span-2">
                        <label for="notes" class="label">Catatan <span class="font-normal text-slate-400">(opsional)</span></label>
                        <textarea id="notes" name="notes" rows="3" maxlength="2000" placeholder="Detail tambahan yang ingin kamu simpan…"
                                  class="input @error('notes') input-error @enderror">{{ old('notes', $transaction->notes) }}</textarea>
                        @error('notes')
                            <p class="error-text">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </section>

            <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                <a href="{{ $transaction->exists ? route('transactions.show', $transaction) : route('transactions.index') }}" class="btn-secondary">Batal</a>
                <button type="submit" class="btn-primary">
                    <x-heroicon-o-check class="h-4 w-4" />
                    {{ $transaction->exists ? 'Simpan Perubahan' : 'Simpan Transaksi' }}
                </button>
            </div>
        </div>

        <aside class="space-y-4">
            <div class="card card-pad">
                <div class="flex items-center gap-3">
                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-slate-900 text-white">
                        <x-heroicon-o-light-bulb class="h-5 w-5" />
                    </span>
                    <h2 class="text-sm font-bold text-slate-900">Catat dengan rapi</h2>
                </div>
                <ul class="mt-4 space-y-3 text-xs leading-relaxed text-slate-500">
                    <li class="flex gap-2"><x-heroicon-o-check class="mt-0.5 h-3.5 w-3.5 shrink-0 text-teal-600" /> Pilih kategori yang paling mendekati agar laporan lebih bermakna.</li>
                    <li class="flex gap-2"><x-heroicon-o-check class="mt-0.5 h-3.5 w-3.5 shrink-0 text-teal-600" /> Untuk memindahkan uang antar akun, gunakan menu Transfer agar tidak terhitung dua kali.</li>
                    <li class="flex gap-2"><x-heroicon-o-check class="mt-0.5 h-3.5 w-3.5 shrink-0 text-teal-600" /> Catat transaksi sedekat mungkin dengan waktu kejadian.</li>
                </ul>
            </div>

            @if ($transaction->exists)
                <div class="card card-pad">
                    <p class="text-xs font-semibold text-slate-500">Dibuat</p>
                    <p class="mt-1 text-sm font-semibold text-slate-900">{{ $transaction->created_at->translatedFormat('d F Y, H:i') }}</p>
                    <p class="mt-0.5 text-[11px] text-slate-400">Terakhir diperbarui {{ $transaction->updated_at->diffForHumans() }}</p>
                </div>

                <div class="rounded-2xl border border-rose-100 bg-rose-50/60 p-4">
                    <p class="text-xs font-bold text-rose-800">Hapus transaksi</p>
                    <p class="mt-1 text-xs leading-relaxed text-rose-700/80">Transaksi akan dihapus dari riwayat dan saldo akun akan disesuaikan.</p>
                </div>
            @endif
        </aside>
    </form>

    @if ($transaction->exists)
        <div class="mt-5 flex justify-end">
            <form method="POST" action="{{ route('transactions.destroy', $transaction) }}" data-confirm="Hapus transaksi ini? Saldo akun akan disesuaikan dan tindakan ini tidak dapat dibatalkan.">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-soft-danger btn-sm">
                    <x-heroicon-o-trash class="h-4 w-4" />
                    Hapus Transaksi
                </button>
            </form>
        </div>
    @endif
@endsection
