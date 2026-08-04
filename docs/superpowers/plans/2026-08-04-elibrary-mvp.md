# e-Library MVP Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Built MVP perpustakaan SMP — 6 core flows (login, buku, anggota, pinjam, kembali, laporan) menggantikan pencatatan manual.

**Architecture:** Laravel 13 + Breeze Blade layout + Livewire full-page components per module. Stok computed dari item pinjaman aktif (`jumlah_eksemplar − dipinjam aktif`) — tanpa counter tersimpan. Model transaksi: header (`peminjamen`) + item (`peminjaman_details`).

**Tech Stack:** Laravel 13, Livewire 3, Tailwind (Breeze), SQLite, dompdf, maatwebsite/excel, PHPUnit.

## Global Constraints
- Tanpa register/forgot-password/OTP (user dibuat admin)
- `no_transaksi` format `PJM-YYYYMMDD-N`; `kode buku` format `BUK-0001`; keduanya auto
- ISBN unique, nullable; judul/kategori/rak wajib di buku
- Default settings: `lama_pinjam=7`, `maksimal_buku=3`
- Tanpa denda — hanya tampil `Terlambat N Hari`
- Stok tidak pernah disimpan sebagai counter — selalu dihitung

---

## Task 1: Packages + Livewire boot

**Files:**
- Modify: `composer.json` (via composer require)
- Modify: `resources/views/layouts/app.blade.php`
- Test: adhoc route render

**Interfaces:**
- Produces: Livewire 3 terpasang, styles/scripts di layout app.

- [ ] **Step 1: Install packages**
```bash
composer require livewire/livewire barryvdh/laravel-dompdf maatwebsite/excel
```
- [ ] **Step 2: Add Livewire directives to Breeze app layout**
Di `resources/views/layouts/app.blade.php`, pastikan `@livewireStyles` sebelum `@vite` di `<head>` dan `@livewireScripts` sebelum `</body>`.
- [ ] **Step 3: Smoke test**
```bash
php artisan route:list | grep livewire
php artisan livewire:make Dashboard --test
```
Expected: command jalan, file `app/Livewire/Dashboard.php` + `tests/Feature/Livewire/DashboardTest.php` dibuat.
- [ ] **Step 4: Commit**
```bash
git add -A && git commit -m "build: install livewire, dompdf, maatwebsite/excel"
```

## Task 2: Settings infra

**Files:**
- Create: `database/migrations/2026_08_04_000002_create_settings_table.php`
- Create: `app/Models/Setting.php`
- Test: `tests/Feature/SettingTest.php`

**Interfaces:**
- Produces: `Setting::get($key, $default)`, `Setting::set($key, $value)`.

- [ ] **Step 1: Migration**
```php
Schema::create('settings', function (Blueprint $table) {
    $table->id();
    $table->string('key')->unique();
    $table->text('value')->nullable();
    $table->timestamps();
});
```
- [ ] **Step 2: Model Setting**
```php
class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    public static function get(string $key, $default = null)
    {
        return Cache::rememberForever("setting.$key", function () use ($key, $default) {
            return static::where('key', $key)->value('value') ?? $default;
        });
    }

    public static function set(string $key, $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget("setting.$key");
    }
}
```
- [ ] **Step 3: Test**
```php
public function test_set_dan_get(): void
{
    Setting::set('lama_pinjam', 7);
    $this->assertEquals('7', Setting::get('lama_pinjam'));
    $this->assertEquals('default', Setting::get('tidak_ada', 'default'));
    $this->assertTrue(Cache::has('setting.lama_pinjam'));
}
```
- [ ] **Step 4: Run test** — `php artisan test --filter=SettingTest` → PASS
- [ ] **Step 5: Commit**

## Task 3: Core schema + models

**Files:**
- Create migrations: `kategoris`, `raks`, `bukus`, `anggota`, `peminjamen`, `peminjaman_details`, `add_role_to_users`
- Create models: `Kategori`, `Rak`, `Buku`, `Anggota`, `Peminjaman`, `PeminjamanDetail`
- Modify: `app/Models/User.php` (+role)
- Create factories: `BukuFactory`, `AnggotaFactory`, `PeminjamanFactory`
- Test: `tests/Feature/ModelTest.php`

