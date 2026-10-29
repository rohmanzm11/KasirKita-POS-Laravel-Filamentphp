<p align="center">
  <img src="public/images/kasir%20kita-warna.png" alt="Kasir Kita" width="220">
</p>

<h1 align="center">Kasir Kita - POS Multi-Tenant</h1>

<p align="center">
  <a href="https://laravel.com"><img src="https://img.shields.io/badge/Laravel-12-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel 12"></a>
  <a href="https://filamentphp.com"><img src="https://img.shields.io/badge/Filament-3-FDAE4B?style=for-the-badge&logoColor=white" alt="Filament 3"></a>
  <a href="https://livewire.laravel.com"><img src="https://img.shields.io/badge/Livewire-3-FB70A9?style=for-the-badge&logo=livewire&logoColor=white" alt="Livewire 3"></a>
  <a href="https://www.php.net"><img src="https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8.2+"></a>
  <a href="https://www.mysql.com"><img src="https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL"></a>
  <a href="https://tailwindcss.com"><img src="https://img.shields.io/badge/Tailwind_CSS-06B6D4?style=for-the-badge&logo=tailwindcss&logoColor=white" alt="Tailwind CSS"></a>
</p>

---

Aplikasi Point of Sale (kasir) berbasis web untuk banyak toko dalam satu sistem (multi-tenant). Dibangun dengan **Laravel 12** dan **Filament PHP v3**, dengan antarmuka berbahasa Indonesia.

Setiap toko (tenant) punya data barang, kategori, pelanggan, diskon, transaksi, dan pengguna sendiri yang terpisah dari toko lain.

## Fitur Utama

- **Penjualan (Kasir)**: halaman kasir dengan pencarian barang, keranjang belanja, pilihan pelanggan (bisa tambah pelanggan baru lewat modal), diskon, metode pembayaran, hitung kembalian otomatis, dan cetak nota.
- **Inventory**: tampilan barang dalam mode grid atau list, dengan filter kategori, status stok (aman, menipis, habis, nonaktif), dan pengurutan. Mendukung **import barang dari Excel/CSV** (maksimal 2000 baris per import) beserta template yang bisa diunduh.
- **Master Data**: barang (dengan banyak gambar), kategori, pelanggan, diskon, pengguna, dan tenant/toko.
- **Diskon**: berdasarkan persen atau potongan nominal, dengan minimal belanja dan periode berlaku.
- **Transaksi**: kode transaksi otomatis dengan format `0001/19/09/2026` (nomor urut reset tiap hari, per toko).
- **Riwayat Stok**: pencatatan mutasi stok (stok masuk, penjualan, pembatalan, penyesuaian).
- **Laporan Pemasukan**: periode harian, mingguan, bulanan, atau tahunan, dengan ekspor ke **Excel** dan **PDF**.
- **Cetak Nota**: ukuran kertas struk **58mm** atau **80mm**, dengan catatan kaki yang bisa diatur.
- **Setting Toko**: nama, kode, alamat, nomor HP, logo, ukuran kertas struk, dan catatan struk.
- **Dashboard**: widget sambutan, statistik inventori, dan pemasukan hari ini.
- **Hak akses berbasis peran** memakai Filament Shield dan Spatie Permission.

## Peran Pengguna

| Peran | Akses |
|---|---|
| **Super Admin** | Semua toko. Mengelola tenant, pengguna, peran, master data, transaksi, dan riwayat stok. |
| **Owner** | Data tokonya sendiri: Inventory, Kategori, Laporan Pemasukan, Setting Toko, dan statistik dashboard. |
| **Kasir** | Halaman Penjualan (kasir) dan Inventory. Tidak bisa melihat laporan pemasukan. |

Isolasi data antar toko dijalankan lewat trait `BelongsToTenant` (global scope berdasarkan `tenant_id`). Di level database, relasi memakai composite foreign key `(tenant_id, id)` agar data tidak bisa tercampur lintas toko.

## Teknologi

