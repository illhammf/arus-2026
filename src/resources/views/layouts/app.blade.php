@php
    $user = auth()->user();
    $navigation = [
        ['route' => 'dashboard', 'url' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'home'],
        ['route' => 'transactions.*', 'url' => 'transactions.index', 'label' => 'Transaksi', 'icon' => 'arrows-right-left'],
        ['route' => 'transfers.*', 'url' => 'transfers.create', 'label' => 'Transfer', 'icon' => 'arrows-up-down'],
        ['route' => 'accounts.*', 'url' => 'accounts.index', 'label' => 'Akun', 'icon' => 'wallet'],
        ['route' => 'budgets.*', 'url' => 'budgets.index', 'label' => 'Anggaran', 'icon' => 'chart-pie'],
        ['route' => 'goals.*', 'url' => 'goals.index', 'label' => 'Target Tabungan', 'icon' => 'flag'],
        ['route' => 'reports.*', 'url' => 'reports.index', 'label' => 'Laporan', 'icon' => 'chart-bar'],
        ['route' => 'settings.*', 'url' => 'settings.index', 'label' => 'Pengaturan', 'icon' => 'cog-6-tooth'],
    ];
    $initials = \Illuminate\Support\Str::of($user->name)
        ->explode(' ')
        ->filter()
        ->map(fn ($word) => mb_substr($word, 0, 1))
        ->take(2)
        ->implode('');
