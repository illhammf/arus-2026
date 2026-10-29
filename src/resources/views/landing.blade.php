<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0f172a">
    <title>Arus — Keuangan pribadi yang lebih tenang</title>
    <meta name="description" content="Catat pemasukan dan pengeluaran, atur anggaran, capai target tabungan, dan pahami arus keuanganmu dalam satu aplikasi yang sederhana.">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-white font-sans text-slate-900">
    <div class="relative overflow-hidden bg-slate-950">
        <div class="absolute inset-0 bg-gradient-to-br from-slate-950 via-slate-950 to-teal-950"></div>
        <div class="hero-grid absolute inset-0 opacity-50"></div>
        <div class="absolute -left-40 top-10 h-96 w-96 rounded-full bg-teal-500/20 blur-3xl"></div>
        <div class="absolute -right-32 top-40 h-96 w-96 rounded-full bg-emerald-400/10 blur-3xl"></div>

        {{-- Navigation --}}
        <header class="relative z-20">
            <nav class="mx-auto flex max-w-7xl items-center justify-between px-5 py-5 sm:px-8">
                <a href="{{ route('home') }}" class="flex items-center gap-2.5">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-teal-400 to-emerald-500 text-slate-950 shadow-lg shadow-teal-500/20">
                        <x-heroicon-o-arrow-trending-up class="h-5 w-5" />
                    </span>
                    <span class="text-xl font-extrabold tracking-tight text-white">Arus<span class="text-teal-400">.</span></span>
                </a>

                <div class="hidden items-center gap-8 text-sm font-medium text-slate-300 md:flex">
                    <a href="#fitur" class="transition hover:text-white">Fitur</a>
                    <a href="#cara-kerja" class="transition hover:text-white">Cara Kerja</a>
                    <a href="#cerita" class="transition hover:text-white">Kenapa Arus</a>
                </div>

                <div class="flex items-center gap-2">
                    @auth
                        <a href="{{ route('dashboard') }}" class="btn-primary btn-sm">Buka Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="hidden rounded-xl px-4 py-2 text-sm font-semibold text-slate-300 transition hover:bg-white/5 hover:text-white sm:inline-flex">Masuk</a>
                        <a href="{{ route('register') }}" class="btn-primary btn-sm">Mulai Gratis</a>
                    @endauth
                </div>
            </nav>
        </header>

        {{-- Hero --}}
        <section class="relative z-10 mx-auto grid max-w-7xl gap-12 px-5 pb-20 pt-12 sm:px-8 lg:grid-cols-[1.05fr_0.95fr] lg:items-center lg:pb-28 lg:pt-16">
            <div class="max-w-2xl">
                <span class="inline-flex items-center gap-2 rounded-full border border-teal-400/20 bg-teal-400/10 px-3.5 py-1.5 text-xs font-semibold text-teal-200">
                    <x-heroicon-o-sparkles class="h-4 w-4" />
                    Keuangan pribadi, tanpa drama
                </span>
                <h1 class="mt-6 text-balance text-4xl font-extrabold leading-[1.08] tracking-tight text-white sm:text-5xl lg:text-6xl">
                    Uangmu punya arah.<br>
                    <span class="bg-gradient-to-r from-teal-300 via-emerald-300 to-teal-200 bg-clip-text text-transparent">Kamu punya kendali.</span>
                </h1>
                <p class="mt-6 max-w-xl text-base leading-relaxed text-slate-300 sm:text-lg">
                    Arus merapikan pemasukan, pengeluaran, transfer, anggaran, dan target tabunganmu dalam satu tempat. Tenang dipakai, mudah dipahami.
                </p>

                <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                    <a href="{{ route('register') }}" class="btn-primary btn-lg">
                        Mulai sekarang — gratis
                        <x-heroicon-o-arrow-right class="h-4 w-4" />
                    </a>
                    <a href="{{ route('login') }}" class="btn btn-lg border border-white/15 bg-white/5 text-white backdrop-blur hover:bg-white/10">
                        Saya sudah punya akun
                    </a>
                </div>

                <div class="mt-8 flex flex-wrap items-center gap-x-6 gap-y-3 text-xs text-slate-400">
                    <span class="inline-flex items-center gap-1.5">
                        <x-heroicon-o-check-circle class="h-4 w-4 text-teal-400" />
                        Tanpa kartu kredit
                    </span>
                    <span class="inline-flex items-center gap-1.5">
                        <x-heroicon-o-check-circle class="h-4 w-4 text-teal-400" />
                        Data keuanganmu terisolasi
                    </span>
                    <span class="inline-flex items-center gap-1.5">
                        <x-heroicon-o-check-circle class="h-4 w-4 text-teal-400" />
                        Rupiah & waktu Indonesia
                    </span>
                </div>
            </div>

            {{-- Mockup --}}
            <div class="relative mx-auto w-full max-w-lg lg:max-w-none">
                <div class="absolute -inset-6 rounded-[2.5rem] bg-gradient-to-tr from-teal-500/20 via-transparent to-emerald-400/10 blur-2xl"></div>
                <div class="relative rounded-[2rem] border border-white/10 bg-white/5 p-2.5 shadow-2xl shadow-slate-950/60 backdrop-blur">
                    <div class="overflow-hidden rounded-[1.55rem] bg-slate-50">
                        <div class="flex items-center justify-between border-b border-slate-200/80 bg-white px-5 py-3.5">
                            <div class="flex items-center gap-2">
                                <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-teal-600 text-white">
                                    <x-heroicon-o-arrow-trending-up class="h-4 w-4" />
                                </span>
                                <span class="text-sm font-extrabold tracking-tight text-slate-900">Arus<span class="text-teal-600">.</span></span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                                <span class="text-[10px] font-semibold text-slate-500">Live</span>
                            </div>
                        </div>

                        <div class="space-y-3 p-4">
                            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-teal-600 to-emerald-700 p-5 text-white">
                                <div class="hero-grid absolute inset-0 opacity-30"></div>
                                <div class="relative">
                                    <p class="text-[11px] font-semibold uppercase tracking-wider text-teal-100">Total saldo</p>
                                    <p class="money mt-1 text-3xl font-extrabold tracking-tight">Rp24.850.000</p>
                                    <div class="mt-4 flex items-center gap-5 text-[11px]">
                                        <span class="inline-flex items-center gap-1.5">
                                            <span class="flex h-5 w-5 items-center justify-center rounded-full bg-white/15"><x-heroicon-o-arrow-down-left class="h-3 w-3" /></span>
                                            Masuk <strong class="font-bold">Rp8.500.000</strong>
                                        </span>
                                        <span class="inline-flex items-center gap-1.5">
                                            <span class="flex h-5 w-5 items-center justify-center rounded-full bg-white/15"><x-heroicon-o-arrow-up-right class="h-3 w-3" /></span>
                                            Keluar <strong class="font-bold">Rp3.240.000</strong>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div class="rounded-xl border border-slate-200 bg-white p-3">
                                    <div class="flex items-center gap-2">
                                        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-amber-50 text-amber-600"><x-heroicon-o-cake class="h-4 w-4" /></span>
                                        <div>
                                            <p class="text-[10px] text-slate-500">Makan & Minum</p>
                                            <p class="money text-xs font-bold text-slate-900">Rp1.240.000</p>
                                        </div>
                                    </div>
                                    <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-slate-100"><div class="h-full w-[62%] rounded-full bg-amber-500"></div></div>
                                </div>
                                <div class="rounded-xl border border-slate-200 bg-white p-3">
                                    <div class="flex items-center gap-2">
                                        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-teal-50 text-teal-600"><x-heroicon-o-flag class="h-4 w-4" /></span>
                                        <div>
                                            <p class="text-[10px] text-slate-500">Dana Darurat</p>
                                            <p class="money text-xs font-bold text-slate-900">68%</p>
                                        </div>
                                    </div>
                                    <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-slate-100"><div class="h-full w-[68%] rounded-full bg-teal-500"></div></div>
                                </div>
                            </div>

                            <div class="rounded-xl border border-slate-200 bg-white p-2">
                                <div class="flex items-center justify-between px-2 py-1.5">
                                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Transaksi terbaru</p>
                                    <span class="text-[10px] font-semibold text-teal-600">Lihat semua</span>
                                </div>
                                <div class="space-y-0.5">
                                    <div class="flex items-center gap-2.5 rounded-lg px-2 py-2">
                                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600"><x-heroicon-o-arrow-down-left class="h-4 w-4" /></span>
                                        <div class="min-w-0 flex-1">
                                            <p class="truncate text-xs font-semibold text-slate-900">Gaji Oktober</p>
                                            <p class="text-[10px] text-slate-500">Pemasukan · 25 Okt</p>
                                        </div>
                                        <p class="money text-xs font-bold text-emerald-600">+Rp8.500.000</p>
                                    </div>
                                    <div class="flex items-center gap-2.5 rounded-lg px-2 py-2">
                                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-rose-50 text-rose-600"><x-heroicon-o-arrow-up-right class="h-4 w-4" /></span>
                                        <div class="min-w-0 flex-1">
                                            <p class="truncate text-xs font-semibold text-slate-900">Belanja bulanan</p>
                                            <p class="text-[10px] text-slate-500">Pengeluaran · 24 Okt</p>
                                        </div>
                                        <p class="money text-xs font-bold text-slate-900">−Rp865.000</p>
                                    </div>
                                    <div class="flex items-center gap-2.5 rounded-lg px-2 py-2">
                                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-sky-50 text-sky-600"><x-heroicon-o-arrows-right-left class="h-4 w-4" /></span>
                                        <div class="min-w-0 flex-1">
                                            <p class="truncate text-xs font-semibold text-slate-900">Transfer ke Tabungan</p>
                                            <p class="text-[10px] text-slate-500">Transfer · 23 Okt</p>
                                        </div>
                                        <p class="money text-xs font-bold text-slate-900">−Rp1.500.000</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="absolute -bottom-5 -left-3 hidden items-center gap-2 rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 shadow-xl sm:flex">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600"><x-heroicon-o-check-badge class="h-4 w-4" /></span>
                    <div>
                        <p class="text-[10px] font-semibold text-slate-500">Bulan ini</p>
                        <p class="text-xs font-bold text-slate-900">Lebih hemat 18%</p>
                    </div>
                </div>
            </div>
        </section>

        <div class="relative z-10 border-t border-white/5 bg-white/[0.02]">
            <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-center gap-x-10 gap-y-4 px-5 py-5 text-xs font-medium text-slate-400 sm:px-8">
                <span class="inline-flex items-center gap-2"><x-heroicon-o-lock-closed class="h-4 w-4 text-teal-400" /> Data terisolasi per pengguna</span>
                <span class="inline-flex items-center gap-2"><x-heroicon-o-banknotes class="h-4 w-4 text-teal-400" /> Mendukung Rupiah</span>
                <span class="inline-flex items-center gap-2"><x-heroicon-o-clock class="h-4 w-4 text-teal-400" /> Zona waktu Asia/Jakarta</span>
                <span class="inline-flex items-center gap-2"><x-heroicon-o-device-phone-mobile class="h-4 w-4 text-teal-400" /> Nyaman di ponsel</span>
            </div>
        </div>
    </div>

    {{-- Features --}}
    <section id="fitur" class="relative overflow-hidden bg-white py-20 sm:py-24">
        <div class="landing-grid absolute inset-0 opacity-40"></div>
        <div class="relative mx-auto max-w-7xl px-5 sm:px-8">
            <div class="mx-auto max-w-2xl text-center">
                <span class="badge-brand">Semua yang kamu butuhkan</span>
                <h2 class="mt-4 text-balance text-3xl font-extrabold tracking-tight text-slate-900 sm:text-4xl">Satu tempat untuk setiap arus uangmu</h2>
                <p class="mt-3 text-base leading-relaxed text-slate-500">Bukan sekadar catatan. Arus membantumu mengambil keputusan kecil yang berdampak besar.</p>
            </div>

            @php
                $features = [
                    ['icon' => 'wallet', 'tone' => 'teal', 'title' => 'Banyak akun, satu pandangan', 'description' => 'Kelola dompet, rekening bank, e-wallet, dan tabungan. Saldo dihitung otomatis dari transaksi.'],
                    ['icon' => 'arrows-right-left', 'tone' => 'sky', 'title' => 'Transaksi & transfer rapi', 'description' => 'Catat pemasukan dan pengeluaran, lalu pindahkan uang antar akun tanpa catatan ganda.'],
                    ['icon' => 'chart-pie', 'tone' => 'amber', 'title' => 'Anggaran yang bekerja', 'description' => 'Buat anggaran bulanan atau tahunan dan lihat sisa uang sebelum kamu kehabisan.'],
                    ['icon' => 'flag', 'tone' => 'violet', 'title' => 'Target yang bisa diraih', 'description' => 'Dana darurat, liburan, atau gadget baru — pecah targetnya dan rayakan setiap langkah.'],
                    ['icon' => 'chart-bar', 'tone' => 'emerald', 'title' => 'Laporan tanpa istilah rumit', 'description' => 'Tren enam bulan, kategori pengeluaran, dan ekspor CSV untuk kebutuhanmu.'],
                    ['icon' => 'shield-check', 'tone' => 'rose', 'title' => 'Privasi sejak awal', 'description' => 'Setiap data keuangan terikat ke akunmu dan diperiksa di server. Bukan sekadar tampilan.'],
                ];
                $tones = [
                    'teal' => 'bg-teal-50 text-teal-600 ring-teal-600/10',
                    'sky' => 'bg-sky-50 text-sky-600 ring-sky-600/10',
                    'amber' => 'bg-amber-50 text-amber-600 ring-amber-600/10',
                    'violet' => 'bg-violet-50 text-violet-600 ring-violet-600/10',
                    'emerald' => 'bg-emerald-50 text-emerald-600 ring-emerald-600/10',
                    'rose' => 'bg-rose-50 text-rose-600 ring-rose-600/10',
                ];
            @endphp

            <div class="mt-12 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($features as $feature)
                    <div class="card card-hover p-6">
                        <span class="icon-tile ring-1 {{ $tones[$feature['tone']] }}">
                            <x-dynamic-component :component="'heroicon-o-' . $feature['icon']" class="h-5 w-5" />
                        </span>
                        <h3 class="mt-4 text-base font-bold text-slate-900">{{ $feature['title'] }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-slate-500">{{ $feature['description'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- How it works --}}
    <section id="cara-kerja" class="relative overflow-hidden bg-slate-50 py-20 sm:py-24">
        <div class="mx-auto max-w-7xl px-5 sm:px-8">
            <div class="grid gap-12 lg:grid-cols-[0.9fr_1.1fr] lg:items-center">
                <div>
                    <span class="badge-brand">Cara kerja</span>
                    <h2 class="mt-4 text-balance text-3xl font-extrabold tracking-tight text-slate-900 sm:text-4xl">Tiga langkah, lalu biarkan Arus bekerja</h2>
                    <p class="mt-3 text-base leading-relaxed text-slate-500">Tidak perlu menyiapkan spreadsheet. Mulai dari hal kecil hari ini.</p>

                    <div class="mt-8 space-y-6">
                        @foreach ([
                            ['01', 'Buat akun dengan nomor HP', 'Daftar dalam hitungan detik. Dompet utama otomatis dibuat untukmu.'],
                            ['02', 'Catat arus harian', 'Masukkan pemasukan, pengeluaran, dan transfer. Kategori bawaan sudah tersedia.'],
                            ['03', 'Lihat arah keuanganmu', 'Pantau dashboard, anggaran, dan target. Ambil keputusan dengan lebih tenang.'],
                        ] as $step)
                            <div class="flex gap-4">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-slate-900 text-xs font-extrabold text-white">{{ $step[0] }}</span>
                                <div>
                                    <h3 class="text-sm font-bold text-slate-900">{{ $step[1] }}</h3>
                                    <p class="mt-1 text-sm leading-relaxed text-slate-500">{{ $step[2] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <a href="{{ route('register') }}" class="btn-primary mt-8">
                        Buat akun sekarang
                        <x-heroicon-o-arrow-right class="h-4 w-4" />
                    </a>
                </div>

                <div class="relative">
                    <div class="absolute -inset-4 rounded-[2rem] bg-gradient-to-tr from-teal-100 to-emerald-50 blur-xl"></div>
                    <div class="relative grid gap-4 sm:grid-cols-2">
                        <div class="card p-5 sm:mt-8">
                            <div class="flex items-center justify-between">
                                <p class="text-xs font-semibold text-slate-500">Pemasukan bulan ini</p>
                                <span class="badge-success">+12%</span>
                            </div>
                            <p class="money mt-2 text-2xl font-extrabold text-slate-900">Rp8.500.000</p>
                            <div class="mt-4 flex h-16 items-end gap-1.5">
                                @foreach ([38, 52, 44, 68, 58, 82, 74, 96, 88, 100] as $height)
                                    <div class="flex-1 rounded-t bg-emerald-200" style="height: {{ $height }}%"></div>
                                @endforeach
                            </div>
                        </div>
                        <div class="card p-5">
                            <div class="flex items-center justify-between">
                                <p class="text-xs font-semibold text-slate-500">Pengeluaran</p>
                                <span class="badge-warning">Perlu perhatian</span>
                            </div>
                            <p class="money mt-2 text-2xl font-extrabold text-slate-900">Rp3.240.000</p>
                            <div class="mt-4 space-y-3">
                                @foreach ([['Makan', 72, 'bg-amber-500'], ['Transport', 48, 'bg-sky-500'], ['Belanja', 36, 'bg-rose-500']] as $row)
                                    <div>
                                        <div class="mb-1 flex items-center justify-between text-[10px] font-medium text-slate-500">
                                            <span>{{ $row[0] }}</span>
                                            <span>{{ $row[1] }}%</span>
                                        </div>
                                        <div class="h-1.5 overflow-hidden rounded-full bg-slate-100">
                                            <div class="h-full rounded-full {{ $row[2] }}" style="width: {{ $row[1] }}%"></div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="card p-5 sm:col-span-2">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-violet-50 text-violet-600"><x-heroicon-o-flag class="h-5 w-5" /></span>
                                    <div>
                                        <p class="text-sm font-bold text-slate-900">Dana Darurat</p>
                                        <p class="text-xs text-slate-500">Target Rp30.000.000</p>
                                    </div>
                                </div>
                                <p class="money text-sm font-extrabold text-violet-600">68%</p>
                            </div>
                            <div class="mt-3 h-2.5 overflow-hidden rounded-full bg-slate-100">
                                <div class="h-full w-[68%] rounded-full bg-gradient-to-r from-violet-500 to-fuchsia-500"></div>
                            </div>
                            <p class="mt-2 text-xs text-slate-500">Tersisa <strong class="font-semibold text-slate-700">Rp9.600.000</strong> lagi. Sedikit demi sedikit, pasti sampai.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Why --}}
    <section id="cerita" class="bg-white py-20 sm:py-24">
        <div class="mx-auto max-w-7xl px-5 sm:px-8">
            <div class="overflow-hidden rounded-[2rem] bg-slate-950">
                <div class="relative grid gap-10 p-8 sm:p-12 lg:grid-cols-2 lg:items-center lg:p-16">
                    <div class="hero-grid absolute inset-0 opacity-40"></div>
                    <div class="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-teal-500/20 blur-3xl"></div>
                    <div class="relative">
                        <span class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-3 py-1 text-xs font-semibold text-teal-200">
                            <x-heroicon-o-heart class="h-3.5 w-3.5" />
                            Dibuat dengan niat baik
                        </span>
                        <h2 class="mt-5 text-balance text-3xl font-extrabold tracking-tight text-white sm:text-4xl">Keuangan bukan soal angka besar. Ini soal ketenangan.</h2>
                        <p class="mt-4 text-base leading-relaxed text-slate-300">
                            Arus tidak menghakimi pengeluaranmu. Kami hanya membantumu melihat gambaran utuh, agar keputusan berikutnya terasa lebih ringan.
                        </p>
                        <div class="mt-8 grid grid-cols-3 gap-4">
                            <div>
                                <p class="text-2xl font-extrabold text-white">1</p>
                                <p class="mt-1 text-xs text-slate-400">Tempat untuk semua akun</p>
                            </div>
                            <div>
                                <p class="text-2xl font-extrabold text-white">100%</p>
                                <p class="mt-1 text-xs text-slate-400">Data milikmu sendiri</p>
                            </div>
                            <div>
                                <p class="text-2xl font-extrabold text-white">Rp</p>
                                <p class="mt-1 text-xs text-slate-400">Format Indonesia</p>
                            </div>
                        </div>
                    </div>
                    <div class="relative space-y-3">
                        @foreach ([
                            ['icon' => 'sparkles', 'text' => '“Akhirnya tahu ke mana uangku pergi tanpa harus buka banyak aplikasi.”', 'name' => 'Rani', 'role' => 'Karyawan swasta'],
                            ['icon' => 'flag', 'text' => '“Target tabungan jadi kelihatan progresnya. Jadi lebih semangat menabung.”', 'name' => 'Dimas', 'role' => 'Freelancer'],
                        ] as $quote)
                            <div class="rounded-2xl border border-white/10 bg-white/5 p-5 backdrop-blur">
                                <div class="flex gap-1 text-amber-300">
                                    @for ($i = 0; $i < 5; $i++)
                                        <x-heroicon-s-star class="h-4 w-4" />
                                    @endfor
                                </div>
                                <p class="mt-3 text-sm leading-relaxed text-slate-200">{{ $quote['text'] }}</p>
                                <div class="mt-4 flex items-center gap-2.5">
                                    <span class="flex h-8 w-8 items-center justify-center rounded-full bg-teal-500/20 text-xs font-bold text-teal-200">{{ mb_substr($quote['name'], 0, 1) }}</span>
                                    <div>
                                        <p class="text-xs font-semibold text-white">{{ $quote['name'] }}</p>
                                        <p class="text-[10px] text-slate-400">{{ $quote['role'] }}</p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- CTA --}}
    <section class="bg-slate-50 py-20">
        <div class="mx-auto max-w-4xl px-5 text-center sm:px-8">
            <span class="inline-flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-teal-500 to-emerald-600 text-white shadow-lg shadow-teal-600/25">
                <x-heroicon-o-arrow-trending-up class="h-7 w-7" />
            </span>
            <h2 class="mt-5 text-balance text-3xl font-extrabold tracking-tight text-slate-900 sm:text-4xl">Mulai arus keuangan yang lebih sehat hari ini</h2>
            <p class="mx-auto mt-3 max-w-xl text-base leading-relaxed text-slate-500">Gratis, cepat, dan tanpa komitmen. Cukup nomor HP untuk memulai.</p>
            <div class="mt-7 flex flex-col justify-center gap-3 sm:flex-row">
                <a href="{{ route('register') }}" class="btn-primary btn-lg">Daftar gratis</a>
                <a href="{{ route('login') }}" class="btn-secondary btn-lg">Masuk ke akun</a>
            </div>
        </div>
    </section>

    <footer class="border-t border-slate-200 bg-white">
        <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-4 px-5 py-8 text-sm text-slate-500 sm:flex-row sm:px-8">
            <div class="flex items-center gap-2.5">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-teal-500 to-emerald-600 text-white">
                    <x-heroicon-o-arrow-trending-up class="h-4 w-4" />
                </span>
                <span class="font-extrabold text-slate-900">Arus<span class="text-teal-600">.</span></span>
                <span class="text-xs">Keuangan pribadi yang lebih tenang.</span>
            </div>
            <div class="flex items-center gap-5 text-xs">
                <a href="#fitur" class="hover:text-slate-900">Fitur</a>
                <a href="#cara-kerja" class="hover:text-slate-900">Cara Kerja</a>
                <a href="{{ route('login') }}" class="hover:text-slate-900">Masuk</a>
                <span>&copy; {{ date('Y') }} Arus</span>
            </div>
        </div>
    </footer>
</body>
</html>
