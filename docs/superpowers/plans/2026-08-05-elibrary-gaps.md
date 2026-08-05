# e-Library — Implementasi Gap PRD

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Menuntaskan modul MVP yang belum ada — Dashboard, Peminjaman, Pengembalian, Laporan, Setting, filter buku tambahan, dan seeder lengkap.

**Architecture:** Livewire single-file components (anonymous class) di `resources/views/components/{domain}/{name}.php` + `.blade.php` — komponen diregistrasi otomatis dengan nama `{domain}` (mis. `peminjaman`, `dashboard`). Logika bisnis Peminjaman di `app/Services/PeminjamanService.php` + exception per rule. Stok selalu computed dari pinjaman aktif — tidak ada counter tersimpan. Laporan: Livewire page untuk preview + `ReportController` untuk export PDF (dompdf) / Excel (maatwebsite).

**Tech Stack:** Laravel 13, Livewire 4.3, Breeze (Blade/Tailwind/Alpine), SQLite, PHPUnit, `barryvdh/laravel-dompdf`, `maatwebsite/excel`.

## Global Constraints
- SFC pattern — jangan buat `app/Livewire/`. Komponen = anonymous class `new class extends Component`, `render()` return `view('components.{domain}.{name}')`. Page view di `resources/views/{domain}/index.blade.php` hanya render `<livewire:{domain} />`.
- Stok tidak pernah disimpan sebagai counter — `Buku::stokTersedia()` = `jumlah_eksemplar − detailsAktif()->count()`.
- Tanpa denda — hanya tampil "Terlambat N Hari".
- Default settings: `lama_pinjam=7`, `maksimal_buku=3` via `Setting::get(key, default)`.
- `composer test` saat ini RUSAK (script `@no_additional_args`) — selama Task 1 belum selesai, jalankan `php artisan test` langsung.
- Livewire 4.3.5 + Symfony 7.4 bug: `->set('file', UploadedFile)` di test crash (`Undefined property: ...::$name`). Upload test pakai subclass `NamedUploadedFile` (contoh `tests/Feature/Livewire/MemberImportTest.php:116`).
- Livewire test mount: `Livewire::test('{name}')`; komponen nested pakai titik (`'anggota.member-import'`).
- UI bahasa Indonesia.

---

## Task 1: Perbaiki script `composer test`

**Files:**
- Modify: `composer.json` (script `test`)

**Interfaces:**
- Produces: `composer test` berjalan (config:clear + `php artisan test`).

- [ ] **Step 1: Edit script test**

Ubah:
```json
"test": [
    "@php artisan config:clear --ansi @no_additional_args",
    "@php artisan test"
],
```
menjadi:
```json
"test": [
    "@php artisan config:clear --ansi",
    "@php artisan test"
],
```

- [ ] **Step 2: Verifikasi**

Run: `composer test`
Expected: test suite jalan (akan ada 1 fail flaky `BookTest::test_isbn_duplikat_ditolak` — itu memang sudah rusak, difix di Task 8).

- [ ] **Step 3: Commit**

```bash
git add composer.json && git commit -m "fix: remove broken @no_additional_args from test script"
```

---

## Task 2: `PeminjamanService` + exception

**Files:**
- Create: `app/Services/PeminjamanService.php`
- Create: `app/Exceptions/AnggotaNonaktifException.php`, `AnggotaTerlambatException.php`, `MelebihiLimitException.php`, `StokHabisException.php`
- Test: `tests/Unit/PeminjamanServiceTest.php`

**Interfaces:**
- Produces: `App\Services\PeminjamanService::buatPeminjaman(int $anggotaId, array $bukuIds, int $petugasId): Peminjaman` — throw exception di atas; sukses = header `Peminjaman` (`status=dipinjam`) + 1 `PeminjamanDetail` per buku, dalam satu transaksi DB.
- `AnggotaNonaktifException`, `AnggotaTerlambatException`, `MelebihiLimitException` extends `RuntimeException`, constructor tanpa arg, pesan default.
- `StokHabisException extends RuntimeException` — constructor `(Buku $buku)`, expose `public readonly Buku $buku`.

- [ ] **Step 1: Tulis test gagal**

`tests/Unit/PeminjamanServiceTest.php`:
```php
<?php

namespace Tests\Unit;

use App\Exceptions\AnggotaNonaktifException;
use App\Exceptions\AnggotaTerlambatException;
use App\Exceptions\MelebihiLimitException;
use App\Exceptions\StokHabisException;
use App\Models\Anggota;
use App\Models\Buku;
use App\Models\Peminjaman;
use App\Models\PeminjamanDetail;
use App\Models\Setting;
use App\Models\User;
use App\Services\PeminjamanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PeminjamanServiceTest extends TestCase
{
    use RefreshDatabase;

    private PeminjamanService $service;
    private User $petugas;
    private Anggota $anggota;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PeminjamanService();
        $this->petugas = User::factory()->petugas()->create();
        $this->anggota = Anggota::factory()->create();
    }

    public function test_sukses_membuat_peminjaman(): void
    {
        $buku = Buku::factory()->create(['jumlah_eksemplar' => 3]);
        Setting::set('lama_pinjam', 7);
        Setting::set('maksimal_buku', 3);

        $peminjaman = $this->service->buatPeminjaman($this->anggota->id, [$buku->id], $this->petugas->id);

        $this->assertInstanceOf(Peminjaman::class, $peminjaman);
        $this->assertMatchesRegularExpression('/^PJM-\d{8}-\d+$/', $peminjaman->no_transaksi);
        $this->assertEquals('dipinjam', $peminjaman->status);
        $this->assertEquals(today(), $peminjaman->tanggal);
        $this->assertEquals(today()->addDays(7), $peminjaman->tanggal_jatuh_tempo);
        $this->assertEquals($this->petugas->id, $peminjaman->petugas_id);
        $this->assertEquals(1, $peminjaman->details()->count());
        $this->assertEquals(2, $buku->fresh()->stokTersedia());
    }

    public function test_anggota_nonaktif_ditolak(): void
    {
        $buku = Buku::factory()->create(['jumlah_eksemplar' => 3]);
        $this->anggota->update(['status' => 'nonaktif']);

        $this->expectException(AnggotaNonaktifException::class);
        $this->service->buatPeminjaman($this->anggota->id, [$buku->id], $this->petugas->id);
    }

    public function test_anggota_terlambat_ditolak(): void
    {
        $buku = Buku::factory()->create(['jumlah_eksemplar' => 3]);
        $peminjaman = Peminjaman::factory()->create([
            'anggota_id' => $this->anggota->id,
            'tanggal_jatuh_tempo' => today()->subDays(2),
        ]);
        PeminjamanDetail::create(['peminjaman_id' => $peminjaman->id, 'buku_id' => $buku->id]);

        $this->expectException(AnggotaTerlambatException::class);
        $this->service->buatPeminjaman($this->anggota->id, [$buku->id], $this->petugas->id);
    }

    public function test_melebihi_limit_ditolak(): void
    {
        Setting::set('maksimal_buku', 2);
        $buku = Buku::factory()->create(['jumlah_eksemplar' => 5]);
        $buku2 = Buku::factory()->create(['jumlah_eksemplar' => 5]);
        $buku3 = Buku::factory()->create(['jumlah_eksemplar' => 5]);

        $this->expectException(MelebihiLimitException::class);
        $this->service->buatPeminjaman($this->anggota->id, [$buku->id, $buku2->id, $buku3->id], $this->petugas->id);
    }

    public function test_cart_kosong_ditolak(): void
    {
        $this->expectException(MelebihiLimitException::class);
        $this->service->buatPeminjaman($this->anggota->id, [], $this->petugas->id);
    }

    public function test_stok_habis_ditolak(): void
    {
        $buku = Buku::factory()->create(['jumlah_eksemplar' => 1]);
        $p = Peminjaman::factory()->create(['anggota_id' => $this->anggota->id]);
        PeminjamanDetail::create(['peminjaman_id' => $p->id, 'buku_id' => $buku->id]);

        try {
            $this->service->buatPeminjaman($this->anggota->id, [$buku->id], $this->petugas->id);
            $this->fail('Seharusnya throw StokHabisException');
        } catch (StokHabisException $e) {
            $this->assertEquals($buku->id, $e->buku->id);
        }
    }
}
```

- [ ] **Step 2: Run, pastikan gagal**

Run: `php artisan test --filter=PeminjamanServiceTest`
Expected: FAIL — class `App\Services\PeminjamanService` tidak ditemukan.

- [ ] **Step 3: Buat 4 exception**

`app/Exceptions/AnggotaNonaktifException.php`:
```php
<?php

namespace App\Exceptions;

use RuntimeException;

class AnggotaNonaktifException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Anggota tidak aktif, tidak bisa meminjam buku.');
    }
}
```
`AnggotaTerlambatException.php` — sama, pesan: `'Anggota masih memiliki buku terlambat yang belum dikembalikan.'`
`MelebihiLimitException.php` — pesan: `'Jumlah buku melebihi batas maksimal peminjaman.'`
`StokHabisException.php`:
```php
<?php

namespace App\Exceptions;

use App\Models\Buku;
use RuntimeException;

class StokHabisException extends RuntimeException
{
    public function __construct(public readonly Buku $buku)
    {
        parent::__construct("Stok buku \"{$buku->judul}\" habis.");
    }
}
```

