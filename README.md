# E-Library (Aplikasi Perpustakaan Digital)

Aplikasi manajemen perpustakaan berbasis web dengan antarmuka berbahasa Indonesia. Dibangun dengan **Laravel 13**, **Livewire 4.3** (pola single-file component), **Breeze** (Blade + Tailwind CSS), dan database **SQLite**.

## Fitur

| Modul | Deskripsi |
|---|---|
| **Dashboard** | Widget total buku/eksemplar/anggota, buku sedang dipinjam, terlambat, kembali hari ini, serta grafik peminjaman 7 hari terakhir |
| **Buku** | CRUD buku, pencarian & filter (kategori, rak, pengarang, penerbit, tahun), kode buku otomatis, cek stok tersedia |
| **Kategori** | CRUD kategori buku |
| **Rak** | CRUD lokasi rak buku |
| **Anggota** | CRUD anggota, pencarian & filter, import anggota dari Excel (dengan preview & template), cek duplikat |
| **Peminjaman** | Cari anggota, pilih buku, batas jumlah & lama pinjam (dari pengaturan), validasi stok, aturan peminjaman via `PeminjamanService` |
| **Pengembalian** | Cari transaksi peminjaman, kembalikan per buku, hitung keterlambatan otomatis |
| **Laporan** | Preview laporan buku / anggota / transaksi (peminjaman, pengembalian, terlambat), ekspor **PDF** & **Excel** |
| **Pengguna** | Manajemen user (admin/petugas) — khusus admin |
| **Pengaturan** | Nama perpustakaan, logo (upload), lama pinjam, maksimal buku — khusus admin |

## Alur Kerja per Modul

Berikut penjelasan bagaimana setiap modul bekerja, dari sudut pandang penggunaan harian petugas perpustakaan.

### Setup Awal Aplikasi
1. Login sebagai **admin** → menu **Pengaturan**, isi nama perpustakaan, upload logo, atur **lama pinjam** (hari) dan **maksimal buku** per peminjaman.
2. Lewat menu **Kategori** dan **Rak**, siapkan pengelompokan koleksi.
3. Buat anggota lewat menu **Anggota** (manual atau import Excel).
4. Tambah buku lewat menu **Buku** — kode buku dibuat otomatis, tentukan kategori & rak, serta jumlah eksemplar.

### Buku
1. Buka menu **Buku** → klik **Tambah Buku** → isi data (judul, pengarang, penerbit, tahun, kategori, rak, jumlah eksemplar).
2. Kode buku (`BUK-xxxx`) terisi otomatis dan tidak bisa bentrok.
3. Gunakan kotak pencarian + filter (kategori, rak, pengarang, penerbit, tahun) untuk menyaring daftar.
4. Buku yang sudah punya riwayat pinjam tidak bisa dihapus; stok tidak bisa dikurangi di bawah jumlah yang sedang dipinjam.

### Anggota
1. Admin/petugas membuat record anggota (NIS, nama, kelas, jenis kelamin, kontak) atau **mengimpor** dari Excel.
2. Import Excel: unduh **template** → isi di Excel → halaman **Import** menampilkan **preview** + hitungan (berhasil / dilewati / error) → konfirmasi simpan. Baris tidak valid dilewati tanpa menggagalkan seluruh file.
3. Ubah status anggota bila perlu (aktif, lulus, pindah, nonaktif — mis. karena lulus sekolah).