**Interfaces:**
- `Buku::stokTersedia(): int` = `jumlah_eksemplar − detailsAktif()->count()`
- `Buku::detailsAktif()` = detail via `peminjaman` status `dipinjam` dan `tanggal_kembali null`
- `Peminjaman::hitungKeterlambatan(?Carbon $dikembalikan = null): int`
- `Peminjaman::generateNoTransaksi(): string` = `PJM-YYYYMMDD-N` (count hari itu + 1, pad)
- `User::isAdmin(): bool`, `User::isPetugas(): bool`

- [ ] **Step 1: Migration schemas**
```php
// kategoris
Schema::create('kategoris', fn ($t) => $t->id() + $t->string('nama') + $t->timestamps());
// raks: id, kode string unique, nama string, keterangan text nullable, timestamps
// bukus: id, kode string unique, isbn string nullable unique, judul string, sub_judul string nullable,
//        kategori_id FK, pengarang string nullable, penerbit string nullable, tahun smallInteger nullable,
//        bahasa string nullable, rak_id FK, jumlah_eksemplar unsignedInteger default 1,
//        deskripsi text nullable, cover string nullable, status enum(['aktif','tidak']) default 'aktif', timestamps
// anggota: id, nis string unique, nama string, jenis_kelamin enum(['L','P']), kelas string nullable,
//          alamat text nullable, no_hp string nullable, tanggal_masuk date, status enum(['aktif','lulus','pindah','nonaktif']) default 'aktif', timestamps
// peminjamen: id, no_transaksi string unique, tanggal date, tanggal_jatuh_tempo date,
//             petugas_id FK users, anggota_id FK, status enum(['dipinjam','selesai']) default 'dipinjam', timestamps
// peminjaman_details: id, peminjaman_id FK, buku_id FK, tanggal_kembali date nullable, keterlambatan_hari int nullable, timestamps
// add_role_to_users: role enum(['admin','petugas']) default 'petugas'
```
- [ ] **Step 2: Models + relations**
```php
class Buku extends Model {
    protected $fillable = ['kode','isbn','judul','sub_judul','kategori_id','pengarang','penerbit','tahun','bahasa','rak_id','jumlah_eksemplar','deskripsi','cover','status'];
    public function kategori() { return $this->belongsTo(Kategori::class); }
    public function rak() { return $this->belongsTo(Rak::class); }
    public function detailsAktif() {
        return $this->hasMany(PeminjamanDetail::class)
            ->whereHas('peminjaman', fn($q) => $q->where('status','dipinjam'))
            ->whereNull('tanggal_kembali');
    }
    public function stokTersedia(): int { return $this->jumlah_eksemplar - $this->detailsAktif()->count(); }
    public static function buatKode(): string {
        $n = static::count() + 1;
        while (static::where('kode', $kode = 'BUK-'.str_pad($n, 4, '0', STR_PAD_LEFT))->exists()) $n++;
        return $kode;
    }
}
// Peminjaman::generateNoTransaksi()
public static function generateNoTransaksi(): string {
    $prefix = 'PJM-'.now()->format('Ymd');
    $count  = static::whereDate('created_at', today())->count() + 1;
    while (static::where('no_transaksi', $no = $prefix.'-'.$count)->exists()) $count++;
    return $no;
}
public function hitungKeterlambatan(?Carbon $dikembalikan = null): int {
    $end = $dikembalikan ?? now();
    return $end->gt($this->tanggal_jatuh_tempo) ? $end->diffInDays($this->tanggal_jatuh_tempo) : 0;
}
// User: 'role' cast + isAdmin()/isPetugas()
```
- [ ] **Step 3: Factories** — BukuFactory (faker, kode otomatis `BUK-xxxx`, kategori_id default make), AnggotaFactory (nis unik), PeminjamanFactory (header + state `selesai`)
- [ ] **Step 4: ModelTest**
```php
public function test_stok_tersedia_dihitung_dari_pinjaman_aktif()
{
    $buku = Buku::factory()->create(['jumlah_eksemplar' => 3]);
    $this->assertEquals(3, $buku->stokTersedia());
    $p = Peminjaman::factory()->create();
    PeminjamanDetail::create(['peminjaman_id'=>$p->id,'buku_id'=>$buku->id]);
    $buku->refresh();
    $this->assertEquals(2, $buku->stokTersedia());
}
public function test_keterlambatan_dihitung() { /* jatuh tempo kemarin -> 1+ hari */ }
public function test_format_no_transaksi() { /* regex PJM-\d{8}-\d+ */ }
```
- [ ] **Step 5: Run** `php artisan migrate` + `php artisan test --filter=ModelTest` → PASS
- [ ] **Step 6: Commit**

