<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class BukuTemplateExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return collect();
    }

    public function headings(): array
    {
        return [
            'isbn',
            'judul',
            'sub_judul',
            'kategori',
            'pengarang',
            'penerbit',
            'tahun',
            'bahasa',
            'rak',
            'jumlah_eksemplar',
            'deskripsi',
            'status',
        ];
    }
}
