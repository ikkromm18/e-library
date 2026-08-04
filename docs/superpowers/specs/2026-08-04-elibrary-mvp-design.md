# e-Library MVP Design

## Goal
MVP sistem perpustakaan SMP menggantikan pencatatan manual (buku tulis/Excel). Menuntaskan seluruh proses operasional harian: login, kelola buku, kelola anggota, pinjam, kembali, laporan.

## Stack
- Laravel 13 + Breeze (Blade/Tailwind/Alpine), SQLite
- livewire/livewire, barryvdh/laravel-dompdf, maatwebsite/excel
- PHPUnit

## Data Model
- `users`: + `role` enum `admin|petugas`, `is_active` boolean
- `kategoris`: nama
- `raks`: kode (unique), nama, keterangan
- `bukus`: kode auto `BUK-0001`, isbn unique nullable, judul, sub_judul, kategori_id, pengarang, penerbit, tahun, bahasa, rak_id, jumlah_eksemplar, deskripsi, cover, status `aktif|tidak`
- `anggota`: nis unique, nama, jenis_kelamin `L|P`, kelas, alamat, no_hp, tanggal_masuk, status `aktif|lulus|pindah|nonaktif`
- `peminjamen`: no_transaksi auto `PJM-YYYYMMDD-N`, tanggal, tanggal_jatuh_tempo, petugas_id, anggota_id, status `dipinjam|selesai`
- `peminjaman_details`: peminjaman_id, buku_id, tanggal_kembali nullable, keterlambatan_hari nullable
- `settings`: key/value

Stok tersedia = `jumlah_eksemplar − jumlah detail aktif belum kembali`. Tidak ada counter stok tersimpan.

## Rules
- Tanpa register/forgot password/OTP. User dibuat admin.
- Max buku pinjam & lama pinjam dari settings (default 3, 7 hari).
- Pinjam dilarang jika: anggota nonaktif, ada buku terlambat belum kembali, stok habis, melebihi limit.
- Simpan pinjam → stok berkurang (computed).
- Kembali per item → `tanggal_kembali=now`, hitung `keterlambatan_hari`.
- Tanpa denda; tampil "Terlambat N Hari".
- Laporan export PDF + Excel.

## Modules
1. Authentication — login/logout/ganti password
2. Dashboard — widget count + grafik 7 hari + shortcut
3. Master Buku — CRUD, search (ISBN/kode/judul/pengarang), filter (kategori/rak/pengarang/penerbit/tahun)
4. Kategori Buku — CRUD
5. Rak Buku — CRUD
6. Master Anggota — CRUD, search, filter status, import Excel
7. Peminjaman — flow cari anggota → pilih buku → daftar → simpan
8. Pengembalian — cari transaksi → pilih buku → kembali per item
9. Laporan — buku (semua/kategori/rak), anggota (semua/aktif), transaksi (pinjam/kembali/terlambat), export PDF/Excel
10. Setting — nama perpus, logo, lama pinjam, maksimal buku
11. (Admin) Pengguna — buat petugas, reset password