## Task 4: Auth roles + navigation

**Files:**
- Modify: `app/Models/User.php` (is_active)
- Create: `database/migrations/..._add_is_active_to_users_table.php`
- Modify: `routes/web.php`
- Modify: `resources/views/layouts/navigation.blade.php`
- Test: `tests/Feature/AuthRoleTest.php`

- [ ] **Step 1: Migration** `users.is_active boolean default true`
- [ ] **Step 2: Login guard** — di `app/Http/Controllers/Auth/AuthenticatedSessionController.php`, setelah autentikasi, blokir jika `! $user->is_active` (logout + redirect login error).
- [ ] **Step 3: Routes** — protect admin-only (pengguna, setting) via `->middleware('can:manage-system')`. Daftarkan policy di `app/Providers/AppServiceProvider.php`:
```php
Gate::define('manage-system', fn (User $u) => $u->isAdmin());
```
- [ ] **Step 4: Navigation menu** — menu auth: Dashboard, Buku, Kategori, Rak, Anggota, Peminjaman, Pengembalian, Laporan. Admin-only: Pengguna, Setting.
- [ ] **Step 5: Test**
```php
public function test_petugas_tidak_bisa_akses_halaman_admin()
public function test_user_nonaktif_tidak_bisa_login()
```
- [ ] **Step 6: Run** `php artisan test --filter=AuthRoleTest` → PASS
- [ ] **Step 7: Commit**

## Task 5: User management (admin)

**Files:**
- Create: `app/Livewire/UserIndex.php` + `resources/views/livewire/user-index.blade.php`
- Modify: `routes/web.php` (route `/pengguna`)
- Test: `tests/Feature/Livewire/UserManagementTest.php`

- [ ] **Step 1: Livewire component** — tabel user (nama, username, role, is_active), form tambah (nama, username, email, role, password), edit role, toggle aktif, reset password (password baru).
- [ ] **Step 2: View** — table + modal form (reuse Breeze modal component).
- [ ] **Step 3: Test**
```php
public function test_admin_buat_petugas()
public function test_toggle_nonaktif_user()
```
- [ ] **Step 4: Run** `php artisan test --filter=UserManagementTest` → PASS
- [ ] **Step 5: Commit**

## Task 6: Kategori CRUD

**Files:**
- Create: `app/Livewire/CategoryIndex.php` + view `category-index.blade.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/Livewire/CategoryTest.php`

- [ ] **Step 1: Component** — tabel + form inline (nama), edit, delete. Hapus ditolak bila ada `buku` terkait (`redirect` + flash error).
- [ ] **Step 2: View**
- [ ] **Step 3: Test**
```php
public function test_buat_kategori()
public function test_hapus_kategori_dengan_buku_ditolak()
```
- [ ] **Step 4: Run** → PASS
- [ ] **Step 5: Commit**

## Task 7: Rak CRUD

**Files:**
- Create: `app/Livewire/RakIndex.php` + view
- Modify: `routes/web.php`
- Test: `tests/Feature/Livewire/RakTest.php`

- [ ] **Step 1: Component** — CRUD kode/nama/keterangan, kode unique, hapus ditolak bila ada buku.
- [ ] **Step 2: View**
- [ ] **Step 3: Test** — create, duplicate kode ditolak, delete guard
- [ ] **Step 4: Run** → PASS
- [ ] **Step 5: Commit**

