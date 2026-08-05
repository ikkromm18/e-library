<?php

namespace App\Exports;

use App\Models\Buku;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class BukuExport implements FromCollection, WithHeadings
{
    public function __construct(private ?int $kategoriId = null, private ?int $rakId = null) {}

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