- [ ] **Step 4: Buat service**

`app/Services/PeminjamanService.php`:
```php
<?php

namespace App\Services;

use App\Exceptions\AnggotaNonaktifException;
use App\Exceptions\AnggotaTerlambatException;
use App\Exceptions\MelebihiLimitException;
use App\Exceptions\StokHabisException;
use App\Models\Anggota;
use App\Models\Buku;
use App\Models\Peminjaman;
use App\Models\PeminjamanDetail;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;

class PeminjamanService
{
    public function buatPeminjaman(int $anggotaId, array $bukuIds, int $petugasId): Peminjaman
    {
        $anggota = Anggota::findOrFail($anggotaId);

        if ($anggota->status !== 'aktif') {
            throw new AnggotaNonaktifException;
        }

        if ($this->punyaBukuTerlambat($anggotaId)) {
            throw new AnggotaTerlambatException;
        }

        $maksimal = (int) Setting::get('maksimal_buku', 3);
        if (count($bukuIds) < 1 || count($bukuIds) > $maksimal) {
            throw new MelebihiLimitException;
        }

        foreach (Buku::whereIn('id', $bukuIds)->get() as $buku) {
            if ($buku->stokTersedia() < 1) {
                throw new StokHabisException($buku);
            }
        }

        $lama = (int) Setting::get('lama_pinjam', 7);

        return DB::transaction(function () use ($anggotaId, $bukuIds, $petugasId, $lama) {
            $peminjaman = Peminjaman::create([
                'no_transaksi' => Peminjaman::generateNoTransaksi(),
                'tanggal' => today(),
                'tanggal_jatuh_tempo' => today()->addDays($lama),
                'petugas_id' => $petugasId,
                'anggota_id' => $anggotaId,
                'status' => 'dipinjam',
            ]);

            foreach ($bukuIds as $bukuId) {
                PeminjamanDetail::create([
                    'peminjaman_id' => $peminjaman->id,
                    'buku_id' => $bukuId,
                ]);
            }

            return $peminjaman;
        });
    }

    private function punyaBukuTerlambat(int $anggotaId): bool
    {
        return PeminjamanDetail::whereNull('tanggal_kembali')
            ->whereHas('peminjaman', function ($q) use ($anggotaId) {
                $q->where('status', 'dipinjam')
                    ->where('anggota_id', $anggotaId)
                    ->whereDate('tanggal_jatuh_tempo', '<', today());
            })
            ->exists();
    }
}
```

- [ ] **Step 5: Run, pastikan PASS**

Run: `php artisan test --filter=PeminjamanServiceTest`
Expected: 6 tests PASS.

- [ ] **Step 6: Commit**

```bash
git add app/Services app/Exceptions tests/Unit/PeminjamanServiceTest.php
git commit -m "feat: peminjaman service with borrow rules and exceptions"
```

---

## Task 3: SFC Peminjaman + route

**Files:**
- Create: `resources/views/components/peminjaman/peminjaman.php`, `resources/views/components/peminjaman/peminjaman.blade.php`, `resources/views/peminjaman/index.blade.php`
- Modify: `routes/web.php:28`
- Test: `tests/Feature/Livewire/PeminjamanTest.php`

**Interfaces:**
- Consumes: `PeminjamanService::buatPeminjaman(int, array, int): Peminjaman`, `Buku::stokTersedia(): int`, `Setting::get('maksimal_buku', 3)`.
- Produces: komponen `peminjaman`; route `peminjaman.index`.

- [ ] **Step 1: Tulis test gagal**

`tests/Feature/Livewire/PeminjamanTest.php`:
```php
<?php

namespace Tests\Feature\Livewire;

use App\Models\Anggota;
use App\Models\Buku;
use App\Models\Kategori;
use App\Models\Rak;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PeminjamanTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Anggota $anggota;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->petugas()->create();
        $this->anggota = Anggota::factory()->create();
    }

    public function test_alur_pilih_anggota_tambah_buku_simpan(): void
    {
        $buku = Buku::factory()->create(['jumlah_eksemplar' => 2]);

        Livewire::actingAs($this->user)
            ->test('peminjaman')
            ->set('searchAnggota', $this->anggota->nama)
            ->call('pilihAnggota', $this->anggota->id)
            ->assertSet('anggotaDipilih.id', $this->anggota->id)
            ->set('searchBuku', $buku->judul)
            ->call('tambahBuku', $buku->id)
            ->assertSet('cart.0.id', $buku->id)
            ->call('simpan')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('peminjamen', ['anggota_id' => $this->anggota->id, 'status' => 'dipinjam']);
        $this->assertEquals(1, $buku->fresh()->stokTersedia());
    }

    public function test_cart_cegah_duplikat_buku(): void
    {
        $buku = Buku::factory()->create(['jumlah_eksemplar' => 5]);

        $component = Livewire::actingAs($this->user)->test('peminjaman');
        $component->call('pilihAnggota', $this->anggota->id);
        $component->call('tambahBuku', $buku->id);
        $component->call('tambahBuku', $buku->id);

        $this->assertCount(1, $component->get('cart'));
    }

    public function test_anggota_nonaktif_ditolak(): void
    {
        $this->anggota->update(['status' => 'nonaktif']);
        $buku = Buku::factory()->create(['jumlah_eksemplar' => 2]);

        Livewire::actingAs($this->user)
            ->test('peminjaman')
            ->call('pilihAnggota', $this->anggota->id)
            ->call('tambahBuku', $buku->id)
            ->call('simpan');

        $this->assertDatabaseMissing('peminjamen', ['anggota_id' => $this->anggota->id]);
        $this->assertEquals(2, $buku->fresh()->stokTersedia());
    }

    public function test_cart_limit_ditolak(): void
    {
        $buku1 = Buku::factory()->create(['jumlah_eksemplar' => 5]);
        $buku2 = Buku::factory()->create(['jumlah_eksemplar' => 5]);
        $buku3 = Buku::factory()->create(['jumlah_eksemplar' => 5]);
        $buku4 = Buku::factory()->create(['jumlah_eksemplar' => 5]);

        $component = Livewire::actingAs($this->user)->test('peminjaman');
        $component->call('pilihAnggota', $this->anggota->id);
        foreach ([$buku1, $buku2, $buku3, $buku4] as $b) {
            $component->call('tambahBuku', $b->id);
        }

        $this->assertCount(3, $component->get('cart'));
    }
}
```

- [ ] **Step 2: Run, pastikan gagal**

Run: `php artisan test --filter=PeminjamanTest`
Expected: FAIL — komponen `peminjaman` tidak ditemukan.

- [ ] **Step 3: Buat komponen PHP**

`resources/views/components/peminjaman/peminjaman.php`:
```php
<?php

use App\Exceptions\AnggotaNonaktifException;
use App\Exceptions\AnggotaTerlambatException;
use App\Exceptions\MelebihiLimitException;
use App\Exceptions\StokHabisException;
use App\Models\Anggota;
use App\Models\Buku;
use App\Models\Setting;
use App\Services\PeminjamanService;
use Livewire\Component;

new class extends Component
{
    public $searchAnggota = '';
    public $hasilAnggota = [];
    public $anggotaDipilih = null;
    public $searchBuku = '';
    public $hasilBuku = [];
    public $cart = [];
    public $maksimalBuku = 3;

    public function mount(): void
    {
        $this->maksimalBuku = (int) Setting::get('maksimal_buku', 3);
    }

    public function pilihAnggota($id): void
    {
        $this->anggotaDipilih = Anggota::findOrFail($id);
        $this->searchAnggota = '';
    }

    public function gantiAnggota(): void
    {
        $this->anggotaDipilih = null;
        $this->searchAnggota = '';
    }

    public function tambahBuku($id): void
    {
        $buku = Buku::findOrFail($id);

        if (collect($this->cart)->contains('id', $id)) {
            session()->flash('error', 'Buku sudah ada di daftar.');
            return;
        }

        if (count($this->cart) >= $this->maksimalBuku) {
            session()->flash('error', "Maksimal {$this->maksimalBuku} buku per peminjaman.");
            return;
        }

        if ($buku->stokTersedia() < 1) {
            session()->flash('error', "Stok buku \"{$buku->judul}\" habis.");
            return;
        }

        $this->cart[] = ['id' => $buku->id, 'kode' => $buku->kode, 'judul' => $buku->judul];
    }

    public function hapusDariCart($index): void
    {
        unset($this->cart[$index]);
        $this->cart = array_values($this->cart);
    }

    public function simpan(): void
    {
        if (! $this->anggotaDipilih) {
            session()->flash('error', 'Pilih anggota dulu.');
            return;
        }

        if (count($this->cart) === 0) {
            session()->flash('error', 'Pilih minimal satu buku.');
            return;
        }

        $bukuIds = array_column($this->cart, 'id');

        try {
            $peminjaman = app(PeminjamanService::class)
                ->buatPeminjaman($this->anggotaDipilih->id, $bukuIds, auth()->id());
        } catch (AnggotaNonaktifException|AnggotaTerlambatException|MelebihiLimitException|StokHabisException $e) {
            session()->flash('error', $e->getMessage());

            return;
        }

        session()->flash('success', "Peminjaman {$peminjaman->no_transaksi} berhasil disimpan.");
        $this->anggotaDipilih = null;
        $this->cart = [];
        $this->searchBuku = '';
    }

    public function render()
    {
        $this->hasilAnggota = strlen($this->searchAnggota) >= 1
            ? Anggota::where(function ($q) {
                $q->where('nama', 'like', "%{$this->searchAnggota}%")
                    ->orWhere('nis', 'like', "%{$this->searchAnggota}%");
            })->limit(5)->get()
            : collect();

        $this->hasilBuku = strlen($this->searchBuku) >= 1
            ? Buku::with(['kategori', 'rak'])
                ->where('status', 'aktif')
                ->where(function ($q) {
                    $q->where('kode', 'like', "%{$this->searchBuku}%")
                        ->orWhere('isbn', 'like', "%{$this->searchBuku}%")
                        ->orWhere('judul', 'like', "%{$this->searchBuku}%");
                })->limit(5)->get()
            : collect();

        return view('components.peminjaman.peminjaman');
    }
};
```