## Task 8: Buku CRUD + search/filter

**Files:**
- Create: `app/Livewire/BookIndex.php`, `app/Livewire/BookForm.php` + views `book-index.blade.php`, `book-form.blade.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/Livewire/BookTest.php`

**Interfaces:**
- Uses: `Buku::buatKode()`, `Buku::stokTersedia()`, `Setting::get('lama_pinjam')` (tidak di sini), kategori/rak relasi.

- [ ] **Step 1: BookIndex** — pencarian (isbn/kode/judul/pengarang), filter (kategori_id, rak_id, pengarang, penerbit, tahun), paginate 10, tombol detail (modal) + tautan form edit/hapus.
- [ ] **Step 2: BookForm** — form create/edit: kode auto saat create (readonly), isbn unique (rule `unique:bukus,isbn` + ignore saat edit), judul/kategori/rak wajib, cover upload `storage/app/public/covers`, status toggle, tampil `stokTersedia` saat edit.
- [ ] **Step 3: Views** — reuse Breeze form component.
- [ ] **Step 4: Test**
```php
public function test_kode_buku_otomatis()
public function test_isbn_duplikat_ditolak()
public function test_judul_kategori_rak_wajib()
public function test_search_filter_buku()
public function test_upload_cover()
```
- [ ] **Step 5: Run** → PASS
- [ ] **Step 6: Commit**

## Task 9: Anggota CRUD + search

**Files:**
- Create: `app/Livewire/MemberIndex.php` + view
- Modify: `routes/web.php`
- Test: `tests/Feature/Livewire/MemberTest.php`

- [ ] **Step 1: Component** — CRUD, search (nis/nama/kelas), filter status, detail.
- [ ] **Step 2: View**
- [ ] **Step 3: Test**
```php
public function test_buat_anggota()
public function test_nis_duplikat_ditolak()
public function test_filter_status()
```
- [ ] **Step 4: Run** → PASS
- [ ] **Step 5: Commit**

## Task 10: Import Excel anggota

**Files:**
- Create: `app/Imports/AnggotaImport.php`
- Create: `app/Livewire/MemberImport.php` + view `member-import.blade.php`
- Create: `app/Exports/AnggotaTemplateExport.php`
- Modify: `routes/web.php` (template download)
- Test: `tests/Feature/Livewire/MemberImportTest.php`

**Interfaces:**
- Produces: `AnggotaImport` `model()` dengan validasi NIS unique (skip duplikat).
- Template kolom: `nis, nama, jenis_kelamin(L/P), kelas, alamat, no_hp, tanggal_masuk, status`.

- [ ] **Step 1: AnggotaImport** — `WithHeadingRow`, `SkipsOnFailure`/`SkipsOnError`, row invalid → skip. `model()` cek `Anggota::where('nis',...)->exists()`.
- [ ] **Step 2: MemberImport component** — upload file (xlsx), preview baris valid, tombol konfirmasi import (loop `Anggota::create`), laporan sukses/skip.
- [ ] **Step 3: Template export** — heading row kosong.
- [ ] **Step 4: Test**
```php
public function test_import_file_valid()
public function test_nis_duplikat_dilewati()
public function test_download_template()
```
- [ ] **Step 5: Run** → PASS
- [ ] **Step 6: Commit**

## Task 11: Peminjaman

**Files:**
- Create: `app/Services/PeminjamanService.php`
- Create exceptions: `AnggotaNonaktifException`, `AnggotaTerlambatException`, `MelebihiLimitException`, `StokHabisException` (di `app/Exceptions/`)
- Create: `app/Livewire/BorrowCreate.php` + view `borrow-create.blade.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/Livewire/BorrowRuleTest.php` + `tests/Unit/PeminjamanServiceTest.php`

**Interfaces:**
- `PeminjamanService::buatPeminjaman(int $anggotaId, array $bukuIds, int $petugasId): Peminjaman`
- Uses: `Setting::get('maksimal_buku', 3)`, `Setting::get('lama_pinjam', 7)`, `Peminjaman::generateNoTransaksi()`.

