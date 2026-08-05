<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class AnggotaTemplateExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return collect();
    }

    public function headings(): array
    {
        return [
            'nis',
            'nama',
            'jenis_kelamin',
            'kelas',
            'alamat',
            'no_hp',
            'tanggal_masuk',
            'status',
        ];
    }
}