- [ ] **Step 4: Buat blade**

`resources/views/components/peminjaman/peminjaman.blade.php`:
```blade
<div>
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Peminjaman Buku</h1>
    </div>

    @if (session()->has('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">{{ session('success') }}</div>
    @endif
    @if (session()->has('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">{{ session('error') }}</div>
    @endif

    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <h2 class="font-semibold text-gray-800 mb-2">1. Cari Anggota</h2>
        @if ($anggotaDipilih)
            <div class="flex items-center justify-between bg-green-50 border border-green-300 rounded p-3">
                <div>
                    <div class="font-medium">{{ $anggotaDipilih->nama }}</div>
                    <div class="text-sm text-gray-500">NIS: {{ $anggotaDipilih->nis }} &middot; Kelas: {{ $anggotaDipilih->kelas }} &middot; {{ $anggotaDipilih->status }}</div>
                </div>
                <button wire:click="gantiAnggota" class="text-sm text-red-600 hover:text-red-900">Ganti</button>
            </div>
        @else
            <input type="text" wire:model.live="searchAnggota" placeholder="Cari nama / NIS anggota..." class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
            @if ($searchAnggota)
                <ul class="mt-2 divide-y divide-gray-100">
                    @forelse ($hasilAnggota as $a)
                        <li class="flex items-center justify-between py-2">
                            <span>{{ $a->nama }} <span class="text-sm text-gray-500">({{ $a->nis }} &mdash; {{ $a->kelas }})</span></span>
                            <button wire:click="pilihAnggota({{ $a->id }})" class="text-blue-600 hover:text-blue-900 text-sm">Pilih</button>
                        </li>
                    @empty
                        <li class="py-2 text-sm text-gray-500">Tidak ditemukan.</li>
                    @endforelse
                </ul>
            @endif
        @endif
    </div>

    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <h2 class="font-semibold text-gray-800 mb-2">2. Pilih Buku (maksimal {{ $maksimalBuku }})</h2>
        <input type="text" wire:model.live="searchBuku" placeholder="Cari kode / ISBN / judul..." class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
        @if ($searchBuku)
            <ul class="mt-2 divide-y divide-gray-100">
                @forelse ($hasilBuku as $b)
                    <li class="flex items-center justify-between py-2">
                        <div>
                            <div class="text-sm font-medium">{{ $b->judul }}</div>
                            <div class="text-sm text-gray-500">{{ $b->kode }} &mdash; stok {{ $b->stokTersedia() }}/{{ $b->jumlah_eksemplar }}</div>
                        </div>
                        <button wire:click="tambahBuku({{ $b->id }})" class="text-blue-600 hover:text-blue-900 text-sm">Tambah</button>
                    </li>
                @empty
                    <li class="py-2 text-sm text-gray-500">Tidak ditemukan.</li>
                @endforelse
            </ul>
        @endif
    </div>

    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <h2 class="font-semibold text-gray-800 mb-2">3. Daftar Buku</h2>
        @if (count($cart) === 0)
            <p class="text-sm text-gray-500">Belum ada buku dipilih.</p>
        @else
            <table class="min-w-full divide-y divide-gray-200 mb-4">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Kode</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Judul</th>
                        <th class="px-4 py-2"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach ($cart as $index => $item)
                        <tr>
                            <td class="px-4 py-2 text-sm font-mono">{{ $item['kode'] }}</td>
                            <td class="px-4 py-2 text-sm">{{ $item['judul'] }}</td>
                            <td class="px-4 py-2 text-right">
                                <button wire:click="hapusDariCart({{ $index }})" class="text-red-600 hover:text-red-900 text-sm">Hapus</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <button wire:click="simpan" @if (! $anggotaDipilih) disabled @endif class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">Simpan Peminjaman</button>
        @endif
    </div>
</div>
```

- [ ] **Step 5: Buat page view**

`resources/views/peminjaman/index.blade.php`:
```blade
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Peminjaman</h2>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <livewire:peminjaman />
        </div>
    </div>
</x-app-layout>
```

- [ ] **Step 6: Update route**

`routes/web.php:28`, ganti:
```php
Route::get('/peminjaman', fn () => view('dashboard'))->name('peminjaman.index');
```
menjadi:
```php
Route::get('/peminjaman', fn () => view('peminjaman.index'))->name('peminjaman.index');
```

- [ ] **Step 7: Run, pastikan PASS**

Run: `php artisan test --filter=PeminjamanTest`
Expected: 4 tests PASS.

- [ ] **Step 8: Commit**

```bash
git add resources/views/components/peminjaman resources/views/peminjaman routes/web.php tests/Feature/Livewire/PeminjamanTest.php
git commit -m "feat: peminjaman create flow (search member, pick book, save)"
```

---

## Task 4: SFC Pengembalian + route

**Files:**
- Create: `resources/views/components/pengembalian/pengembalian.php`, `resources/views/components/pengembalian/pengembalian.blade.php`, `resources/views/pengembalian/index.blade.php`
- Modify: `routes/web.php:29`
- Test: `tests/Feature/Livewire/PengembalianTest.php`

**Interfaces:**
- Consumes: `Peminjaman::hitungKeterlambatan(): int`, `Peminjaman::sudahSelesai(): bool`.
- Produces: komponen `pengembalian`; route `pengembalian.index`.

- [ ] **Step 1: Tulis test gagal**

`tests/Feature/Livewire/PengembalianTest.php`:
```php
<?php

namespace Tests\Feature\Livewire;

use App\Models\Anggota;
use App\Models\Buku;
use App\Models\Peminjaman;
use App\Models\PeminjamanDetail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PengembalianTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->petugas()->create();
    }

    public function test_kembalikan_tepat_waktu(): void
    {
        $buku = Buku::factory()->create(['jumlah_eksemplar' => 2]);
        $peminjaman = Peminjaman::factory()->create(['tanggal_jatuh_tempo' => today()->addDays(3)]);
        $detail = PeminjamanDetail::create(['peminjaman_id' => $peminjaman->id, 'buku_id' => $buku->id]);

        Livewire::actingAs($this->user)
            ->test('pengembalian')
            ->call('pilih', $peminjaman->id)
            ->assertSet('peminjaman.id', $peminjaman->id)
            ->call('kembalikan', $detail->id);

        $detail->refresh();
        $this->assertNotNull($detail->tanggal_kembali);
        $this->assertNull($detail->keterlambatan_hari);
        $this->assertEquals(2, $buku->fresh()->stokTersedia());
    }

    public function test_kembalikan_terlambat(): void
    {
        $buku = Buku::factory()->create(['jumlah_eksemplar' => 2]);
        $peminjaman = Peminjaman::factory()->create(['tanggal_jatuh_tempo' => today()->subDays(5)]);
        $detail = PeminjamanDetail::create(['peminjaman_id' => $peminjaman->id, 'buku_id' => $buku->id]);

        Livewire::actingAs($this->user)
            ->test('pengembalian')
            ->call('pilih', $peminjaman->id)
            ->call('kembalikan', $detail->id);

        $detail->refresh();
        $this->assertEquals(5, $detail->keterlambatan_hari);
    }

    public function test_header_selesai_saat_semua_kembali(): void
    {
        $buku1 = Buku::factory()->create(['jumlah_eksemplar' => 2]);
        $buku2 = Buku::factory()->create(['jumlah_eksemplar' => 2]);
        $peminjaman = Peminjaman::factory()->create();
        $d1 = PeminjamanDetail::create(['peminjaman_id' => $peminjaman->id, 'buku_id' => $buku1->id]);
        $d2 = PeminjamanDetail::create(['peminjaman_id' => $peminjaman->id, 'buku_id' => $buku2->id]);

        Livewire::actingAs($this->user)
            ->test('pengembalian')
            ->call('pilih', $peminjaman->id)
            ->call('kembalikan', $d1->id)
            ->call('kembalikan', $d2->id);

        $this->assertEquals('selesai', $peminjaman->fresh()->status);
    }

    public function test_cari_transaksi_via_no_transaksi(): void
    {
        $peminjaman = Peminjaman::factory()->create(['no_transaksi' => 'PJM-20260805-1']);

        Livewire::actingAs($this->user)
            ->test('pengembalian')
            ->set('search', '20260805')
            ->assertSet('hasilTransaksi.0.id', $peminjaman->id);
    }
}
```

