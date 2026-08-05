<?php

namespace App\Exports;

use App\Models\Anggota;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class AnggotaExport implements FromCollection, WithHeadings
{
    public function __construct(private ?string $status = null, private ?string $dari = null, private ?string $sampai = null) {}

    public function collection(): Collection
    {
        return Anggota::query()
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->when($this->dari && $this->sampai, fn ($q) => $q->whereBetween('tanggal_masuk', [$this->dari, $this->sampai]))
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
