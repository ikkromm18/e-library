# e-Library — Implementasi Sisa Gap PRD

## Goal
Menuntaskan seluruh modul MVP dari `PRD.md` yang belum terimplementasi: Dashboard, Peminjaman, Pengembalian, Laporan, Setting, filter buku tambahan, dan seeder lengkap. Mengikuti SFC pattern repo (`resources/views/components/{domain}/{name}.php` + `.blade.php`) dan arsitektur yang sudah berjalan (Laravel 13 + Livewire 4 + Breeze Blade/Tailwind, SQLite).

## Status Gap (dari analisis PRD vs implementasi)
Belum ada: Dashboard (placeholder Breeze), Peminjaman (route placeholder), Pengembalian (route placeholder), Laporan (route placeholder), Setting UI (model `Setting` sudah ada, tanpa UI), seeder lengkap (DatabaseSeeder masih default), filter buku pengarang/penerbit/tahun.

Sudah: auth (login email — PRD minta username, diputuskan pertahankan email), ganti password, Master Buku (CRUD/search/filter kategori+rak/cover/detail), Kategori, Rak, Anggota (CRUD/search/filter status/import Excel), Pengguna admin, model `Peminjaman`/`PeminjamanDetail`/`Setting`.

## Stack & Konvensi
- Laravel 13 + Breeze (Blade/Tailwind/Alpine), SQLite, Livewire 4, PHPUnit
- `barryvdh/laravel-dompdf` (PDF), `maatwebsite/excel` (Excel) — sudah terpasang
- SFC anonymous class: `new class extends Component { ... }` dengan `render()` return `view('components.{domain}.{name}')`
- Page view di `resources/views/{domain}/index.blade.php` hanya render `<livewire:{domain} />`
- Tanpa denda; tampil "Terlambat N Hari"
- Stok computed dari pinjaman aktif (`jumlah_eksemplar − detailsAktif()->count()`), tidak pernah disimpan sebagai counter

## 1. Peminjaman

### Service & Exception
- `app/Services/PeminjamanService.php`:
  - `buatPeminjaman(int $anggotaId, array $bukuIds, int $petugasId): Peminjaman`
  - Urutan rule, throw exception: anggota nonaktif → `AnggotaNonaktifException`; ada buku terlambat belum kembali → `AnggotaTerlambatException`; `count($bukuIds) > Setting::get('maksimal_buku', 3)` → `MelebihiLimitException`; ada stok habis (`stokTersedia() < 1`) → `StokHabisException`
  - Sukses: `DB::transaction` — header `Peminjaman::create(['no_transaksi' => generateNoTransaksi(), 'tanggal' => today(), 'tanggal_jatuh_tempo' => today()->addDays(Setting::get('lama_pinjam', 7)), 'petugas_id', 'anggota_id', 'status' => 'dipinjam'])` + `PeminjamanDetail::create` per buku
- `app/Exceptions/AnggotaNonaktifException.php`, `AnggotaTerlambatException.php`, `MelebihiLimitException.php`, `StokHabisException.php` (extends `\Exception`; `StokHabisException` membawa `$buku`)

### SFC + Route
- `resources/views/components/peminjaman/peminjaman.php` + `.blade.php`
- `resources/views/peminjaman/index.blade.php`
- `routes/web.php`: `/peminjaman` → `view('peminjaman.index')` (ganti placeholder)

### Alur UI (satu halaman)
1. Cari anggota (nama/NIS live) → klik pilih → `selectedAnggota`
2. Cari buku (kode/ISBN/judul live, tampil `stokTersedia()`) → klik tambah → cart
3. Cart guard: cegah duplikat buku, cegah > `maksimal_buku`, cegah stok habis
4. Review cart → Simpan → panggil `buatPeminjaman()`; exception → flash error; sukses → flash sukses + reset cart

Stok re-check di service saat simpan (bukan hanya UI), antisipasi perubahan antar langkah.

### Test
- Unit `tests/Unit/PeminjamanServiceTest.php`: tiap rule throw; sukses turunkan stok; format `no_transaksi` regex `PJM-\d{8}-\d+`; jatuh tempo = today + setting
- Feature `tests/Feature/Livewire/PeminjamanTest.php`: alur tambah cart + simpan; limit cart; anggota nonaktif ditolak (flash/DB assertion)

## 2. Pengembalian
- SFC `components/pengembalian/pengembalian.php` + `.blade.php`, page `pengembalian/index.blade.php`
- Route `/pengembalian` → `view('pengembalian.index')` (ganti placeholder)
- Alur: cari transaksi (`no_transaksi` ATAU nama/NIS anggota, live) → pilih transaksi `status=dipinjam` → tampil header (no transaksi, anggota, petugas, tanggal, jatuh tempo) + list detail (judul, `tanggal_kembali`, badge) → tombol Kembalikan per item belum kembali
- Kembalikan item: `tanggal_kembali=today()`, `keterlambatan_hari = peminjaman->hitungKeterlambatan()` (reuse model). Semua item kembali → header `status=selesai`
- Badge: `keterlambatan_hari > 0` → "Terlambat N Hari", else "Tepat Waktu"
- Stok naik otomatis (computed)
- Test `tests/Feature/Livewire/PengembalianTest.php`: tepat waktu (stok naik, `keterlambatan_hari` null); terlambat (`keterlambatan_hari` = selisih); semua kembali → `selesai`

