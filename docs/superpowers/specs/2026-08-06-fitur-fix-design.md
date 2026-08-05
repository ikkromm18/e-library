# Design: Fix Upload Gambar, Pagination, Import Buku, Landing Login, & Optimasi Query

Tanggal: 2026-08-06

## Konteks
E-Library dipakai SMP dengan ribuan murid. Permintaan:
1. Upload file gambar (cover buku, logo setting) belum bisa jalan & tak tampil.
2. Beberapa tabel belum punya kolom No & pagination; saat klik pagination view kembali ke atas.
3. Import data Excel untuk Buku (Anggota sudah ada) + template.
4. Performa: hindari query N+1, panggilan DB optimal utk ribut data.
5. Landing langsung ke halaman login, bukan halaman Laravel welcome.

## Temuan akar masalah (dari eksplorasi)
- `bookragte.php`/`buku.php` TIDAK `use WithFileUploads;` → upload cover mati.
- Symlink `public/storage` hilang → `asset('storage/...')` untuk cover & logo tak tampil.
- `BukuExport.collection()` panggil `$b->stokTersedia()` per baris (N+1).
- `TransaksiExport` default panggil `$peminjaman->details()->count()` per baris (N+1).
- Tabel master kategori/rak/pengguna pakai `->get()` tanpa pagination.

## Keputusan
- Buku cover saja (Anggota di luar scope) untuk upload gambar.
- Kolom No + pagination di semua daftar: buku, anggota, kategori, rak, pengguna, laporan.
- Selector per-halaman 10/25/50/100 default 10 (Livewire).
- Keep scroll posisi via attribut `preserveScroll` Livewire.
- Navbar & halaman auth menampilkan logo dari Setting, fallback `x-application-logo`.
- Import Buku kolom inti; kategori & rak dicocokkan by nama/kode; kode buku auto.
- Tambah index DB migration.

## Desain per bagian
Uraian detail di plan doc: `docs/superpowers/plans/2026-08-06-fitur-fix.md`.