- [ ] **Step 2: Run, pastikan gagal**

Run: `php artisan test --filter=PengembalianTest`
Expected: FAIL — komponen `pengembalian` tidak ditemukan.

- [ ] **Step 3: Buat komponen PHP**

`resources/views/components/pengembalian/pengembalian.php`:
```php
<?php

use App\Models\Peminjaman;
use App\Models\PeminjamanDetail;
use Livewire\Component;

new class extends Component
{
    public $search = '';
    public $hasilTransaksi = [];
    public $peminjaman = null;
    public $details = [];

    public function pilih($id): void
    {
        $this->peminjaman = Peminjaman::with(['anggota', 'petugas'])->findOrFail($id);
        $this->details = $this->peminjaman->details()->with('buku')->get();
    }

    public function kembalikan($detailId): void
    {
        $detail = PeminjamanDetail::findOrFail($detailId);
        $peminjaman = $detail->peminjaman;

        $detail->update([
            'tanggal_kembali' => today(),
            'keterlambatan_hari' => $peminjaman->hitungKeterlambatan(),
        ]);

        if ($peminjaman->sudahSelesai()) {
            $peminjaman->update(['status' => 'selesai']);
        }

        $this->details = $peminjaman->details()->with('buku')->get();
    }

    public function cariLagi(): void
    {
        $this->peminjaman = null;
        $this->details = [];
        $this->search = '';
    }

    public function render()
    {
        $this->hasilTransaksi = strlen($this->search) >= 1
            ? Peminjaman::with('anggota')
                ->where('status', 'dipinjam')
                ->where(function ($q) {
                    $q->where('no_transaksi', 'like', "%{$this->search}%")
                        ->orWhereHas('anggota', function ($q2) {
                            $q2->where('nama', 'like', "%{$this->search}%")
                                ->orWhere('nis', 'like', "%{$this->search}%");
                        });
                })->limit(5)->get()
            : collect();

        return view('components.pengembalian.pengembalian');
    }
};
```

- [ ] **Step 4: Buat blade**

`resources/views/components/pengembalian/pengembalian.blade.php`:
```blade
<div>
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Pengembalian Buku</h1>
    </div>

    @if (session()->has('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">{{ session('success') }}</div>
    @endif

    @if ($peminjaman)
        <div class="bg-white rounded-lg shadow p-4 mb-6">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <div class="text-lg font-semibold">{{ $peminjaman->no_transaksi }}</div>
                    <div class="text-sm text-gray-500">
                        {{ $peminjaman->anggota->nama }} ({{ $peminjaman->anggota->nis }}) &middot;
                        Petugas: {{ $peminjaman->petugas->name }} &middot;
                        Pinjam: {{ $peminjaman->tanggal->format('d/m/Y') }} &middot;
                        Jatuh Tempo: {{ $peminjaman->tanggal_jatuh_tempo->format('d/m/Y') }}
                    </div>
                </div>
                <button wire:click="cariLagi" class="text-sm text-red-600 hover:text-red-900">Cari Lain</button>
            </div>

            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Buku</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-2"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach ($details as $detail)
                        <tr>
                            <td class="px-4 py-2 text-sm">{{ $detail->buku->judul }}</td>
                            <td class="px-4 py-2 text-sm">
                                @if ($detail->tanggal_kembali)
                                    @if ($detail->keterlambatan_hari > 0)
                                        <span class="px-2 py-1 rounded-full text-xs bg-red-100 text-red-800">Terlambat {{ $detail->keterlambatan_hari }} Hari</span>
                                    @else
                                        <span class="px-2 py-1 rounded-full text-xs bg-green-100 text-green-800">Tepat Waktu</span>
                                    @endif
                                @else
                                    <span class="px-2 py-1 rounded-full text-xs bg-gray-100 text-gray-600">Belum Kembali</span>
                                @endif
                            </td>
                            <td class="px-4 py-2 text-right">
                                @if (! $detail->tanggal_kembali)
                                    <button wire:click="kembalikan({{ $detail->id }})" class="text-blue-600 hover:text-blue-900 text-sm">Kembalikan</button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="bg-white rounded-lg shadow p-4 mb-6">
            <input type="text" wire:model.live="search" placeholder="Cari no transaksi / nama / NIS anggota..." class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
            @if ($search)
                <ul class="mt-2 divide-y divide-gray-100">
                    @forelse ($hasilTransaksi as $t)
                        <li class="flex items-center justify-between py-2">
                            <span>{{ $t->no_transaksi }} <span class="text-sm text-gray-500">&mdash; {{ $t->anggota->nama }}</span></span>
                            <button wire:click="pilih({{ $t->id }})" class="text-blue-600 hover:text-blue-900 text-sm">Pilih</button>
                        </li>
                    @empty
                        <li class="py-2 text-sm text-gray-500">Tidak ditemukan transaksi aktif.</li>
                    @endforelse
                </ul>
            @endif
        </div>
    @endif
</div>
```

- [ ] **Step 5: Buat page view**

`resources/views/pengembalian/index.blade.php`:
```blade
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Pengembalian</h2>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <livewire:pengembalian />
        </div>
    </div>
</x-app-layout>
```

- [ ] **Step 6: Update route**

`routes/web.php:29`, ganti:
```php
Route::get('/pengembalian', fn () => view('dashboard'))->name('pengembalian.index');
```
menjadi:
```php
Route::get('/pengembalian', fn () => view('pengembalian.index'))->name('pengembalian.index');
```

- [ ] **Step 7: Run, pastikan PASS**

Run: `php artisan test --filter=PengembalianTest`
Expected: 4 tests PASS.

- [ ] **Step 8: Commit**

```bash
git add resources/views/components/pengembalian resources/views/pengembalian routes/web.php tests/Feature/Livewire/PengembalianTest.php
git commit -m "feat: return flow (search transaction, return per item)"
```

---

## Task 5: Dashboard

**Files:**
- Create: `resources/views/components/dashboard/dashboard.php`, `resources/views/components/dashboard/dashboard.blade.php`
- Modify: `resources/views/dashboard.blade.php`
- Test: `tests/Feature/Livewire/DashboardTest.php`

**Interfaces:**
- Produces: komponen `dashboard`.

- [ ] **Step 1: Tulis test gagal**

`tests/Feature/Livewire/DashboardTest.php`:
```php
<?php

namespace Tests\Feature\Livewire;

use App\Models\Anggota;
use App\Models\Buku;
use App\Models\Peminjaman;
use App\Models\PeminjamanDetail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_widget_dan_grafik(): void
    {
        Buku::factory()->count(3)->create(['jumlah_eksemplar' => 2]);
        Anggota::factory()->count(5)->create();

        // aktif (sedang dipinjam)
        $aktif = Peminjaman::factory()->create(['tanggal' => today()]);
        $bukuA = Buku::factory()->create(['jumlah_eksemplar' => 2]);
        $bukuB = Buku::factory()->create(['jumlah_eksemplar' => 2]);
        PeminjamanDetail::create(['peminjaman_id' => $aktif->id, 'buku_id' => $bukuA->id]);
        PeminjamanDetail::create(['peminjaman_id' => $aktif->id, 'buku_id' => $bukuB->id]);

        // terlambat
        $telat = Peminjaman::factory()->create(['tanggal' => today(), 'tanggal_jatuh_tempo' => today()->subDays(2)]);
        $bukuC = Buku::factory()->create(['jumlah_eksemplar' => 2]);
        PeminjamanDetail::create(['peminjaman_id' => $telat->id, 'buku_id' => $bukuC->id]);

        // kembali hari ini
        $selesai = Peminjaman::factory()->selesai()->create(['tanggal' => today()->subDays(3)]);
        $bukuD = Buku::factory()->create(['jumlah_eksemplar' => 2]);
        PeminjamanDetail::create([
            'peminjaman_id' => $selesai->id,
            'buku_id' => $bukuD->id,
            'tanggal_kembali' => today(),
            'keterlambatan_hari' => null,
        ]);

        Livewire::actingAs(User::factory()->petugas()->create())
            ->test('dashboard')
            ->assertSet('totalEksemplar', 14)
            ->assertSet('totalJudul', 7)
            ->assertSet('totalAnggota', 5)
            ->assertSet('sedangDipinjam', 3)
            ->assertSet('terlambat', 1)
            ->assertSet('kembaliHariIni', 1)
            ->assertSet('grafik.'.today()->format('Y-m-d'), 2);
    }
}
```
Catatan: `grafik` adalah array `['Y-m-d' => count]`; key tanggal hari ini pakai `today()->format('Y-m-d')`. `totalEksemplar` = 3 buku × 2 + bukuA–D × 2 = 14.

- [ ] **Step 2: Run, pastikan gagal**

Run: `php artisan test --filter=DashboardTest`
Expected: FAIL — komponen `dashboard` tidak ditemukan.

- [ ] **Step 3: Buat komponen PHP**

