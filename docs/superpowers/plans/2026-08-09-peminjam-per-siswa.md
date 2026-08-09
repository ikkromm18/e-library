# Peminjam Buku per Siswa Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Menambahkan menu "Peminjam per Siswa" untuk melihat daftar peminjam buku yang di-grouping berdasarkan siswa/anggota beserta detail riwayat buku yang dipinjam menggunakan expandable row.

**Architecture:** Menggunakan pola Livewire 4.3 SFC (Single File Component) pada `resources/views/components/peminjaman/peminjam-per-siswa.php` & `peminjam-per-siswa.blade.php`, eager loading Eloquent (`with(['peminjamen.details.buku'])`), dan Alpine.js untuk fitur expand/collapse UI row.

**Tech Stack:** Laravel 13, Livewire 4.3, Tailwind CSS, Alpine.js, PHPUnit.

## Global Constraints

- Livewire SFC layout: Anonymous component di `resources/views/components/peminjaman/peminjam-per-siswa.php` + view Blade di `resources/views/components/peminjaman/peminjam-per-siswa.blade.php`.
- Do NOT create `app/Livewire/`.
- Maintain UI design tokens (`bg-surface`, `border-border`, `text-text-primary`, `text-text-secondary`, dsb).
- Prevent N+1 query problem using Eager Loading.

---

### Task 1: Add Route & Page Container View

**Files:**
- Create: `resources/views/peminjaman/per-siswa.blade.php`
- Modify: `routes/web.php`

- [ ] **Step 1: Create `resources/views/peminjaman/per-siswa.blade.php`**

```html
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-text-primary leading-tight">
            {{ __('Daftar Peminjam Buku (per Siswa)') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <livewire:peminjaman.peminjam-per-siswa />
        </div>
    </div>
</x-app-layout>
```

- [ ] **Step 2: Add route in `routes/web.php`**

Inside `Route::middleware('auth')->group(...)`:
```php
Route::get('/peminjaman/per-siswa', fn () => view('peminjaman.per-siswa'))->name('peminjaman.per-siswa');
```

---

### Task 2: Create Livewire SFC Component (PHP Logic & Blade View)

**Files:**
- Create: `resources/views/components/peminjaman/peminjam-per-siswa.php`
- Create: `resources/views/components/peminjaman/peminjam-per-siswa.blade.php`

- [ ] **Step 1: Create PHP class `resources/views/components/peminjaman/peminjam-per-siswa.php`**

```php
<?php

use App\Models\Anggota;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public int $perPage = 10;
    public string $search = '';
    public string $filterStatus = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterStatus(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    public function queryPeminjam()
    {
        return Anggota::query()
            ->has('peminjamen')
            ->withCount([
                'peminjamen as total_transaksi',
                'peminjamen as pinjaman_aktif_count' => function ($q) {
                    $q->where('status', 'dipinjam');
                },
            ])
            ->with([
                'peminjamen' => function ($q) {
                    $q->orderBy('created_at', 'desc')->with(['details.buku', 'petugas']);
                },
            ])
            ->when($this->search, function ($q) {
                $q->where(function ($sub) {
                    $sub->where('nis', 'like', "%{$this->search}%")
                        ->orWhere('nama', 'like', "%{$this->search}%")
                        ->orWhere('kelas', 'like', "%{$this->search}%");
                });
            })
            ->when($this->filterStatus === 'aktif', function ($q) {
                $q->whereHas('peminjamen', fn ($p) => $p->where('status', 'dipinjam'));
            })
            ->when($this->filterStatus === 'terlambat', function ($q) {
                $q->whereHas('peminjamen', fn ($p) => $p->where('status', 'dipinjam')->where('tanggal_jatuh_tempo', '<', today()));
            })
            ->orderBy('nama');
    }

    public function render()
    {
        return view('components.peminjaman.peminjam-per-siswa', [
            'peminjamList' => $this->queryPeminjam()->paginate($this->perPage),
        ]);
    }
};
```

- [ ] **Step 2: Create Blade view `resources/views/components/peminjaman/peminjam-per-siswa.blade.php`**