@endphp
<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0d9488">
    <title>@yield('title', 'Dashboard') · Arus</title>
    <meta name="description" content="@yield('meta_description', 'Arus membantu Anda mengelola keuangan pribadi dengan cara yang sederhana dan menyenangkan.')">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="app-shell-bg min-h-full font-sans">
    <div class="min-h-screen lg:flex">
        {{-- Mobile backdrop --}}
        <div data-sidebar-backdrop class="fixed inset-0 z-40 hidden bg-slate-950/40 backdrop-blur-sm lg:hidden"></div>

        {{-- Sidebar --}}
        <aside data-sidebar class="fixed inset-y-0 left-0 z-50 flex w-72 -translate-x-full flex-col border-r border-slate-200/80 bg-white/95 backdrop-blur-xl transition-transform duration-300 ease-out lg:sticky lg:top-0 lg:h-screen lg:translate-x-0">
            <div class="flex h-16 shrink-0 items-center justify-between px-5">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-teal-500 to-emerald-600 text-white shadow-lg shadow-teal-600/25">
                        <x-heroicon-o-arrow-trending-up class="h-5 w-5" />
                    </span>
                    <span class="text-lg font-extrabold tracking-tight text-slate-900">Arus<span class="text-teal-600">.</span></span>
                </a>
                <button type="button" data-sidebar-close class="btn-icon lg:hidden" aria-label="Tutup menu">
                    <x-heroicon-o-x-mark class="h-5 w-5" />
                </button>
            </div>

            <div class="px-4 pb-4">
                <div class="rounded-2xl bg-gradient-to-br from-slate-900 to-slate-800 p-4 text-white shadow-lg shadow-slate-900/10">
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Arus Keuangan</p>
                    <p class="mt-1 text-sm font-medium text-slate-200">Catat, atur, dan capai targetmu.</p>
                    <div class="mt-3 flex gap-2">
                        <a href="{{ route('transactions.create') }}" class="inline-flex flex-1 items-center justify-center gap-1.5 rounded-lg bg-teal-500 px-2.5 py-2 text-xs font-bold text-white transition hover:bg-teal-400">
                            <x-heroicon-o-plus class="h-3.5 w-3.5" />
                            Transaksi
                        </a>
                        <a href="{{ route('transfers.create') }}" class="inline-flex flex-1 items-center justify-center gap-1.5 rounded-lg bg-white/10 px-2.5 py-2 text-xs font-bold text-white transition hover:bg-white/20">
                            <x-heroicon-o-arrows-up-down class="h-3.5 w-3.5" />
                            Transfer
                        </a>
                    </div>
                </div>
            </div>

            <nav class="sidebar-scroll flex-1 space-y-1 overflow-y-auto px-3 pb-4">
                <p class="px-3 pb-2 pt-1 text-[11px] font-bold uppercase tracking-wider text-slate-400">Menu Utama</p>
                @foreach ($navigation as $item)
                    <a href="{{ route($item['url']) }}"
                       class="nav-link group {{ request()->routeIs($item['route']) ? 'nav-link-active' : '' }}">
                        <x-dynamic-component :component="'heroicon-o-' . $item['icon']" class="nav-icon" />
                        <span>{{ $item['label'] }}</span>
                        @if (request()->routeIs($item['route']))
                            <span class="ml-auto h-1.5 w-1.5 rounded-full bg-teal-500"></span>
                        @endif
                    </a>
                @endforeach

                @if ($user->isSuperAdmin())
                    <div class="pt-3">
                        <p class="px-3 pb-2 text-[11px] font-bold uppercase tracking-wider text-slate-400">Administrator</p>
                        <a href="{{ url('/admin') }}" class="nav-link group">
                            <x-heroicon-o-shield-check class="nav-icon" />
                            <span>Panel Admin</span>
                            <x-heroicon-o-arrow-top-right-on-square class="ml-auto h-3.5 w-3.5 text-slate-400" />
                        </a>
                    </div>
                @endif
            </nav>

            <div class="shrink-0 border-t border-slate-200/80 p-3">
                <div class="flex items-center gap-3 rounded-xl px-2 py-2">
                    <span class="avatar h-9 w-9 text-xs">{{ $initials ?: 'A' }}</span>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-semibold text-slate-900">{{ $user->name }}</p>
                        <p class="truncate text-xs text-slate-500">{{ \App\Support\PhoneNormalizer::display($user->phone) !== '-' ? \App\Support\PhoneNormalizer::display($user->phone) : $user->email }}</p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn-icon" title="Keluar" aria-label="Keluar">
                            <x-heroicon-o-arrow-right-on-rectangle class="h-5 w-5" />
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        {{-- Main --}}
        <div class="flex min-w-0 flex-1 flex-col pb-20 lg:pb-0">
            <header class="sticky top-0 z-30 border-b border-slate-200/70 bg-white/80 backdrop-blur-xl">
                <div class="flex h-16 items-center gap-3 px-4 sm:px-6 lg:px-8">
                    <button type="button" data-sidebar-open class="btn-icon lg:hidden" aria-label="Buka menu">
                        <x-heroicon-o-bars-3 class="h-6 w-6" />
                    </button>

                    <div class="min-w-0 flex-1">
                        <h1 class="truncate text-base font-bold text-slate-900 sm:text-lg">@yield('title', 'Dashboard')</h1>
                        <p class="hidden truncate text-xs text-slate-500 sm:block">@yield('subtitle', 'Kelola arus keuanganmu dengan tenang.')</p>
                    </div>

                    <div class="hidden items-center gap-2 md:flex">
                        <a href="{{ route('transfers.create') }}" class="btn-secondary btn-sm">
                            <x-heroicon-o-arrows-up-down class="h-4 w-4" />
                            Transfer
                        </a>
                        <a href="{{ route('transactions.create') }}" class="btn-primary btn-sm">
                            <x-heroicon-o-plus class="h-4 w-4" />
                            Transaksi Baru
                        </a>
                    </div>

                    <div class="relative" data-dropdown>
                        <button type="button" data-dropdown-trigger aria-expanded="false" class="flex items-center gap-2 rounded-xl p-1 transition hover:bg-slate-100" aria-label="Menu pengguna">
                            <span class="avatar h-9 w-9 text-xs">{{ $initials ?: 'A' }}</span>
                            <x-heroicon-o-chevron-down class="hidden h-4 w-4 text-slate-400 sm:block" />
                        </button>
                        <div data-dropdown-panel class="absolute right-0 mt-2 hidden w-60 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl shadow-slate-900/10">
                            <div class="border-b border-slate-100 px-4 py-3">
                                <p class="truncate text-sm font-semibold text-slate-900">{{ $user->name }}</p>
                                <p class="truncate text-xs text-slate-500">{{ $user->email ?: \App\Support\PhoneNormalizer::display($user->phone) }}</p>
                            </div>
                            <div class="p-1.5">
                                <a href="{{ route('settings.index') }}" class="flex items-center gap-2.5 rounded-lg px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 hover:text-slate-900">
                                    <x-heroicon-o-cog-6-tooth class="h-4 w-4" />
                                    Pengaturan
                                </a>
                                @if ($user->isSuperAdmin())
                                    <a href="{{ url('/admin') }}" class="flex items-center gap-2.5 rounded-lg px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 hover:text-slate-900">
                                        <x-heroicon-o-shield-check class="h-4 w-4" />
                                        Panel Admin
                                    </a>
                                @endif
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="flex w-full items-center gap-2.5 rounded-lg px-3 py-2 text-sm font-medium text-rose-600 hover:bg-rose-50">
                                        <x-heroicon-o-arrow-right-on-rectangle class="h-4 w-4" />
                                        Keluar
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <main class="mx-auto w-full max-w-7xl flex-1 px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
                @if (session('success'))
                    <div data-alert class="mb-5 flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800 animate-rise">
                        <x-heroicon-o-check-circle class="mt-0.5 h-5 w-5 shrink-0 text-emerald-600" />
                        <p class="flex-1 font-medium">{{ session('success') }}</p>
                        <button type="button" data-dismiss-alert class="rounded-lg p-1 text-emerald-600 hover:bg-emerald-100" aria-label="Tutup">
                            <x-heroicon-o-x-mark class="h-4 w-4" />
                        </button>
                    </div>
                @endif

                @if (session('error'))
                    <div data-alert class="mb-5 flex items-start gap-3 rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800 animate-rise">
                        <x-heroicon-o-exclamation-triangle class="mt-0.5 h-5 w-5 shrink-0 text-rose-600" />
                        <p class="flex-1 font-medium">{{ session('error') }}</p>
                        <button type="button" data-dismiss-alert class="rounded-lg p-1 text-rose-600 hover:bg-rose-100" aria-label="Tutup">
                            <x-heroicon-o-x-mark class="h-4 w-4" />
                        </button>
                    </div>
                @endif

                @yield('content')
            </main>

            <footer class="mx-auto w-full max-w-7xl px-4 pb-6 text-center text-xs text-slate-400 sm:px-6 lg:px-8">
                &copy; {{ date('Y') }} Arus. Dibuat untuk keuangan yang lebih tenang.
            </footer>
        </div>
    </div>

    {{-- Mobile bottom navigation --}}
    <nav class="fixed inset-x-0 bottom-0 z-30 border-t border-slate-200 bg-white/95 pb-[env(safe-area-inset-bottom)] backdrop-blur-xl lg:hidden">
        <div class="grid grid-cols-5">
            <a href="{{ route('dashboard') }}" class="flex flex-col items-center gap-1 py-2.5 text-[10px] font-semibold {{ request()->routeIs('dashboard') ? 'text-teal-600' : 'text-slate-500' }}">
                <x-heroicon-o-home class="h-5 w-5" />
                Beranda
            </a>
            <a href="{{ route('transactions.index') }}" class="flex flex-col items-center gap-1 py-2.5 text-[10px] font-semibold {{ request()->routeIs('transactions.*') ? 'text-teal-600' : 'text-slate-500' }}">
                <x-heroicon-o-arrows-right-left class="h-5 w-5" />
                Transaksi
            </a>
            <a href="{{ route('transactions.create') }}" class="-mt-5 flex flex-col items-center">
                <span class="flex h-12 w-12 items-center justify-center rounded-full bg-gradient-to-br from-teal-500 to-emerald-600 text-white shadow-lg shadow-teal-600/30 ring-4 ring-white">
                    <x-heroicon-o-plus class="h-6 w-6" />
                </span>
                <span class="mt-0.5 text-[10px] font-semibold text-slate-500">Catat</span>
            </a>
            <a href="{{ route('budgets.index') }}" class="flex flex-col items-center gap-1 py-2.5 text-[10px] font-semibold {{ request()->routeIs('budgets.*') ? 'text-teal-600' : 'text-slate-500' }}">
                <x-heroicon-o-chart-pie class="h-5 w-5" />
                Anggaran
            </a>
            <a href="{{ route('reports.index') }}" class="flex flex-col items-center gap-1 py-2.5 text-[10px] font-semibold {{ request()->routeIs('reports.*') ? 'text-teal-600' : 'text-slate-500' }}">
                <x-heroicon-o-chart-bar class="h-5 w-5" />
                Laporan
            </a>
        </div>
    </nav>

    @stack('scripts')
</body>
</html>