- PHP 8.2 atau lebih baru
- Laravel 12
- Filament PHP v3
- [bezhansalleh/filament-shield](https://github.com/bezhansalleh/filament-shield) dan [spatie/laravel-permission](https://github.com/spatie/laravel-permission) untuk peran dan izin
- [maatwebsite/excel](https://laravel-excel.com/) untuk import dan ekspor Excel
- [barryvdh/laravel-dompdf](https://github.com/barryvdh/laravel-dompdf) untuk ekspor PDF
- Tailwind CSS dan Vite

## Struktur Direktori Penting

```
app/
├── Exports/                  # Ekspor Excel (laporan, template import barang)
├── Imports/BarangImport.php  # Import barang dari Excel/CSV
├── Filament/
│   ├── Pages/                # Penjualan, Inventory, KategoriPage, LaporanPemasukan, SettingToko, Dashboard
│   ├── Resources/            # Barang, Diskon, Kategori, Pelanggan, StokLog, Tenant, Transaksi, User, Role
│   └── Widgets/              # Welcome, InventoryStats, PemasukanHariIni
├── Http/Controllers/NotaController.php
├── Models/                   # Model + trait Concerns/BelongsToTenant
├── Policies/                 # Policy untuk Filament Shield
└── Providers/Filament/AdminPanelProvider.php
database/migrations/          # Skema database
resources/views/
├── exports/                  # Template PDF laporan
├── filament/                 # Halaman & komponen kustom
└── nota/print.blade.php      # Template nota
routes/web.php
```

## Instalasi

### 1. Clone repositori

```bash
git clone https://github.com/rohmanzm11/KasirKita-POS-Laravel-Filamentphp.git
cd KasirKita-POS-Laravel-Filamentphp
```

### 2. Install dependensi

```bash
composer install
npm install
```

### 3. Konfigurasi environment

```bash
cp .env.example .env
php artisan key:generate
```

Lalu atur koneksi database di file `.env`:

```env
APP_NAME="Kasir Kita"
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=kasir_kita
DB_USERNAME=root
DB_PASSWORD=
```

> **Gunakan MySQL atau MariaDB.** Pembuatan kode transaksi memakai fungsi `SUBSTRING_INDEX`, yang tidak tersedia di SQLite.

### 4. Migrasi database

```bash
php artisan migrate
```

### 5. Buat symbolic link storage

Diperlukan agar logo toko dan gambar barang bisa tampil.

```bash
php artisan storage:link
```

### 6. Buat peran dan izin (Filament Shield)

```bash
php artisan shield:install admin
php artisan shield:generate --all
```

Pastikan peran **Super Admin**, **Owner**, dan **Kasir** sudah dibuat, lalu atur izin masing-masing lewat menu **Roles** di panel admin.

### 7. Buat akun Super Admin pertama

Tabel `users` mewajibkan `tenant_id`, jadi buat satu tenant terlebih dahulu lewat `php artisan tinker`:

```php
$tenant = \App\Models\Tenant::create([
    'kode' => 'pusat',
    'nama' => 'Pusat Control',
    'is_active' => true,
]);

$user = \App\Models\User::create([
    'tenant_id' => $tenant->id,
    'name' => 'Super Admin',
    'username' => 'superadmin',
    'email' => 'admin@example.com',
    'password' => 'password-yang-kuat',
    'role' => 'Super Admin',
    'is_active' => true,
]);

$user->assignRole('super_admin');
```

Sesuaikan nama peran dengan yang dibuat Shield. Pengecekan Super Admin di aplikasi ini memeriksa peran `super admin` atau kolom `role` bernilai `Super Admin`.

### 8. Jalankan aplikasi

```bash
npm run build        # atau: npm run dev
php artisan serve
```

Buka `http://localhost:8000`, Anda akan diarahkan ke halaman login panel di `/admin/login`.

## Alur Penggunaan

1. **Super Admin** membuat tenant (toko) dan akun **Owner** serta **Kasir** untuk toko tersebut.
2. **Owner** mengisi data toko di **Setting Toko**, membuat **Kategori**, lalu menambahkan barang satu per satu atau lewat **Import Barang** (unduh template dulu dari tombol *Download Template*).
3. **Kasir** membuka **Penjualan**, memilih barang ke keranjang, memilih pelanggan, diskon, dan metode pembayaran, lalu menyimpan transaksi. Stok barang berkurang otomatis dan nota bisa dicetak.
4. **Owner** memantau pemasukan lewat **Laporan Pemasukan** dan mengunduhnya sebagai Excel atau PDF.

### Format Import Barang

Kolom yang dibaca: `kode_barang`, `nama_barang`, `kategori`, `satuan`, dan seterusnya sesuai template. Nama `kategori` harus sudah ada di toko, dan `satuan` yang valid adalah `pcs`, `dos`, atau `lusin`. Baris yang gagal akan dilaporkan beserta nomor barisnya.

## Skema Database (Ringkas)

| Tabel | Keterangan |
|---|---|
| `tenants` | Data toko: kode, nama, alamat, no HP, logo, ukuran kertas struk, catatan struk |
| `users` | Pengguna per tenant (`username` unik per tenant) |
| `kategori`, `barang`, `barang_gambar` | Katalog produk dan gambarnya |
| `pelanggan`, `diskon` | Data pelanggan dan promo |
| `transaksi`, `detail_transaksi` | Penjualan dan item-itemnya |
| `stok_log` | Riwayat mutasi stok |
| `roles`, `permissions`, dst. | Tabel Spatie Permission |
| `imports`, `exports`, `failed_import_rows` | Tabel bawaan Filament untuk import/ekspor |

## Keamanan

- Jangan commit file `.env` dan jangan ubah `APP_DEBUG` menjadi `true` di server produksi.
- File `database/database.sqlite` dan isi `public/storage/` adalah data lokal. Sebaiknya masukkan keduanya ke `.gitignore` agar data tidak ikut terunggah.
- Ganti kata sandi akun awal sebelum aplikasi dipakai secara nyata.

## Lisensi

Tentukan lisensi proyek Anda di sini (misalnya MIT).