- [ ] **Step 1: Service + exceptions**
```php
public function buatPeminjaman(int $anggotaId, array $bukuIds, int $petugasId): Peminjaman
{
    $anggota = Anggota::findOrFail($anggotaId);
    if ($anggota->status !== 'aktif') throw new AnggotaNonaktifException;
    $terlambat = PeminjamanDetail::whereHas('peminjaman', fn($q) => $q->where('status','dipinjam')->where('anggota_id',$anggotaId))
        ->whereNull('tanggal_kembali')->whereHas('peminjaman', fn($q) => $q->whereDate('tanggal_jatuh_tempo','<',today()))
        ->exists();
    if ($terlambat) throw new AnggotaTerlambatException;
    $max = (int) Setting::get('maksimal_buku', 3);
    if (count($bukuIds) > $max) throw new MelebihiLimitException;
    foreach ($bukuIds as $bukuId) {
        $buku = Buku::findOrFail($bukuId);
        if ($buku->stokTersedia() < 1) throw new StokHabisException($buku);
    }
    $lama = (int) Setting::get('lama_pinjam', 7);
    return DB::transaction(function () use (...) {
        $p = Peminjaman::create([... 'no_transaksi' => Peminjaman::generateNoTransaksi(),
            'tanggal' => today(), 'tanggal_jatuh_tempo' => today()->addDays($lama), ...]);
        foreach ($bukuIds as $bukuId) PeminjamanDetail::create(['peminjaman_id'=>$p->id,'buku_id'=>$bukuId]);
        return $p;
    });
}
```
- [ ] **Step 2: BorrowCreate component** — step1 cari anggota (result pilih), step2 cari buku (tampil stok) + tombol tambah ke cart (cegah > max & stok habis), step3 review + simpan → redirect ke halaman detail transaksi. Error exceptions → flash message.
- [ ] **Step 3: View**
- [ ] **Step 4: Unit test service** — tiap rule throw; sukses: stokTersedia turun, format no_transaksi benar, jatuh tempo = today+settings.
- [ ] **Step 5: Feature test component** — flow add cart + simpan.
- [ ] **Step 6: Run** `php artisan test --filter=BorrowRuleTest` + `--filter=PeminjamanServiceTest` → PASS
- [ ] **Step 7: Commit**

## Task 12: Pengembalian

**Files:**
- Create: `app/Livewire/ReturnIndex.php` + view `return-index.blade.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/Livewire/ReturnTest.php`

**Interfaces:**
- Uses: `Peminjaman::hitungKeterlambatan()`.

- [ ] **Step 1: Component** — cari transaksi (`no_transaksi` / anggota), tampil header (anggota, petugas, tanggal, jatuh tempo, status) + daftar detail (judul, tanggal_kembali, status badge Tepat Waktu/Terlambat N Hari). Tombol "Kembalikan" per item belum kembali → set `tanggal_kembali=today()`, `keterlambatan_hari = peminjaman->hitungKeterlambatan()`. Bila semua item kembali → header status `selesai`.
- [ ] **Step 2: View**
- [ ] **Step 3: Test**
```php
public function test_kembalikan_tepat_waktu()   // stok naik, keterlambatan_hari null
public function test_kembalikan_terlambat()     // keterlambatan_hari = diff
public function test_header_selesai_semua_kembali()
```
- [ ] **Step 4: Run** → PASS
- [ ] **Step 5: Commit**

## Task 13: Dashboard

**Files:**
- Create: `app/Livewire/Dashboard.php` + view `dashboard.blade.php` (ganti Breeze default)
- Modify: `routes/web.php`
- Test: `tests/Feature/Livewire/DashboardTest.php`

- [ ] **Step 1: Component** — widget: jumlah judul (count bukus), jumlah eksemplar (sum jumlah_eksemplar), jumlah anggota, sedang dipinjam (detail aktif count), terlambat (detail aktif jatuh tempo < today), kembali hari ini (detail tanggal_kembali = today). Grafik 7 hari: `Peminjaman::where('tanggal','>=',today()->subDays(6))` group by tanggal count. Shortcut link.
- [ ] **Step 2: View** — grid widget + chart (bar sederhana via div/width, tanpa lib JS tambahan).
- [ ] **Step 3: Test** — seed data lalu assert nilai widget.
- [ ] **Step 4: Run** → PASS
- [ ] **Step 5: Commit**