### Peminjaman
1. Buka menu **Peminjaman** → cari **anggota berdasarkan NIS/nama** → pilih buku.
2. Sistem validasi otomatis oleh `PeminjamanService: jumlah buku tidak melebihi **maksimal buku**, anggota masih **aktif**, stok buku **tersedia**, durasi mengikuti **lama pinjam** dari pengaturan.
3. Transaksi tersimpan dengan `no_transaksi` otomatis dan tanggal jatuh tempo.

### Pengembalian
1. Buka menu **Pengembalian** → cari berdasarkan **no_transaksi** (data transaksi aktif muncul).
2. Klik kembali per buku yang dikembalikan.
3. Sistem menghitung **keterlambatan** otomatis jika melewati jatuh tempo (0 = tepat waktu).
4. Jika semua buku sudah kembali, status transaksi berubah **selesai**.

### Dashboard
- Terlihat di halaman utama setelah login: total buku (judul & eksemplar), total anggota, jumlah sedang dipinjam, terlambat, kembali hari ini, serta **grafik peminjaman 7 hari terakhir**.
- Tautan cepat ke menu Buku / Peminjaman / Pengembalian.

### Laporan
1. Buka menu **Laporan** → pilih tab **Buku / Anggota / Transaksi**.
2. Terapkan filter (mis. kategori & rak untuk buku, status untuk anggota, jenis transaksi: peminjaman/pengembalian/terlambat).
3. Data muncul sebagai **preview** di layar → klik **Export PDF** atau **Export Excel**.

### Pengguna (khusus admin)
Membuat / mengedit / menonaktifkan akun admin & petugas. Mengontrol siapa yang bisa mengelola sistem.

### Pengaturan (khusus admin)
Menyimpan identitas perpustakaan (nama, logo), serta mengubah **lama pinjam** dan **maksimal buku** — nilai ini langsung dipakai aturan peminjaman.

---

## Teknologi

- PHP ^8.3
- Laravel 13
- Livewire 4.3 (anonymous component — kode di `resources/views/components/{modul}/{modul}.php` + `.blade.php`)
- Laravel Breeze (Blade + Tailwind CSS 3)
- SQLite (`database/database.sqlite`)
- maatwebsite/excel (import/export Excel)
- barryvdh/laravel-dompdf (export PDF)
- Vite (build aset frontend)

## Persyaratan

- PHP **8.3+** (dengan ekstensi `pdo_sqlite`, `mbstring`, `fileinfo`, `gd`/`imagick` untuk image)
- Composer 2
- Node.js **20+** (disarankan 22 LTS)
- Git

## Instalasi di Laptop Baru

```bash
# 1. Clone repositori
git clone git@github.com:ikkromm18/e-library.git
cd e-library

# 2. Install dependency PHP
composer install

# 3. Buat file .env
cp .env.example .env

# 4. Generate application key
php artisan key:generate

# 5. Buat file database SQLite
touch database/database.sqlite

# 6. Jalankan migrasi + seeder (data demo + akun awal)
php artisan migrate:fresh --seed

# 7. Link storage (agar logo / cover bisa diakses)
php artisan storage:link

# 8. Install & build aset frontend
npm install
npm run build

# 9. Jalankan server
php artisan serve
```

Akses aplikasi di `http://127.0.0.1:8000`.

> **Alternatif cepat:** beberapa langkah di atas sudah dirangkum dalam skrip `composer setup` (install + `.env` + key + migrate + build aset). Tapi tetap jalankan `php artisan migrate:fresh --seed` setelahnya agar ada data demo, dan `php artisan storage:link` untuk folder storage.

## Akun Login (dari seeder)

| Role | Email | Password |
|---|---|---|
| **Admin** | `admin@perpus.test` | `admin123` |
| **Petugas 1** | `petugas1@perpus.test` | `password` |
| **Petugas 2** | `petugas2@perpus.test` | `password` |

Menu **Pengguna** dan **Pengaturan** hanya bisa diakses akun **admin**.

## Menjalankan Mode Pengembangan

```bash
composer dev
```

Perintah di atas menjalankan server, antrian (`queue:listen`), log (`pail`), dan Vite secara bersamaan. Atau jalankan terpisah:

```bash
php artisan serve        # terminal 1
npm run dev              # terminal 2
```

## Menjalankan Test

```bash
composer test
# atau
php artisan test
```

Seluruh 99 test / 218 assertion dipastikan lolos pada cabang `main`.

## Data Demo dari Seeder

`php artisan migrate:fresh --seed` menghasilkan:

- 6 kategori, 10 rak (A-01 s/d B-05)
- 40 buku (terhubung ke kategori & rak terseed)
- 200 anggota (campuran status aktif/nonaktif/lulus/pindah)
- 30 transaksi peminjaman (sebagian sudah dikembalikan / terlambat)
- 1 admin + 2 petugas

## Catatan Teknis

- Komponen Livewire menggunakan **pola single-file component** (anonymous class), bukan `app/Livewire/`. Contoh: `resources/views/components/peminjaman/peminjaman.php` + `peminjaman.blade.php`.
- Modul laporan memakai class export yang sama (`app/Exports/`) untuk preview maupun unduhan PDF/Excel.
- Stok yang tersedia dihitung dari `jumlah_eksemplar` dikurangi detail peminjaman aktif.

## Lisensi

Proyek ini berlisensi **MIT**.
