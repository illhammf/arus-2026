@php
    $features = [
        ['icon' => 'chart-pie', 'title' => 'Anggaran yang terkendali', 'description' => 'Tetapkan batas pengeluaran dan lihat sisa anggaran secara real-time.'],
        ['icon' => 'flag', 'title' => 'Target tabungan terukur', 'description' => 'Pecah impian menjadi target kecil yang bisa kamu rayakan.'],
        ['icon' => 'chart-bar', 'title' => 'Laporan yang jujur', 'description' => 'Pahami ke mana uangmu pergi lewat laporan yang mudah dibaca.'],
    ];
@endphp
<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0d9488">
    <title>@yield('title', 'Masuk') · Arus</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full bg-white font-sans text-slate-900">
    <div class="min-h-screen lg:grid lg:grid-cols-[1.05fr_1fr]">
        {{-- Brand panel --}}
        <aside class="relative hidden overflow-hidden bg-slate-950 lg:flex lg:flex-col lg:justify-between lg:p-10 xl:p-14">
            <div class="absolute inset-0 bg-gradient-to-br from-teal-950 via-slate-950 to-slate-900"></div>
            <div class="hero-grid absolute inset-0 opacity-60"></div>
            <div class="absolute -left-24 top-1/3 h-80 w-80 rounded-full bg-teal-500/20 blur-3xl"></div>
            <div class="absolute -right-20 bottom-10 h-72 w-72 rounded-full bg-emerald-400/10 blur-3xl"></div>

            <div class="relative">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/10 text-white ring-1 ring-white/20 backdrop-blur">
                        <x-heroicon-o-arrow-trending-up class="h-5 w-5" />
                    </span>
                    <span class="text-xl font-extrabold tracking-tight text-white">Arus<span class="text-teal-400">.</span></span>
                </a>
            </div>

            <div class="relative max-w-lg">
                <span class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-3 py-1 text-xs font-semibold text-teal-200">
                    <span class="h-1.5 w-1.5 rounded-full bg-teal-400"></span>
                    Keuangan pribadi, tanpa ribet
                </span>
                <h2 class="mt-5 text-4xl font-extrabold leading-tight tracking-tight text-white xl:text-5xl">
                    Uangmu punya arah.<br>
                    <span class="bg-gradient-to-r from-teal-300 to-emerald-300 bg-clip-text text-transparent">Kamu punya kendali.</span>
                </h2>
                <p class="mt-4 text-base leading-relaxed text-slate-300">
                    Arus merapikan pemasukan, pengeluaran, dan targetmu dalam satu tempat yang tenang dan mudah dipahami.
                </p>

                <div class="mt-8 space-y-4">
                    @foreach ($features as $feature)
                        <div class="flex items-start gap-3">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-teal-500/15 text-teal-300 ring-1 ring-teal-400/20">
                                <x-dynamic-component :component="'heroicon-o-' . $feature['icon']" class="h-5 w-5" />
                            </span>
                            <div>
                                <p class="text-sm font-semibold text-white">{{ $feature['title'] }}</p>
                                <p class="mt-0.5 text-xs leading-relaxed text-slate-400">{{ $feature['description'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="relative flex items-center gap-3 text-xs text-slate-400">
                <span class="flex -space-x-2">
                    <span class="flex h-7 w-7 items-center justify-center rounded-full bg-teal-500 text-[10px] font-bold text-white ring-2 ring-slate-900">RA</span>
                    <span class="flex h-7 w-7 items-center justify-center rounded-full bg-sky-500 text-[10px] font-bold text-white ring-2 ring-slate-900">DN</span>
                    <span class="flex h-7 w-7 items-center justify-center rounded-full bg-amber-500 text-[10px] font-bold text-white ring-2 ring-slate-900">SP</span>
                </span>
                <p>Dibuat untuk kamu yang ingin lebih tenang soal uang.</p>
            </div>
        </aside>

        {{-- Form panel --}}
        <main class="flex min-h-screen flex-col bg-slate-50">
            <div class="flex items-center justify-between p-5 sm:p-7 lg:hidden">
                <a href="{{ route('home') }}" class="flex items-center gap-2.5">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-teal-500 to-emerald-600 text-white shadow-lg shadow-teal-600/25">
                        <x-heroicon-o-arrow-trending-up class="h-5 w-5" />
                    </span>
                    <span class="text-lg font-extrabold tracking-tight text-slate-900">Arus<span class="text-teal-600">.</span></span>
                </a>
                <a href="{{ route('home') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-900">Kembali ke beranda</a>
            </div>

            <div class="flex flex-1 items-center justify-center px-5 py-8 sm:px-10">
                <div class="w-full max-w-md">
                    <div class="mb-7">
                        <p class="text-xs font-bold uppercase tracking-wider text-teal-600">@yield('eyebrow', 'Selamat datang')</p>
                        <h1 class="mt-2 text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">@yield('heading', 'Masuk ke Arus')</h1>
                        <p class="mt-2 text-sm leading-relaxed text-slate-500">@yield('description', 'Lanjutkan perjalanan keuanganmu dari tempat terakhir.')</p>
                    </div>

                    @yield('content')

                    <p class="mt-8 text-center text-xs text-slate-400">
                        &copy; {{ date('Y') }} Arus. Uangmu aman, datamu milikmu.
                    </p>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
