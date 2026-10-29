@props([
    'label',
    'value',
    'icon' => 'chart-bar',
    'tone' => 'teal',
    'hint' => null,
    'href' => null,
])

@php
    $tones = [
        'teal' => ['bg' => 'bg-teal-50', 'text' => 'text-teal-600', 'ring' => 'ring-teal-600/10'],
        'emerald' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-600', 'ring' => 'ring-emerald-600/10'],
        'rose' => ['bg' => 'bg-rose-50', 'text' => 'text-rose-600', 'ring' => 'ring-rose-600/10'],
        'amber' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-600', 'ring' => 'ring-amber-600/10'],
        'sky' => ['bg' => 'bg-sky-50', 'text' => 'text-sky-600', 'ring' => 'ring-sky-600/10'],
        'violet' => ['bg' => 'bg-violet-50', 'text' => 'text-violet-600', 'ring' => 'ring-violet-600/10'],
    ];
    $palette = $tones[$tone] ?? $tones['teal'];
@endphp

<div {{ $attributes->merge(['class' => 'stat-card']) }}>
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
            <p class="text-sm font-medium text-slate-500">{{ $label }}</p>
            <p class="money mt-2 truncate text-2xl font-extrabold tracking-tight text-slate-900">{{ $value }}</p>
        </div>
        <span class="icon-tile {{ $palette['bg'] }} {{ $palette['text'] }} ring-1 {{ $palette['ring'] }}">
            <x-dynamic-component :component="'heroicon-o-' . $icon" class="h-5 w-5" />
        </span>
    </div>
    <div class="mt-3 flex items-center justify-between gap-2">
        @if ($hint)
            <p class="text-xs text-slate-500">{{ $hint }}</p>
        @else
            <span></span>
        @endif
        @if ($href)
            <a href="{{ $href }}" class="inline-flex items-center gap-1 text-xs font-semibold text-slate-500 transition hover:text-teal-700">
                Detail
                <x-heroicon-o-arrow-right class="h-3.5 w-3.5" />
            </a>
        @endif
    </div>
</div>