## 3. Dashboard
- SFC `components/dashboard/dashboard.php` + `.blade.php`; `dashboard.blade.php` render `<livewire:dashboard />`
- Widget (6 kartu): jumlah buku (`Buku::sum('jumlah_eksemplar')`), jumlah judul (`Buku::count()`), jumlah anggota (`Anggota::count()`), sedang dipinjam (`PeminjamanDetail` aktif `tanggal_kembali` null), terlambat (detail aktif + `peminjaman.tanggal_jatuh_tempo < today()`), pengembalian hari ini (detail `tanggal_kembali = today()`)
- Grafik: peminjaman 7 hari terakhir (`where('tanggal','>=',today()->subDays(6))`, group by tanggal, count) — bar sederhana div width, tanpa lib JS
- Shortcut: Tambah Buku → `/buku`, Pinjam Buku → `/peminjaman`, Kembalikan Buku → `/pengembalian`
- Test `tests/Feature/Livewire/DashboardTest.php`: seed data → assert nilai widget + jumlah bar grafik

## 4. Laporan
- SFC `components/laporan/laporan.php` + `.blade.php`, page `laporan/index.blade.php`
- `app/Http/Controllers/ReportController.php`
- `app/Exports/BukuExport.php`, `AnggotaExport.php`, `TransaksiExport.php` (FromQuery/FromCollection + WithHeadings)
- View PDF: `resources/views/reports/pdf/{buku,anggota,transaksi}.blade.php`
- Route: `/laporan` → `view('laporan.index')`; `/laporan/export/{tipe}/{format}` (`pdf|excel`), query param filter

### Halaman (tab + filter + preview + export)
- **Buku**: filter semua/kategori/rak; kolom kode, ISBN, judul, kategori, pengarang, penerbit, tahun, rak, stok, status
- **Anggota**: filter semua/aktif; kolom NIS, nama, kelas, jenis kelamin, status
- **Transaksi**: filter peminjaman/pengembalian/terlambat
  - peminjaman: no transaksi, tanggal, anggota, petugas, jumlah buku, status
  - pengembalian: no transaksi, tanggal kembali, buku, anggota
  - terlambat: no transaksi, anggota, buku, jatuh tempo, keterlambatan hari

### Export
- PDF: dompdf render view PDF, dataset sama dengan preview
- Excel: `Excel::download` pakai export class
- Diswitch `pdf|excel`; filter diteruskan sebagai argumen

### Test `tests/Feature/ReportTest.php`
- Export PDF buku → content-type `application/pdf`, ok
- Export Excel anggota → content-type spreadsheet, ok
- Filter transaksi terlambat benar

## 5. Setting (admin)
- SFC `components/setting/setting.php` + `.blade.php`, page `setting/index.blade.php`
- Route `/setting` → `view('setting.index')` (tetap `can:manage-system`)
- Form 4 field: nama perpus (`nama_perpus`, default "Perpustakaan"), logo upload (`logo`, simpan disk public path `logos`, preview), lama pinjam (`lama_pinjam`, default 7), maksimal buku (`maksimal_buku`, default 3)
- Simpan via `Setting::set()` per field; cache dibersihkan otomatis (model sudah handle)
- `tanggal_libur` PRD dilewati (opsional, di luar MVP)
- Test `tests/Feature/Livewire/SettingPageTest.php`: simpan semua field → tersimpan + cache di-refresh; logo upload tersimpan di storage

## 6. Filter Buku Tambahan
- Modifikasi `components/buku/buku.php` + `.blade.php`
- Tambah 3 select: `filterPengarang`, `filterPenerbit`, `filterTahun` — opsi dari nilai distinct
- Update `queryBuku()` dengan `when()` (pattern sama kategori/rak); reset page tiap ganti filter
- Test: tambah case filter di `tests/Feature/Livewire/BookTest.php`

## 7. Seeder Lengkap
- Ganti isi `database/seeders/DatabaseSeeder.php` → panggil seeder baru
- Baru: `KategoriSeeder` (6: Pelajaran, Novel, Agama, Ensiklopedia, Komik Edukasi, Majalah), `RakSeeder` (A-01..A-05, B-01..B-05, kode fixed), `UserSeeder` (1 admin, 2 petugas), `BookSeeder` (40 buku, sebagian status `tidak`), `AnggotaSeeder` (200, sebagian nonaktif/lulus/pindah), `TransaksiSeeder` (~30 header aktif/terlambat/selesai, detail 1-3 buku)
- Seeder tak boleh bentrok kode Rak dengan factory acak

## 8. Fix Infrastruktur
- `composer.json` script `test`: hapus `@no_additional_args` (arg tak dikenal) agar `composer test` jalan kembali (dipakai verifikasi)

## Verifikasi Akhir
```bash
php artisan migrate:fresh --seed
php artisan test
vendor/bin/pint
npm run build
```
Semua hijau. Smoke manual: login admin → pinjam → kembali → laporan export → setting.

## Batasan
- Tanpa denda (hanya tampil keterlambatan hari)
- Tanpa register/forgot password/OTP
- Tanpa QR/OPAC/e-book/reservasi/WA Gateway (di luar MVP)

## Out of Scope
- `tanggal_libur` pada Setting
- Denda / pembayaran
- Peminjaman — halaman daftar transaksi terpisah (cukup alur form + flash)