```html
<div class="bg-surface p-6 rounded-lg shadow border border-border space-y-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h3 class="text-lg font-bold text-text-primary">Daftar Peminjam Buku per Siswa</h3>
            <p class="text-sm text-text-secondary">Daftar anggota perpustakaan beserta riwayat dan status pinjamannya.</p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <div class="relative min-w-[240px]">
                <input 
                    type="text" 
                    wire:model.live.debounce.300ms="search"
                    placeholder="Cari NIS, Nama, atau Kelas..." 
                    class="w-full pl-9 pr-4 py-2 border border-border rounded-lg text-sm bg-surface text-text-primary focus:ring-2 focus:ring-primary focus:border-transparent"
                />
                <svg class="w-4 h-4 absolute left-3 top-3 text-text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>

            <select wire:model.live="filterStatus" class="border border-border rounded-lg text-sm px-3 py-2 bg-surface text-text-primary focus:ring-2 focus:ring-primary">
                <option value="">Semua Status</option>
                <option value="aktif">Memiliki Pinjaman Aktif</option>
                <option value="terlambat">Ada Keterlambatan</option>
            </select>

            <select wire:model.live="perPage" class="border border-border rounded-lg text-sm px-3 py-2 bg-surface text-text-primary focus:ring-2 focus:ring-primary">
                <option value="10">10 / hal</option>
                <option value="25">25 / hal</option>
                <option value="50">50 / hal</option>
            </select>
        </div>
    </div>

    <div class="overflow-x-auto border border-border rounded-lg">
        <table class="w-full text-sm text-left text-text-primary">
            <thead class="bg-surface-muted text-xs uppercase text-text-secondary border-b border-border">
                <tr>
                    <th class="p-3 w-10 text-center">#</th>
                    <th class="p-3">NIS</th>
                    <th class="p-3">Nama Siswa</th>
                    <th class="p-3">Kelas</th>
                    <th class="p-3 text-center">Total Transaksi</th>
                    <th class="p-3 text-center">Status Pinjaman</th>
                    <th class="p-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border">
                @forelse ($peminjamList as $siswa)
                    <tr x-data="{ expanded: false }" class="hover:bg-surface-muted/50 transition-colors">
                        <td class="p-3 text-center">
                            <button 
                                @click="expanded = !expanded" 
                                class="p-1 rounded-md hover:bg-surface-muted text-text-secondary focus:outline-none transition-transform duration-200"
                                :class="{ 'rotate-90': expanded }"
                                title="Lihat detail buku"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </button>
                        </td>
                        <td class="p-3 font-mono font-medium">{{ $siswa->nis }}</td>
                        <td class="p-3 font-semibold">{{ $siswa->nama }}</td>
                        <td class="p-3">{{ $siswa->kelas ?? '-' }}</td>
                        <td class="p-3 text-center font-medium">{{ $siswa->total_transaksi }} Transaksi</td>
                        <td class="p-3 text-center">
                            @if ($siswa->pinjaman_aktif_count > 0)
                                <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300">
                                    {{ $siswa->pinjaman_aktif_count }} Pinjaman Aktif
                                </span>
                            @else
                                <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300">
                                    Bebas Pinjaman
                                </span>
                            @endif
                        </td>
                        <td class="p-3 text-center">
                            <button 
                                @click="expanded = !expanded" 
                                class="text-xs text-primary font-medium hover:underline inline-flex items-center gap-1"
                            >
                                <span x-text="expanded ? 'Sembunyikan' : 'Detail Buku'"></span>
                            </button>
                        </td>

                        <tr x-show="expanded" x-cloak class="bg-surface-muted/30 border-t border-b border-border">
                            <td colspan="7" class="p-4 sm:p-6">
                                <div class="bg-surface border border-border rounded-md p-4 space-y-4 shadow-inner">
                                    <h4 class="font-bold text-sm text-text-primary flex items-center gap-2">
                                        <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                        </svg>
                                        Riwayat Peminjaman Buku - {{ $siswa->nama }} ({{ $siswa->nis }})
                                    </h4>

                                    @if ($siswa->peminjamen->isEmpty())
                                        <p class="text-sm text-text-secondary italic">Belum ada riwayat peminjaman.</p>
                                    @else
                                        <div class="space-y-4">
                                            @foreach ($siswa->peminjamen as $peminjaman)
                                                <div class="border border-border rounded-md p-3 bg-surface text-xs space-y-2">
                                                    <div class="flex flex-wrap justify-between items-center border-b border-border pb-2 gap-2">
                                                        <div class="flex items-center gap-3">
                                                            <span class="font-mono font-bold text-text-primary">{{ $peminjaman->no_transaksi }}</span>
                                                            <span class="text-text-secondary">Tgl Pinjam: <strong>{{ $peminjaman->tanggal->format('d/m/Y') }}</strong></span>
                                                            <span class="text-text-secondary">Jatuh Tempo: <strong>{{ $peminjaman->tanggal_jatuh_tempo->format('d/m/Y') }}</strong></span>
                                                        </div>
                                                        <div>
                                                            @if ($peminjaman->status === 'dipinjam')
                                                                @if (now()->startOfDay()->gt($peminjaman->tanggal_jatuh_tempo))
                                                                    <span class="px-2 py-0.5 rounded bg-rose-100 text-rose-800 dark:bg-rose-900/30 dark:text-rose-300 font-semibold">
                                                                        Terlambat ({{ $peminjaman->hitungKeterlambatan() }} hari)
                                                                    </span>
                                                                @else
                                                                    <span class="px-2 py-0.5 rounded bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300 font-semibold">
                                                                        Sedang Dipinjam
                                                                    </span>
                                                                @endif
                                                            @else
                                                                <span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300 font-semibold">
                                                                    Selesai
                                                                </span>
                                                            @endif
                                                        </div>
                                                    </div>

                                                    <div class="pt-1">
                                                        <p class="font-medium text-text-secondary mb-1">Buku yang dipinjam:</p>
                                                        <ul class="divide-y divide-border/50">
                                                            @foreach ($peminjaman->details as $detail)
                                                                <li class="py-1.5 flex justify-between items-center">
                                                                    <div>
                                                                        <span class="font-semibold text-text-primary">{{ $detail->buku->judul ?? 'Buku dihapus' }}</span>
                                                                        <span class="text-text-secondary ml-2 font-mono">({{ $detail->buku->kode_buku ?? '-' }})</span>
                                                                    </div>
                                                                    <div>
                                                                        @if ($detail->tanggal_kembali)
                                                                            <span class="text-emerald-600 dark:text-emerald-400">
                                                                                Dikembalikan: {{ $detail->tanggal_kembali->format('d/m/Y') }}
                                                                            </span>
                                                                        @else
                                                                            <span class="text-amber-600 dark:text-amber-400">Belum Kembali</span>
                                                                        @endif
                                                                    </div>
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="p-6 text-center text-text-secondary">
                            Tidak ada data peminjam yang ditemukan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>
        {{ $peminjamList->links() }}
    </div>
</div>
```