`resources/views/components/dashboard/dashboard.php`:
```php
<?php

use App\Models\Anggota;
use App\Models\Buku;
use App\Models\Peminjaman;
use App\Models\PeminjamanDetail;
use Livewire\Component;

new class extends Component
{
    public $totalEksemplar = 0;
    public $totalJudul = 0;
    public $totalAnggota = 0;
    public $sedangDipinjam = 0;
    public $terlambat = 0;
    public $kembaliHariIni = 0;
    public $grafik = [];
    public $maksGrafik = 1;

    public function render()
    {
        $this->totalEksemplar = (int) Buku::sum('jumlah_eksemplar');
        $this->totalJudul = Buku::count();
        $this->totalAnggota = Anggota::count();
        $this->sedangDipinjam = PeminjamanDetail::whereNull('tanggal_kembali')
            ->whereHas('peminjaman', fn ($q) => $q->where('status', 'dipinjam'))
            ->count();
        $this->terlambat = PeminjamanDetail::whereNull('tanggal_kembali')
            ->whereHas('peminjaman', fn ($q) => $q
                ->where('status', 'dipinjam')
                ->whereDate('tanggal_jatuh_tempo', '<', today()))
            ->count();
        $this->kembaliHariIni = PeminjamanDetail::whereDate('tanggal_kembali', today())->count();

        $perHari = Peminjaman::where('tanggal', '>=', today()->subDays(6))
            ->selectRaw('tanggal, count(*) as total')
            ->groupBy('tanggal')
            ->orderBy('tanggal')
            ->pluck('total', 'tanggal');

        $this->grafik = [];
        for ($i = 6; $i >= 0; $i--) {
            $tanggal = today()->subDays($i);
            $this->grafik[$tanggal->format('Y-m-d')] = (int) ($perHari[$tanggal->format('Y-m-d')] ?? 0);
        }
        $this->maksGrafik = max(1, max(array_values($this->grafik)));

        return view('components.dashboard.dashboard');
    }
};
```

- [ ] **Step 4: Buat blade**

`resources/views/components/dashboard/dashboard.blade.php`:
```blade
<div>
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-3xl font-bold text-gray-800">{{ $totalEksemplar }}</div>
            <div class="text-sm text-gray-500">Jumlah Buku</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-3xl font-bold text-gray-800">{{ $totalJudul }}</div>
            <div class="text-sm text-gray-500">Jumlah Judul</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-3xl font-bold text-gray-800">{{ $totalAnggota }}</div>
            <div class="text-sm text-gray-500">Jumlah Anggota</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-3xl font-bold text-gray-800">{{ $sedangDipinjam }}</div>
            <div class="text-sm text-gray-500">Sedang Dipinjam</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-3xl font-bold text-red-600">{{ $terlambat }}</div>
            <div class="text-sm text-gray-500">Terlambat</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-3xl font-bold text-gray-800">{{ $kembaliHariIni }}</div>
            <div class="text-sm text-gray-500">Kembali Hari Ini</div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <h2 class="font-semibold text-gray-800 mb-4">Peminjaman 7 Hari Terakhir</h2>
        <div class="flex items-end gap-2 h-40">
            @foreach ($grafik as $tanggal => $jumlah)
                <div class="flex-1 flex flex-col items-center gap-1">
                    <div class="text-xs text-gray-500">{{ $jumlah }}</div>
                    <div class="w-full bg-blue-500 rounded-t" style="height: {{ $jumlah > 0 ? ($jumlah / $maksGrafik) * 100 : 4 }}%"></div>
                    <div class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($tanggal)->format('d/m') }}</div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <a href="{{ route('buku.index') }}" class="bg-white rounded-lg shadow p-4 text-center text-blue-600 hover:bg-blue-50 font-medium">Tambah Buku</a>
        <a href="{{ route('peminjaman.index') }}" class="bg-white rounded-lg shadow p-4 text-center text-blue-600 hover:bg-blue-50 font-medium">Pinjam Buku</a>
        <a href="{{ route('pengembalian.index') }}" class="bg-white rounded-lg shadow p-4 text-center text-blue-600 hover:bg-blue-50 font-medium">Kembalikan Buku</a>
    </div>
</div>
```

- [ ] **Step 5: Update dashboard page**

`resources/views/dashboard.blade.php` — ganti seluruh isi dengan:
```blade
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <livewire:dashboard />
        </div>
    </div>
</x-app-layout>
```

- [ ] **Step 6: Run, pastikan PASS**

Run: `php artisan test --filter=DashboardTest`
Expected: PASS.

- [ ] **Step 7: Commit**

```bash
git add resources/views/components/dashboard resources/views/dashboard.blade.php tests/Feature/Livewire/DashboardTest.php
git commit -m "feat: dashboard widgets, 7-day chart, shortcuts"
```

---

## Task 6: Laporan + export

**Files:**
- Create: `app/Exports/BukuExport.php`, `app/Exports/AnggotaExport.php`, `app/Exports/TransaksiExport.php`
- Create: `app/Http/Controllers/ReportController.php`
- Create: `resources/views/reports/pdf/buku.blade.php`, `resources/views/reports/pdf/anggota.blade.php`, `resources/views/reports/pdf/transaksi.blade.php`
- Create: `resources/views/components/laporan/laporan.php`, `resources/views/components/laporan/laporan.blade.php`, `resources/views/laporan/index.blade.php`
- Modify: `routes/web.php:30`
- Test: `tests/Feature/ReportTest.php`

**Interfaces:**
- Produces: `BukuExport::__construct(?int $kategoriId, ?int $rakId)` + `collection(): Illuminate\Support\Collection` (array rows). `AnggotaExport::__construct(?string $status)` + `collection()`. `TransaksiExport::__construct(string $jenis)` (`peminjaman|pengembalian|terlambat`) + `collection()`. Ketiganya juga `WithHeadings`.
- Route `laporan.export` = `GET /laporan/export/{tipe}/{format}` (`tipe` = `buku|anggota|transaksi`, `format` = `pdf|excel`).
- Komponen `laporan` pakai collection export class untuk preview — DRY.

- [ ] **Step 1: Tulis test gagal**

`tests/Feature/ReportTest.php`:
```php
<?php

namespace Tests\Feature;

use App\Models\Anggota;
use App\Models\Buku;
use App\Models\Peminjaman;
use App\Models\PeminjamanDetail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->petugas()->create();
    }

    public function test_export_pdf_buku(): void
    {
        Buku::factory()->count(2)->create();

        $response = $this->actingAs($this->user)->get('/laporan/export/buku/pdf');

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', $response->headers->get('content-type'));
    }

    public function test_export_excel_anggota(): void
    {
        Anggota::factory()->count(2)->create();

        $response = $this->actingAs($this->user)->get('/laporan/export/anggota/excel');

        $response->assertOk();
        $this->assertStringContainsString('spreadsheet', $response->headers->get('content-type'));
    }

    public function test_laporan_transaksi_terlambat(): void
    {
        $buku = Buku::factory()->create(['jumlah_eksemplar' => 2]);
        $peminjaman = Peminjaman::factory()->create(['tanggal_jatuh_tempo' => today()->subDays(3)]);
        PeminjamanDetail::create([
            'peminjaman_id' => $peminjaman->id,
            'buku_id' => $buku->id,
            'tanggal_kembali' => today(),
            'keterlambatan_hari' => 3,
        ]);

        $rows = (new \App\Exports\TransaksiExport('terlambat'))->collection();

        $this->assertCount(1, $rows);
        $this->assertEquals($peminjaman->no_transaksi, $rows->first()[0]);
    }

    public function test_format_tidak_dikenal_404(): void
    {
        $response = $this->actingAs($this->user)->get('/laporan/export/buku/unknown');
        $response->assertNotFound();
    }
}
```

- [ ] **Step 2: Run, pastikan gagal**

Run: `php artisan test --filter=ReportTest`
Expected: FAIL — class/route tidak ada.

- [ ] **Step 3: Buat export classes**

`app/Exports/BukuExport.php`:
```php
<?php

namespace App\Exports;

use App\Models\Buku;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class BukuExport implements FromCollection, WithHeadings
{
    public function __construct(private ?int $kategoriId = null, private ?int $rakId = null)
    {
    }

    public function collection(): Collection
    {
        return Buku::with(['kategori', 'rak'])
            ->when($this->kategoriId, fn ($q) => $q->where('kategori_id', $this->kategoriId))
            ->when($this->rakId, fn ($q) => $q->where('rak_id', $this->rakId))
            ->orderBy('kode')
            ->get()
            ->map(fn (Buku $b) => [
                $b->kode,
                $b->isbn,
                $b->judul,
                $b->kategori->nama,
                $b->pengarang,
                $b->penerbit,
                $b->tahun,
                $b->rak->kode,
                $b->stokTersedia(),
                $b->status === 'aktif' ? 'Aktif' : 'Tidak',
            ]);
    }

    public function headings(): array
    {
        return ['Kode', 'ISBN', 'Judul', 'Kategori', 'Pengarang', 'Penerbit', 'Tahun', 'Rak', 'Stok', 'Status'];
    }
}
```

`app/Exports/AnggotaExport.php`:
```php
<?php

namespace App\Exports;

use App\Models\Anggota;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class AnggotaExport implements FromCollection, WithHeadings
{
    public function __construct(private ?string $status = null)
    {
    }

    public function collection(): Collection
    {
        return Anggota::query()
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->orderBy('nama')
            ->get()
            ->map(fn (Anggota $a) => [
                $a->nis,
                $a->nama,
                $a->kelas,
                $a->jenis_kelamin,
                $a->status,
            ]);
    }

    public function headings(): array
    {
        return ['NIS', 'Nama', 'Kelas', 'Jenis Kelamin', 'Status'];
    }
}
```

