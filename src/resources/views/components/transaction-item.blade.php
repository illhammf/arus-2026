@props([
    'transaction',
    'showAccount' => true,
])

@php
    use App\Enums\TransactionType;
    use App\Support\Money;

    $type = $transaction->type;
    $isNegative = in_array($type, [TransactionType::Expense, TransactionType::TransferOut], true);
    $meta = match ($type) {
        TransactionType::Income => ['icon' => 'arrow-down-left', 'class' => 'bg-emerald-50 text-emerald-600', 'label' => 'Pemasukan'],
        TransactionType::Expense => ['icon' => 'arrow-up-right', 'class' => 'bg-rose-50 text-rose-600', 'label' => 'Pengeluaran'],
        TransactionType::TransferOut => ['icon' => 'arrows-right-left', 'class' => 'bg-amber-50 text-amber-600', 'label' => 'Transfer keluar'],
        TransactionType::TransferIn => ['icon' => 'arrows-right-left', 'class' => 'bg-sky-50 text-sky-600', 'label' => 'Transfer masuk'],
    };
@endphp

<a href="{{ route('transactions.show', $transaction) }}" {{ $attributes->merge(['class' => 'group flex items-center gap-3 rounded-xl px-2 py-2.5 transition hover:bg-slate-50 sm:gap-4']) }}>
    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $meta['class'] }}">
        <x-dynamic-component :component="'heroicon-o-' . $meta['icon']" class="h-5 w-5" />
    </span>
    <div class="min-w-0 flex-1">
        <p class="truncate text-sm font-semibold text-slate-900 group-hover:text-teal-700">{{ $transaction->description }}</p>
        <p class="mt-0.5 flex items-center gap-1.5 truncate text-xs text-slate-500">
            <span>{{ $meta['label'] }}</span>
            <span class="text-slate-300">·</span>
            <span>{{ $transaction->occurred_at?->translatedFormat('d M Y') }}</span>
            @if ($showAccount)
                <span class="text-slate-300">·</span>
                <span class="truncate">{{ $transaction->account?->name }}</span>
            @endif
        </p>
    </div>
    <div class="shrink-0 text-right">
        <p class="money text-sm font-bold {{ $isNegative ? 'text-slate-900' : 'text-emerald-600' }}">
            {{ $isNegative ? '−' : '+' }}{{ Money::format($transaction->amount, $transaction->currency ?? 'IDR') }}
        </p>
        @if ($transaction->category)
            <p class="mt-0.5 max-w-[9rem] truncate text-[11px] text-slate-400">{{ $transaction->category->name }}</p>
        @endif
    </div>
</a>
