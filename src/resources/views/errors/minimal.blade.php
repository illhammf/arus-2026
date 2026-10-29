@props([
    'code' => '500',
    'title' => 'Terjadi kesalahan',
    'description' => 'Silakan coba lagi beberapa saat lagi.',
    'icon' => 'exclamation-triangle',
])
<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>{{ $code }} · {{ $title }} — Arus</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
</head>
<body class="app-shell-bg flex min-h-full items-center justify-center px-5 py-12 font-sans">
    <div class="w-full max-w-lg text-center">
        <a href="{{ url('/') }}" class="inline-flex items-center gap-2.5">
            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-teal-500 to-emerald-600 text-white shadow-lg shadow-teal-600/25">
                <x-heroicon-o-arrow-trending-up class="h-5 w-5" />
            </span>
            <span class="text-xl font-extrabold tracking-tight text-slate-900">Arus<span class="text-teal-600">.</span></span>
        </a>

        <div class="card mt-8 overflow-hidden">
            <div class="bg-gradient-to-br from-slate-900 to-teal-950 px-6 py-10 text-white">
                <span class="inline-flex h-14 w-14 items-center justify-center rounded-2xl bg-white/10 ring-1 ring-white/15 backdrop-blur">
                    <x-dynamic-component :component="'heroicon-o-' . $icon" class="h-7 w-7" />
                </span>
                <p class="mt-5 text-5xl font-extrabold tracking-tight">{{ $code }}</p>
                <h1 class="mt-2 text-lg font-bold">{{ $title }}</h1>
            </div>
            <div class="px-6 py-6">
                <p class="text-sm leading-relaxed text-slate-500">{{ $description }}</p>
                <div class="mt-6 flex flex-col justify-center gap-2 sm:flex-row">
                    <a href="{{ url('/') }}" class="btn-primary">Kembali ke Beranda</a>
                    @auth
                        <a href="{{ route('dashboard') }}" class="btn-secondary">Buka Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="btn-secondary">Masuk</a>
                    @endauth
                </div>
            </div>
        </div>

        <p class="mt-6 text-xs text-slate-400">&copy; {{ date('Y') }} Arus. Keuangan pribadi yang lebih tenang.</p>
    </div>
</body>
</html>
