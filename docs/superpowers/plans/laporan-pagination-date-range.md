# Laporan Pagination Fix + Date-Range Export Filter

## Context

The Laporan page (`resources/views/components/laporan/`) has two issues:
1. **Pagination bug**: clicking page 2+ navigates to `/?page=N` which redirects to dashboard because the manual `LengthAwarePaginator` lacks a base path.
2. **Missing feature**: no date range selection before exporting PDF/Excel for `anggota` and `transaksi` types (buku has no date column).

## Global Constraints

- Follow existing codebase conventions: Livewire SFC pattern, anonymous class, `view()` return.
- Use `#[Url]` for date range props so they persist in URL.
- Empty range = no filter (all data). One-sided = one-sided filter.
- `whereBetween` on date columns (inclusive both endpoints).
- Buku export: no changes (no date column).
- Existing tests must remain green.

## Task 1: Fix pagination base path

**File:** `resources/views/components/laporan/laporan.php`

In `render()`, add `->withPath(route('laporan.index'))` to the `LengthAwarePaginator` so links point to `/laporan?page=N` instead of `/?page=N`.

```php
$paginator = new LengthAwarePaginator(
    collect($this->rows)->forPage($this->page, $this->perPage)->values(),
    count($this->rows),
    $this->perPage,
    $this->page,
)->withPath(route('laporan.index'));
```

## Task 2: Add date range props to component

**File:** `resources/views/components/laporan/laporan.php`

Add:
- `#[Url] public ?string $dariTanggal = null;`
- `#[Url] public ?string $sampaiTanggal = null;`
- `updatedDariTanggal()` — reset page, call `muatData()`
- `updatedSampaiTanggal()` — reset page, call `muatData()`
- Pass `$this->dariTanggal` and `$this->sampaiTanggal` to `AnggotaExport` and `TransaksiExport` in `muatData()`

## Task 3: Exports accept date range

**File:** `app/Exports/AnggotaExport.php`

Constructor: add `?string $dari = null, ?string $sampai = null`.
In `collection()`: add `->when($dari && $sampai, fn ($q) => $q->whereBetween('tanggal_masuk', [$dari, $sampai]))` after the status filter.

**File:** `app/Exports/TransaksiExport.php`

Constructor: add `?string $dari = null, ?string $sampai = null`.
In `collection()`:
- peminjaman: add `->when($dari && $sampai, fn ($q) => $q->whereBetween('tanggal', [$dari, $sampai]))`
- pengembalian: add `->when($dari && $sampai, fn ($q) => $q->whereBetween('tanggal_kembali', [$dari, $sampai]))`
- terlambat: add `->when($dari && $sampai, fn ($q) => $q->whereBetween('tanggal_kembali', [$dari, $sampai]))`

**File:** `app/Exports/BukuExport.php` — no changes.

## Task 4: Controller reads date range

**File:** `app/Http/Controllers/ReportController.php`

Read `$request->string('dari')` and `$request->string('sampai')`.
Pass to `AnggotaExport` and `TransaksiExport` construction.

## Task 5: Blade date inputs + export links

**File:** `resources/views/components/laporan/laporan.blade.php`

After the filter selects (inside the `flex-wrap` div), for `tipe` anggota and transaksi show:
```blade
<input type="date" wire:model.live="dariTanggal" class="rounded-md border-border shadow-sm text-sm">
<span class="text-text-secondary self-center">s/d</span>
<input type="date" wire:model.live="sampaiTanggal" class="rounded-md border-border shadow-sm text-sm">
```

Add `dari` and `sampai` to both export link query strings:
```blade
{{ route('laporan.export', [...existing params, 'dari' => $dariTanggal, 'sampai' => $sampaiTanggal]) }}
```

## Task 6: Tests

**File:** `tests/Feature/ReportTest.php` (extend existing)

Add tests:
- `test_pagination_link_points_to_laporan`: Livewire test, assert page-2 URL contains `/laporan?page=2`
- `test_anggota_export_filters_by_date_range`: create anggota with different `tanggal_masuk`, filter by range, assert correct rows
- `test_transaksi_peminjaman_export_filters_by_date_range`: create peminjaman with different `tanggal`, filter, assert correct rows
- `test_transaksi_pengembalian_export_filters_by_date_range`: same for pengembalian
- `test_export_route_passes_date_params`: GET `/laporan/export/anggota/excel?dari=...&sampai=...` returns OK

## Task 7: Verify

- `vendor/bin/pint` (format)
- `php artisan test` (all tests green)
- `npm run build` (frontend builds)
