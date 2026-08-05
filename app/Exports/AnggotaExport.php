<?php

namespace App\Exports;

use App\Models\Anggota;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class AnggotaExport implements FromCollection, WithHeadings
{
    public function __construct(private ?string $status = null) {}

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