## Task 14: Laporan + export

**Files:**
- Create: `app/Http/Controllers/ReportController.php`
- Create: `app/Exports/BukuExport.php`, `app/Exports/AnggotaExport.php`, `app/Exports/TransaksiExport.php`
- Create: `app/Exports/` view PDF: `resources/views/reports/pdf/` (buku, anggota, transaksi)
- Modify: `routes/web.php`
- Test: `tests/Feature/ReportTest.php`

**Interfaces:**
- Export classes `FromCollection`/`FromQuery` + `WithHeadings`; menerima filter argumen (kategori_id/rak_id/status).
- Route: `GET /laporan` (view), `GET /laporan/{tipe}/export/{format}` (`pdf|excel`).

- [ ] **Step 1: Export classes** — BukuExport (opsional kategori/rak filter, heading: Kode, ISBN, Judul, Kategori, Pengarang, Penerbit, Tahun, Rak, Stok, Status), AnggotaExport (opsional status filter), TransaksiExport (opsional tipe: peminjaman/pengembalian/terlambat).
- [ ] **Step 2: ReportController** — halaman laporan dengan link export; metode export PDF (dompdf render view dengan dataset sama) dan Excel (`Maatwebsite\Excel\Facades\Excel::download`).
- [ ] **Step 3: Views PDF** — tabel sederhana.
- [ ] **Step 4: Test**
```php
public function test_export_pdf_buku()   // response content-type application/pdf, ok
public function test_export_excel_anggota() // content-type spreadsheet, ok
public function test_laporan_transaksi_terlambat()
```
- [ ] **Step 5: Run** → PASS
- [ ] **Step 6: Commit**

## Task 15: Settings page

**Files:**
- Create: `app/Livewire/SettingsPage.php` + view `settings-page.blade.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/Livewire/SettingsPageTest.php`

- [ ] **Step 1: Component** — form nama_perpus, logo upload, lama_pinjam, maksimal_buku; simpan via `Setting::set`. Re-fetch data saat mount dari `Setting::get`.
- [ ] **Step 2: View**
- [ ] **Step 3: Test**
```php
public function test_simpan_pengaturan() // nilai tersimpan + cache diperbarui
```
- [ ] **Step 4: Run** → PASS
- [ ] **Step 5: Commit**

## Task 16: Full seeder + verify

**Files:**
- Modify: `database/seeders/DatabaseSeeder.php`
- Create: `KategoriSeeder`, `RakSeeder`, `UserSeeder`, `BookSeeder`, `AnggotaSeeder`, `TransaksiSeeder`

- [ ] **Step 1: Seeders**
  - Kategori: Pelajaran, Novel, Agama, Ensiklopedia, Komik Edukasi, Majalah
  - Rak: A-01..A-05, B-01..B-05 (nama "Rak A-01" dst, keterangan)
  - User: 1 admin (`admin/admin123`), 2 petugas (`petugas1`, `petugas2` / `password`)
  - Buku: 40 buku (faker), 1-3 eksemplar, kategori/rak acak, sebagian status `tidak`
  - Anggota: 200 anggota faker, kelas acak 7-9, sebagian status non-aktif/lulus/pindah
  - Transaksi: ±30 header, mix: aktif (baru), terlambat (jatuh tempo lalu), selesai (semua kembali, sebagian terlambat). Detail 1-3 buku.
- [ ] **Step 2: Run**
```bash
php artisan migrate:fresh --seed
php artisan test
vendor/bin/pint
npm run build
```
Expected: migrate fresh clean, seluruh test PASS, pint clean, build sukses.
- [ ] **Step 3: Smoke manual** — login admin → buat buku → import anggota (jika ada file) → pinjam → kembali → export laporan → ganti password.
- [ ] **Step 4: Commit** `build: complete MVP seeder + verify`