---

### Task 3: Update Navigation Layout

**Files:**
- Modify: `resources/views/layouts/navigation.blade.php`

- [ ] **Step 1: Add NavLink in `resources/views/layouts/navigation.blade.php`**

Add navigation link near `peminjaman.index`:
```html
<x-nav-link :href="route('peminjaman.per-siswa')" :active="request()->routeIs('peminjaman.per-siswa')">
    {{ __('Peminjam per Siswa') }}
</x-nav-link>
```
and responsive link:
```html
<x-responsive-nav-link :href="route('peminjaman.per-siswa')" :active="request()->routeIs('peminjaman.per-siswa')">
    {{ __('Peminjam per Siswa') }}
</x-responsive-nav-link>
```

---

### Task 4: Automated Testing & Verification

**Files:**
- Create: `tests/Feature/Livewire/PeminjamPerSiswaTest.php`

- [ ] **Step 1: Write `tests/Feature/Livewire/PeminjamPerSiswaTest.php`**

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

class PeminjamPerSiswaTest extends TestCase
{
    use RefreshDatabase;

    public function test_halaman_peminjam_per_siswa_dapat_diakses_oleh_user_login()
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('peminjaman.per-siswa'))
            ->assertStatus(200);
    }

    public function test_komponen_dapat_menampilkan_siswa_dan_riwayat_peminjaman()
    {
        $user = User::factory()->create();
        $anggota = Anggota::factory()->create(['nama' => 'Budi Santoso', 'nis' => '12345']);
        $buku = Buku::factory()->create(['judul' => 'Belajar Laravel 13']);

        $peminjaman = Peminjaman::create([
            'no_transaksi' => 'PJM-20260809-1',
            'tanggal' => now(),
            'tanggal_jatuh_tempo' => now()->addDays(7),
            'petugas_id' => $user->id,
            'anggota_id' => $anggota->id,
            'status' => 'dipinjam',
        ]);

        PeminjamanDetail::create([
            'peminjaman_id' => $peminjaman->id,
            'buku_id' => $buku->id,
        ]);

        Livewire::actingAs($user)
            ->test('peminjaman.peminjam-per-siswa')
            ->assertSee('Budi Santoso')
            ->assertSee('12345')
            ->assertSee('Belajar Laravel 13');
    }

    public function test_filter_search_berfungsi_dengan_baik()
    {
        $user = User::factory()->create();
        $siswaA = Anggota::factory()->create(['nama' => 'Ahmad Rizki', 'nis' => '11111']);
        $siswaB = Anggota::factory()->create(['nama' => 'Siti Nurhaliza', 'nis' => '22222']);

        Peminjaman::create(['no_transaksi' => 'PJM-1', 'tanggal' => now(), 'tanggal_jatuh_tempo' => now()->addDays(7), 'petugas_id' => $user->id, 'anggota_id' => $siswaA->id, 'status' => 'dipinjam']);
        Peminjaman::create(['no_transaksi' => 'PJM-2', 'tanggal' => now(), 'tanggal_jatuh_tempo' => now()->addDays(7), 'petugas_id' => $user->id, 'anggota_id' => $siswaB->id, 'status' => 'dipinjam']);

        Livewire::actingAs($user)
            ->test('peminjaman.peminjam-per-siswa')
            ->set('search', 'Ahmad')
            ->assertSee('Ahmad Rizki')
            ->assertDontSee('Siti Nurhaliza');
    }
}
```

- [ ] **Step 2: Run test suite**

Run: `php artisan test --filter=PeminjamPerSiswaTest`
Expected: PASS (all 3 tests pass cleanly)