`app/Exports/TransaksiExport.php`:
```php
<?php

namespace App\Exports;

use App\Models\Peminjaman;
use App\Models\PeminjamanDetail;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TransaksiExport implements FromCollection, WithHeadings
{
    public function __construct(private string $jenis = 'peminjaman')
    {
    }

    public function collection(): Collection
    {
        if ($this->jenis === 'pengembalian') {
            return PeminjamanDetail::with(['peminjaman.anggota', 'buku'])
                ->whereNotNull('tanggal_kembali')
                ->orderByDesc('tanggal_kembali')
                ->get()
                ->map(fn (PeminjamanDetail $d) => [
                    $d->peminjaman->no_transaksi,
                    $d->tanggal_kembali->format('d/m/Y'),
                    $d->buku->judul,
                    $d->peminjaman->anggota->nama,
                ]);
        }

        if ($this->jenis === 'terlambat') {
            return PeminjamanDetail::with(['peminjaman.anggota', 'buku'])
                ->whereNotNull('keterlambatan_hari')
                ->where('keterlambatan_hari', '>', 0)
                ->orderByDesc('keterlambatan_hari')
                ->get()
                ->map(fn (PeminjamanDetail $d) => [
                    $d->peminjaman->no_transaksi,
                    $d->peminjaman->anggota->nama,
                    $d->buku->judul,
                    $d->peminjaman->tanggal_jatuh_tempo->format('d/m/Y'),
                    $d->keterlambatan_hari,
                ]);
        }

        return Peminjaman::with(['anggota', 'petugas'])
            ->orderByDesc('tanggal')
            ->get()
            ->map(fn (Peminjaman $p) => [
                $p->no_transaksi,
                $p->tanggal->format('d/m/Y'),
                $p->anggota->nama,
                $p->petugas->name,
                $p->details()->count(),
                $p->status,
            ]);
    }

    public function headings(): array
    {
        return match ($this->jenis) {
            'pengembalian' => ['No Transaksi', 'Tanggal Kembali', 'Buku', 'Anggota'],
            'terlambat' => ['No Transaksi', 'Anggota', 'Buku', 'Jatuh Tempo', 'Terlambat (Hari)'],
            default => ['No Transaksi', 'Tanggal', 'Anggota', 'Petugas', 'Jumlah Buku', 'Status'],
        };
    }
}
```

- [ ] **Step 4: Buat ReportController**

`app/Http/Controllers/ReportController.php`:
```php
<?php

namespace App\Http\Controllers;

use App\Exports\AnggotaExport;
use App\Exports\BukuExport;
use App\Exports\TransaksiExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function export(string $tipe, string $format, Request $request): Response
    {
        $export = match ($tipe) {
            'buku' => new BukuExport(
                $request->integer('kategori_id') ?: null,
                $request->integer('rak_id') ?: null,
            ),
            'anggota' => new AnggotaExport($request->string('status') ?: null),
            'transaksi' => new TransaksiExport($request->string('jenis') ?: 'peminjaman'),
            default => abort(404),
        };

        if ($format === 'excel') {
            return Excel::download($export, "$tipe.xlsx");
        }

        if ($format === 'pdf') {
            $pdf = Pdf::loadView("reports.pdf.$tipe", [
                'rows' => $export->collection(),
                'headings' => $export->headings(),
            ]);

            return $pdf->download("$tipe.pdf");
        }

        abort(404);
    }
}
```

- [ ] **Step 5: Buat view PDF**

`resources/views/reports/pdf/buku.blade.php`:
```blade
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Buku</title>
    <style>
        table { width: 100%; border-collapse: collapse; font-size: 11px; }
        th, td { border: 1px solid #ddd; padding: 4px 6px; text-align: left; }
        th { background: #f3f4f6; }
    </style>
</head>
<body>
    <h2 style="text-align:center;">Laporan Buku</h2>
    <table>
        <thead><tr>@foreach ($headings as $h)<th>{{ $h }}</th>@endforeach</tr></thead>
        <tbody>
            @foreach ($rows as $row)
                <tr>@foreach ($row as $cell)<td>{{ $cell }}</td>@endforeach</tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
```
Salin file yang sama untuk `resources/views/reports/pdf/anggota.blade.php` dan `transaksi.blade.php` dengan judul `Laporan Anggota` / `Laporan Transaksi` (struktur tabel identik).

- [ ] **Step 6: Buat komponen laporan PHP**

`resources/views/components/laporan/laporan.php`:
```php
<?php

use App\Exports\AnggotaExport;
use App\Exports\BukuExport;
use App\Exports\TransaksiExport;
use App\Models\Kategori;
use App\Models\Rak;
use Livewire\Component;

new class extends Component
{
    public $tipe = 'buku';
    public $kategoriId = '';
    public $rakId = '';
    public $statusAnggota = '';
    public $jenisTransaksi = 'peminjaman';

    public $rows = [];
    public $headings = [];
    public $kategoriList = [];
    public $rakList = [];

    public function mount(): void
    {
        $this->kategoriList = Kategori::orderBy('nama')->get();
        $this->rakList = Rak::orderBy('kode')->get();
        $this->muatData();
    }

    public function updated(): void
    {
        $this->muatData();
    }

    public function muatData(): void
    {
        $export = match ($this->tipe) {
            'buku' => new BukuExport($this->kategoriId ?: null, $this->rakId ?: null),
            'anggota' => new AnggotaExport($this->statusAnggota ?: null),
            default => new TransaksiExport($this->jenisTransaksi),
        };

        $this->rows = $export->collection();
        $this->headings = $export->headings();
    }

    public function render()
    {
        return view('components.laporan.laporan');
    }
};
```

- [ ] **Step 7: Buat blade laporan**

`resources/views/components/laporan/laporan.blade.php`:
```blade
<div>
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Laporan</h1>
    </div>

    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <div class="flex gap-2 mb-4">
            <button wire:click="$set('tipe', 'buku')" class="px-4 py-2 rounded-lg text-sm font-medium {{ $tipe === 'buku' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700' }}">Buku</button>
            <button wire:click="$set('tipe', 'anggota')" class="px-4 py-2 rounded-lg text-sm font-medium {{ $tipe === 'anggota' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700' }}">Anggota</button>
            <button wire:click="$set('tipe', 'transaksi')" class="px-4 py-2 rounded-lg text-sm font-medium {{ $tipe === 'transaksi' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700' }}">Transaksi</button>
        </div>

        <div class="flex flex-wrap gap-4 mb-4">
            @if ($tipe === 'buku')
                <select wire:model.live="kategoriId" class="rounded-md border-gray-300 shadow-sm text-sm">
                    <option value="">Semua Kategori</option>
                    @foreach ($kategoriList as $k)<option value="{{ $k->id }}">{{ $k->nama }}</option>@endforeach
                </select>
                <select wire:model.live="rakId" class="rounded-md border-gray-300 shadow-sm text-sm">
                    <option value="">Semua Rak</option>
                    @foreach ($rakList as $r)<option value="{{ $r->id }}">{{ $r->kode }}</option>@endforeach
                </select>
            @elseif ($tipe === 'anggota')
                <select wire:model.live="statusAnggota" class="rounded-md border-gray-300 shadow-sm text-sm">
                    <option value="">Semua Anggota</option>
                    <option value="aktif">Aktif</option>
                    <option value="lulus">Lulus</option>
                    <option value="pindah">Pindah</option>
                    <option value="nonaktif">Nonaktif</option>
                </select>
            @else
                <select wire:model.live="jenisTransaksi" class="rounded-md border-gray-300 shadow-sm text-sm">
                    <option value="peminjaman">Peminjaman</option>
                    <option value="pengembalian">Pengembalian</option>
                    <option value="terlambat">Terlambat</option>
                </select>
            @endif
        </div>

        <div class="flex gap-2 mb-4">
            <a href="{{ route('laporan.export', ['tipe' => $tipe, 'format' => 'pdf', 'kategori_id' => $kategoriId, 'rak_id' => $rakId, 'status' => $statusAnggota, 'jenis' => $jenisTransaksi]) }}" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-medium">Export PDF</a>
            <a href="{{ route('laporan.export', ['tipe' => $tipe, 'format' => 'excel', 'kategori_id' => $kategoriId, 'rak_id' => $rakId, 'status' => $statusAnggota, 'jenis' => $jenisTransaksi]) }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium">Export Excel</a>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        @foreach ($headings as $h)
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ $h }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($rows as $row)
                        <tr>
                            @foreach ($row as $cell)
                                <td class="px-4 py-2 text-sm text-gray-700">{{ $cell }}</td>
                            @endforeach
                        </tr>
                    @empty
                        <tr><td colspan="20" class="px-4 py-4 text-center text-sm text-gray-500">Tidak ada data.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
```

- [ ] **Step 8: Buat page view + route**

`resources/views/laporan/index.blade.php`:
```blade
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Laporan</h2>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <livewire:laporan />
        </div>
    </div>
</x-app-layout>
```

