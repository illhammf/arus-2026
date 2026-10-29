<div align="center">

# Arus

**Keuangan pribadi yang jernih, tanpa ribet.**

Aplikasi pencatatan keuangan pribadi berbahasa Indonesia dengan antarmuka pengguna yang responsif
dan panel admin Filament yang dapat dikelola sepenuhnya oleh super admin.

![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.3-777BB4?style=for-the-badge&logo=php&logoColor=white)
![Filament](https://img.shields.io/badge/Filament-3-F59E0B?style=for-the-badge)
![MariaDB](https://img.shields.io/badge/MariaDB-10.11-003545?style=for-the-badge&logo=mariadb&logoColor=white)
![Tailwind CSS](https://img.shields.io/badge/Tailwind-3-06B6D4?style=for-the-badge&logo=tailwindcss&logoColor=white)

</div>

---

## Tentang Arus

Arus membantu pengguna mencatat pemasukan, pengeluaran, transfer antar akun, anggaran, target
tabungan, dan melihat laporan keuangan dalam Rupiah. Semua data keuangan dimiliki per pengguna dan
diverifikasi kembali di sisi server pada setiap permintaan.

- **Pengguna** masuk dengan nomor HP Indonesia (format apa pun dinormalisasi ke E.164).
- **Super admin** masuk ke panel admin dengan email di `/admin`.
- **Tampilan pengguna** dibangun dengan Blade + Tailwind CSS + Heroicons, bukan panel Filament.
- **Panel admin** memakai Filament dan seluruh data dapat diedit oleh super admin.

---

## Fitur

### Pengguna

| Modul | Kemampuan |
| --- | --- |
| Dashboard | Ringkasan saldo, pemasukan, pengeluaran, arus harian, transaksi terbaru, dan anggaran bulan berjalan |
| Akun | Akun tunai, bank, e-wallet, tabungan, investasi, dan lainnya; saldo dihitung dari mutasi |
| Transaksi | Pemasukan/pengeluaran, kategori, catatan, pencarian, filter, dan pagination |
| Transfer | Transfer antar akun yang dicatat sebagai sepasang transaksi dalam satu grup |
| Anggaran | Anggaran bulanan/tahunan, per kategori atau keseluruhan, progres dan peringatan overspending |
| Target Tabungan | Target dengan tenggat, akun tertaut, setoran, progres, dan status otomatis |
| Laporan | Ringkasan bulanan, tren, komposisi kategori, dan ekspor CSV |
| Pengaturan | Profil, mata uang, zona waktu, dan ganti password |
| Autentikasi | Registrasi dengan nomor HP, login, logout, rate limiting, dan akun nonaktif ditolak |

### Admin (`/admin`)

- CRUD pengguna, akun keuangan, kategori, transaksi, anggaran, dan target tabungan.
- Pengelolaan setoran target tabungan melalui relation manager.
- Widget statistik, grafik transaksi, dan transaksi terbaru.
- Role & permission Filament Shield; hanya role `super_admin` yang dapat membuka panel.
- Branding, warna, navigasi, dan font panel sudah disesuaikan dengan Arus.

---

## Stack

- Laravel 12, PHP 8.3, MariaDB 10.11
- Filament 3, Livewire 3, Filament Shield, Spatie Permission
- Tailwind CSS 3, Vite 6, Heroicons
- Pest 3 untuk pengujian
- Docker Compose: Nginx, PHP-FPM, MariaDB

---

## Menjalankan Proyek

### 1. Jalankan container

```bash
docker compose up -d --build
```

Entrypoint container PHP akan:

1. membuat `.env` dari `.env.example` hanya jika `.env` belum ada;
2. membuat `APP_KEY` hanya jika belum ada;
3. menunggu database siap;
4. menjalankan migrasi yang belum diterapkan;
5. memperbaiki permission `storage` dan `bootstrap/cache`.

Entrypoint **tidak** menjalankan `migrate:fresh`, tidak menghapus data, dan tidak melakukan seeding
otomatis.

### 2. Siapkan data awal

```bash
docker compose exec php php artisan project:install --demo
```

Perintah ini menjalankan migrasi, membuat role, kategori sistem, akun super admin awal, lalu
membersihkan cache. Tanpa `--demo`, role dan kategori sistem tetap dibuat, tetapi akun admin tidak
dibuat otomatis; buat admin secara terpisah dengan:

```bash
docker compose exec php php artisan db:seed --class=UserSeeder
```

### 3. Build aset frontend

```bash
cd src
npm install
npm run build
```

### 4. Buka aplikasi

| Halaman | URL |
| --- | --- |
| Aplikasi | https://arus.test |
| Panel admin | https://arus.test/admin |

Tambahkan `arus.test` ke `/etc/hosts` bila belum ada:

```text
127.0.0.1 arus.test
```

---

## Akun Admin Awal

Seeder membuat akun super admin berikut:

```text
Email    : admin@admin.com
Password : password
```

> **Penting:** segera ganti password setelah login pertama. Akun ini hanya untuk panel admin dan
> tidak memiliki nomor HP. Untuk membuat pengguna biasa, buka **Admin → Pengguna → Tambah**, isi
> nomor HP, lalu bagikan kredensial awal kepada pengguna.

---

## Login Pengguna

Pengguna masuk di `/login` menggunakan nomor HP dan password. Nomor dinormalisasi otomatis, jadi
semua format berikut diterima:

```text
081234567890
+62 812 3456 7890
6281234567890
```

Login dengan email hanya tersedia untuk admin di `/admin/login`.

Verifikasi OTP/email belum diaktifkan karena penyedia OTP belum ditentukan. Kolom
`phone_verified_at` sudah disiapkan untuk kebutuhan tersebut. Jangan menambahkan OTP palsu ke
produksi; integrasikan penyedia nyata terlebih dahulu.

---

## Integritas Data Keuangan

- Semua nominal disimpan sebagai `DECIMAL(18,2)`, bukan float.
- Perhitungan uang memakai BCMath melalui `App\Support\Money`.
- Saldo akun **tidak disimpan**; saldo = saldo awal + transaksi posted, dihitung ulang setiap saat.
- Transfer selalu terdiri dari dua baris transaksi (`transfer_out` dan `transfer_in`) dengan
  `transfer_group_id` yang sama.
- Penghapusan salah satu sisi transfer menghapus kedua sisi.
- Setiap query dan policy memeriksa kepemilikan data di server, bukan hanya menyembunyikan tombol.
- Kategori sistem tidak dapat diubah atau dihapus oleh pengguna biasa.
- Penghapusan pengguna oleh super admin akan menghapus seluruh data keuangannya secara permanen
  melalui cascade database; panel admin menampilkan peringatan sebelum tindakan dilakukan.

---

## Perintah Berguna

```bash
# Migrasi
docker compose exec php php artisan migrate

# Test
docker compose exec php php artisan test

# Format kode
docker compose exec php ./vendor/bin/pint

# Build aset
cd src && npm run build

# Bersihkan cache
docker compose exec php php artisan optimize:clear

# Masuk shell container
docker compose exec php bash
```

Jalankan perintah Artisan yang menulis cache sebagai pengguna `www-data` bila memungkinkan, atau
perbaiki permission setelahnya:

```bash
docker compose exec -u www-data php php artisan view:cache
docker compose exec php chown -R www-data:www-data storage bootstrap/cache
```

---

## Pengujian

```bash
docker compose exec php php artisan test --compact
```

Test berjalan di SQLite in-memory dengan `RefreshDatabase`, mencakup autentikasi, isolasi data antar
pengguna, akun, transaksi, transfer, anggaran, target tabungan, laporan, pengaturan, dan panel admin.

Status terakhir: **94 test lulus, 403 assertion**, dan `pint --test` bersih.

---

## Struktur Penting

```text
src/
├── app/
│   ├── Enums/                  # AccountType, TransactionType, BudgetPeriod, dll.
│   ├── Filament/Admin/         # Resource, widget, dan halaman panel admin
│   ├── Http/Controllers/       # Controller UI pengguna
│   ├── Http/Requests/          # Validasi + normalisasi input uang
│   ├── Models/                 # Model keuangan
│   ├── Policies/               # Otorisasi kepemilikan data
│   ├── Services/               # TransactionService, ReportService, DashboardService
│   └── Support/                # Money dan PhoneNormalizer
├── database/migrations/        # Skema keuangan
├── database/seeders/           # Role, admin, kategori sistem
├── resources/views/            # Blade UI pengguna
├── routes/web.php              # Routing aplikasi
└── tests/                      # Pest unit + feature test
```

---

## Keamanan & Produksi

- Ganti password admin dan kredensial database sebelum deploy.
- Set `APP_ENV=production`, `APP_DEBUG=false`, dan `APP_URL` yang benar.
- Gunakan `RUN_MIGRATIONS=false` bila migrasi dijalankan sebagai langkah release terpisah.
- Jangan commit `.env` maupun private key TLS. `nginx/ssl/*.key` sudah diabaikan oleh Git.
- Sertifikat di `nginx/ssl` adalah self-signed untuk pengembangan lokal; gunakan sertifikat nyata di
  produksi.
- Batasi akses panel admin dan audit role `super_admin` secara berkala.
- Jalankan `composer audit` dan `npm audit` sebagai bagian dari pemeliharaan dependensi.

---

## Lisensi

Proyek aplikasi Arus dibangun di atas Laravel dan komponen open-source terkait.