`routes/web.php:30`, ganti:
```php
Route::get('/laporan', fn () => view('dashboard'))->name('laporan.index');
```
menjadi:
```php
Route::get('/laporan', fn () => view('laporan.index'))->name('laporan.index');
Route::get('/laporan/export/{tipe}/{format}', [ReportController::class, 'export'])
    ->whereIn('tipe', ['buku', 'anggota', 'transaksi'])
    ->whereIn('format', ['pdf', 'excel'])
    ->name('laporan.export');
```
Tambah import di atas file `routes/web.php`:
```php
use App\Http\Controllers\ReportController;
```

- [ ] **Step 9: Run, pastikan PASS**

Run: `php artisan test --filter=ReportTest`
Expected: 4 tests PASS.

- [ ] **Step 10: Commit**

```bash
git add app/Exports app/Http/Controllers/ReportController.php resources/views/reports resources/views/components/laporan resources/views/laporan routes/web.php tests/Feature/ReportTest.php
git commit -m "feat: reports with PDF/Excel export"
```

---

## Task 7: Setting page

**Files:**
- Create: `resources/views/components/setting/setting.php`, `resources/views/components/setting/setting.blade.php`, `resources/views/setting/index.blade.php`
- Modify: `routes/web.php:32`
- Test: `tests/Feature/Livewire/SettingPageTest.php`

**Interfaces:**
- Consumes: `Setting::get(key, default)`, `Setting::set(key, value)`.
- Produces: komponen `setting`; route `setting.index` (tetap `can:manage-system`).

- [ ] **Step 1: Tulis test gagal**

`tests/Feature/Livewire/SettingPageTest.php`:
```php
<?php

namespace Tests\Feature\Livewire;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use Storage;
use Tests\TestCase;

class SettingPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_simpan_pengaturan(): void
    {
        Livewire::actingAs(User::factory()->admin()->create())
            ->test('setting')
            ->set('namaPerpus', 'Perpustakaan SMP Nusantara')
            ->set('lamaPinjam', 14)
            ->set('maksimalBuku', 5)
            ->call('simpan');

        $this->assertEquals('Perpustakaan SMP Nusantara', Setting::get('nama_perpus'));
        $this->assertEquals('14', Setting::get('lama_pinjam'));
        $this->assertEquals('5', Setting::get('maksimal_buku'));
    }

    public function test_upload_logo(): void
    {
        Storage::fake('public');
        $file = new NamedUploadedFile(
            UploadedFile::fake()->image('logo.png')->getRealPath(),
            'logo.png',
            'image/png',
            null,
            true
        );

        Livewire::actingAs(User::factory()->admin()->create())
            ->test('setting')
            ->set('logo', $file)
            ->call('simpan');

        $this->assertNotNull(Setting::get('logo'));
        Storage::disk('public')->assertExists(Setting::get('logo'));
    }
}

class NamedUploadedFile extends UploadedFile
{
    public string $name;

    public function __construct(string $path, string $originalName, ?string $mimeType = null, ?int $error = null, bool $test = false)
    {
        $this->name = $originalName;
        parent::__construct($path, $originalName, $mimeType, $error, $test);
    }
}
```
Catatan: `NamedUploadedFile` subclass wajib karena bug Livewire 4.3.5 + Symfony 7.4 (`$file->name`). Logo disimpan di disk `public` path `logos/`.

- [ ] **Step 2: Run, pastikan gagal**

Run: `php artisan test --filter=SettingPageTest`
Expected: FAIL — komponen `setting` tidak ditemukan.

- [ ] **Step 3: Buat komponen PHP**

`resources/views/components/setting/setting.php`:
```php
<?php

use App\Models\Setting;
use Livewire\Component;
use Livewire\WithFileUploads;

new class extends Component
{
    use WithFileUploads;

    public $namaPerpus = '';
    public $logo = null;
    public $logoPath = null;
    public $lamaPinjam = 7;
    public $maksimalBuku = 3;

    public function mount(): void
    {
        $this->namaPerpus = Setting::get('nama_perpus', 'Perpustakaan');
        $this->logoPath = Setting::get('logo', null);
        $this->lamaPinjam = (int) Setting::get('lama_pinjam', 7);
        $this->maksimalBuku = (int) Setting::get('maksimal_buku', 3);
    }

    public function simpan(): void
    {
        $this->validate([
            'namaPerpus' => 'required|string|max:255',
            'lamaPinjam' => 'required|integer|min:1|max:365',
            'maksimalBuku' => 'required|integer|min:1|max:20',
        ]);

        Setting::set('nama_perpus', $this->namaPerpus);
        Setting::set('lama_pinjam', $this->lamaPinjam);
        Setting::set('maksimal_buku', $this->maksimalBuku);

        if ($this->logo) {
            $path = $this->logo->store('logos', 'public');
            Setting::set('logo', $path);
            $this->logoPath = $path;
            $this->logo = null;
        }

        session()->flash('success', 'Pengaturan berhasil disimpan.');
    }

    public function render()
    {
        return view('components.setting.setting');
    }
};
```

- [ ] **Step 4: Buat blade**

`resources/views/components/setting/setting.blade.php`:
```blade
<div>
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Pengaturan</h1>
    </div>

    @if (session()->has('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">{{ session('success') }}</div>
    @endif

    <div class="bg-white rounded-lg shadow p-6 max-w-2xl">
        <form wire:submit="simpan">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Nama Perpustakaan <span class="text-red-500">*</span></label>
                    <input type="text" wire:model="namaPerpus" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('namaPerpus') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Logo</label>
                    @if ($logoPath)
                        <img src="{{ asset('storage/'.$logoPath) }}" class="h-16 mb-2" alt="Logo">
                    @endif
                    <input type="file" wire:model="logo" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Lama Pinjam (hari) <span class="text-red-500">*</span></label>
                        <input type="number" wire:model="lamaPinjam" min="1" max="365" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('lamaPinjam') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Maksimal Buku <span class="text-red-500">*</span></label>
                        <input type="number" wire:model="maksimalBuku" min="1" max="20" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('maksimalBuku') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>
            <div class="mt-6">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">Simpan</button>
            </div>
        </form>
    </div>
</div>
```

- [ ] **Step 5: Buat page view + route**

`resources/views/setting/index.blade.php`:
```blade
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Setting</h2>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <livewire:setting />
        </div>
    </div>
</x-app-layout>
```

`routes/web.php:32`, ganti:
```php
Route::get('/setting', fn () => view('dashboard'))->middleware('can:manage-system')->name('setting.index');
```
menjadi:
```php
Route::get('/setting', fn () => view('setting.index'))->middleware('can:manage-system')->name('setting.index');
```

- [ ] **Step 6: Run, pastikan PASS**

Run: `php artisan test --filter=SettingPageTest`
Expected: 2 tests PASS.

- [ ] **Step 7: Commit**

```bash
git add resources/views/components/setting resources/views/setting routes/web.php tests/Feature/Livewire/SettingPageTest.php
git commit -m "feat: settings page (name, logo, borrow duration, max books)"
```

---

## Task 8: Filter buku + fix test flaky

**Files:**
- Modify: `resources/views/components/buku/buku.php`, `resources/views/components/buku/buku.blade.php`
- Modify: `database/factories/RakFactory.php`
- Test: `tests/Feature/Livewire/BookTest.php`

**Interfaces:**
- Consumes: `Buku::queryBuku()` — perbaiki query tambah filter pengarang/penerbit/tahun.
- Produces: `filterPengarang`, `filterPenerbit`, `filterTahun` props + list opsi.

- [ ] **Step 1: Fix RakFactory (sumber flaky test)**

`database/factories/RakFactory.php`, ganti line `'kode' => $letter.'-'.str_pad(...),` dengan:
```php
'kode' => $this->faker->unique()->regexify('[A-Z]-[0-9]{2}'),
```
(`unique()` mencegah bentrok kode antar factory dalam satu run — perbaiki `BookTest::test_isbn_duplikat_ditolak`.)

- [ ] **Step 2: Update komponen PHP**

`resources/views/components/buku/buku.php`:
- Tambah props setelah `filterRak`:
```php
public $filterPengarang = '';
public $filterPenerbit = '';
public $filterTahun = '';
```
- Tambah props opsi list:
```php
public $pengarangList = [];
public $penerbitList = [];
public $tahunList = [];
```
- Di `mount()` tambah:
```php
$this->pengarangList = Buku::whereNotNull('pengarang')->distinct()->orderBy('pengarang')->pluck('pengarang');
$this->penerbitList = Buku::whereNotNull('penerbit')->distinct()->orderBy('penerbit')->pluck('penerbit');
$this->tahunList = Buku::whereNotNull('tahun')->distinct()->orderByDesc('tahun')->pluck('tahun');
```
- `queryBuku()` tambah `when`:
```php
->when($this->filterPengarang, fn ($q) => $q->where('pengarang', $this->filterPengarang))
->when($this->filterPenerbit, fn ($q) => $q->where('penerbit', $this->filterPenerbit))
->when($this->filterTahun, fn ($q) => $q->where('tahun', $this->filterTahun))
```
- `render()` pass ketiga list ke view.

- [ ] **Step 3: Update blade**

`resources/views/components/buku/buku.blade.php` — tambah 3 select di grid filter (setelah `filterRak`):
```blade
<div>
    <select wire:model.live="filterPengarang" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
        <option value="">Semua Pengarang</option>
        @foreach ($pengarangList as $p)
            <option value="{{ $p }}">{{ $p }}</option>
        @endforeach
    </select>
</div>
<div>
    <select wire:model.live="filterPenerbit" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
        <option value="">Semua Penerbit</option>
        @foreach ($penerbitList as $p)
            <option value="{{ $p }}">{{ $p }}</option>
        @endforeach
    </select>
</div>
<div>
    <select wire:model.live="filterTahun" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
        <option value="">Semua Tahun</option>
        @foreach ($tahunList as $t)
            <option value="{{ $t }}">{{ $t }}</option>
        @endforeach
    </select>
</div>
```
Ubah grid dari `md:grid-cols-4` menjadi `md:grid-cols-6` agar muat.

- [ ] **Step 4: Tambah test filter**

`tests/Feature/Livewire/BookTest.php`, tambah method:
```php
public function test_filter_pengarang(): void
{
    $rak = Rak::first();
    $kat = Kategori::first();
    Buku::create(['kode' => 'BUK-0101', 'judul' => 'Buku Si A', 'pengarang' => 'Penulis Satu', 'kategori_id' => $kat->id, 'rak_id' => $rak->id, 'jumlah_eksemplar' => 1]);
    Buku::create(['kode' => 'BUK-0102', 'judul' => 'Buku Si B', 'pengarang' => 'Penulis Dua', 'kategori_id' => $kat->id, 'rak_id' => $rak->id, 'jumlah_eksemplar' => 1]);

    Livewire::actingAs($this->user)
        ->test('buku')
        ->set('filterPengarang', 'Penulis Satu')
        ->assertSee('Buku Si A')
        ->assertDontSee('Buku Si B');
}
```

- [ ] **Step 5: Run seluruh BookTest**

Run: `php artisan test --filter=BookTest`
Expected: semua PASS (termasuk `test_isbn_duplikat_ditolak` yang sebelumnya flaky).

- [ ] **Step 6: Commit**

```bash
git add resources/views/components/buku database/factories/RakFactory.php tests/Feature/Livewire/BookTest.php
git commit -m "feat: buku filters (pengarang, penerbit, tahun) + fix flaky rak factory"
```

---

## Task 9: Seeder lengkap

**Files:**
- Create: `database/seeders/KategoriSeeder.php`, `RakSeeder.php`, `UserSeeder.php`, `BookSeeder.php`, `AnggotaSeeder.php`, `TransaksiSeeder.php`
- Modify: `database/seeders/DatabaseSeeder.php`

**Interfaces:**
- Produces: data demo lengkap untuk semua modul (dashboard/peminjaman/pengembalian/laporan punya data).

- [ ] **Step 1: Buat KategoriSeeder**

`database/seeders/KategoriSeeder.php`:
```php
<?php

namespace Database\Seeders;

use App\Models\Kategori;
use Illuminate\Database\Seeder;

class KategoriSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Pelajaran', 'Novel', 'Agama', 'Ensiklopedia', 'Komik Edukasi', 'Majalah'] as $nama) {
            Kategori::create(['nama' => $nama]);
        }
    }
}
```

- [ ] **Step 2: Buat RakSeeder**

`database/seeders/RakSeeder.php`:
```php
<?php

namespace Database\Seeders;

use App\Models\Rak;
use Illuminate\Database\Seeder;

class RakSeeder extends Seeder
{
    public function run(): void
    {
        $kodeRak = [];
        foreach (['A', 'B'] as $letter) {
            for ($i = 1; $i <= 5; $i++) {
                $kodeRak[] = sprintf('%s-%02d', $letter, $i);
            }
        }

        foreach ($kodeRak as $kode) {
            Rak::firstOrCreate(['kode' => $kode], [
                'nama' => "Rak $kode",
                'keterangan' => "Rak untuk koleksi $kode",
            ]);
        }
    }
}
```

- [ ] **Step 3: Buat UserSeeder**

`database/seeders/UserSeeder.php`:
```php
<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@perpus.test'],
            ['name' => 'Admin', 'role' => 'admin', 'is_active' => true, 'password' => 'admin123']
        );

        for ($i = 1; $i <= 2; $i++) {
            User::firstOrCreate(
                ['email' => "petugas$i@perpus.test"],
                ['name' => "Petugas $i", 'role' => 'petugas', 'is_active' => true, 'password' => 'password']
            );
        }
    }
}
```

- [ ] **Step 4: Buat BookSeeder**

`database/seeders/BookSeeder.php`:
```php
<?php

namespace Database\Seeders;

use App\Models\Buku;
use Illuminate\Database\Seeder;

class BookSeeder extends Seeder
{
    public function run(): void
    {
        Buku::factory()->count(40)->create();
    }
}
```

- [ ] **Step 5: Buat AnggotaSeeder**

`database/seeders/AnggotaSeeder.php`:
```php
<?php

namespace Database\Seeders;

use App\Models\Anggota;
use Illuminate\Database\Seeder;

class AnggotaSeeder extends Seeder
{
    public function run(): void
    {
        $anggota = Anggota::factory()->count(200)->create();
        $anggota->take(30)->each(fn ($a) => $a->update(['status' => 'nonaktif']));
        $anggota->take(20)->skip(30)->each(fn ($a) => $a->update(['status' => 'lulus']));
        $anggota->take(10)->skip(50)->each(fn ($a) => $a->update(['status' => 'pindah']));
    }
}
```

- [ ] **Step 6: Buat TransaksiSeeder**

`database/seeders/TransaksiSeeder.php`:
```php
<?php

namespace Database\Seeders;

use App\Models\Anggota;
use App\Models\Buku;
use App\Models\Peminjaman;
use App\Models\PeminjamanDetail;
use App\Models\User;
use Illuminate\Database\Seeder;

class TransaksiSeeder extends Seeder
{
    public function run(): void
    {
        $petugas = User::where('role', 'petugas')->pluck('id')->all();
        $anggota = Anggota::where('status', 'aktif')->pluck('id')->all();
        $bukuList = Buku::where('status', 'aktif')->get();
        $bukuIds = $bukuList->pluck('id')->all();

        foreach (range(1, 30) as $i) {
            $peminjaman = Peminjaman::create([
                'no_transaksi' => Peminjaman::generateNoTransaksi(),
                'tanggal' => today()->subDays(rand(0, 20)),
                'tanggal_jatuh_tempo' => today()->subDays(rand(-10, 10)),
                'petugas_id' => $petugas[array_rand($petugas)],
                'anggota_id' => $anggota[array_rand($anggota)],
                'status' => 'dipinjam',
            ]);

            $jumlah = rand(1, 3);
            $dipilih = array_rand($bukuIds, min($jumlah, count($bukuIds)));

            foreach ((array) $dipilih as $key) {
                $sudahKembali = $peminjaman->tanggal_jatuh_tempo->lt(today()) ? (bool) rand(0, 1) : false;
                PeminjamanDetail::create([
                    'peminjaman_id' => $peminjaman->id,
                    'buku_id' => $bukuIds[$key],
                    'tanggal_kembali' => $sudahKembali ? today() : null,
                    'keterlambatan_hari' => $sudahKembali && $peminjaman->tanggal_jatuh_tempo->lt(today())
                        ? today()->diffInDays($peminjaman->tanggal_jatuh_tempo)
                        : null,
                ]);
            }

            if ($peminjaman->sudahSelesai()) {
                $peminjaman->update(['status' => 'selesai']);
            }
        }
    }
}
```

- [ ] **Step 7: Update DatabaseSeeder**

`database/seeders/DatabaseSeeder.php`, ganti seluruh isi:
```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            KategoriSeeder::class,
            RakSeeder::class,
            UserSeeder::class,
            BookSeeder::class,
            AnggotaSeeder::class,
            TransaksiSeeder::class,
        ]);
    }
}
```

- [ ] **Step 8: Verifikasi seeder**

Run: `php artisan migrate:fresh --seed`
Expected: tanpa error. Cek hitung:
```bash
php artisan tinker --execute="echo 'kategori='.\App\Models\Kategori::count().' rak='.\App\Models\Rak::count().' buku='.\App\Models\Buku::count().' anggota='.\App\Models\Anggota::count().' transaksi='.\App\Models\Peminjaman::count();"
```
Expected: `kategori=6 rak=10 buku=40 anggota=200 transaksi=30`.

- [ ] **Step 9: Commit**

```bash
git add database/seeders
git commit -m "feat: full demo seeder (kategori, rak, user, buku, anggota, transaksi)"
```

---

## Task 10: Verifikasi akhir

**Files:**
- None (verifikasi penuh)

- [ ] **Step 1: Full test suite**

Run: `composer test`
Expected: seluruh test PASS (77 + test baru ≈ 100+), nol fail.

- [ ] **Step 2: Format**

Run: `vendor/bin/pint`
Expected: clean (fix otomatis jika perlu, ulangi test bila ada perubahan).

- [ ] **Step 3: Build aset**

Run: `npm run build`
Expected: build sukses.

- [ ] **Step 4: Smoke manual (opsional, butuh server)**

```bash
php artisan serve
```
Login `admin@perpus.test` / `admin123` → cek dashboard widget → pinjam buku → kembali buku → buka laporan + export PDF/Excel → ubah setting.

- [ ] **Step 5: Commit hasil verify**

```bash
git add -A && git commit -m "build: complete remaining PRD modules (dashboard, borrow, return, report, setting, seeder)"
```
