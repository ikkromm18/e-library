<?php

namespace App\Imports;

use App\Models\Anggota;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Validators\Failure;

class AnggotaImport implements SkipsOnFailure, ToModel, WithHeadingRow
{
    use Importable;

    public int $imported = 0;

    public int $skipped = 0;

    public array $errors = [];

    public function model(array $row): ?Anggota
    {
        $nis = trim($row['nis'] ?? '');

        if (Anggota::where('nis', $nis)->exists()) {
            $this->skipped++;

            return null;
        }

        $this->imported++;

        return new Anggota([
            'nis' => $nis,
            'nama' => trim($row['nama'] ?? ''),
            'jenis_kelamin' => strtoupper(trim($row['jenis_kelamin'] ?? 'L')),
            'kelas' => trim($row['kelas'] ?? ''),
            'alamat' => trim($row['alamat'] ?? ''),
            'no_hp' => trim($row['no_hp'] ?? ''),
            'tanggal_masuk' => $row['tanggal_masuk'] ?? now(),
            'status' => strtolower(trim($row['status'] ?? 'aktif')),
        ]);
    }

    public function rules(): array
    {
        return [
            'nis' => 'required|string',
            'nama' => 'required|string',
            'jenis_kelamin' => 'required|in:L,P',
            'tanggal_masuk' => 'required|date',
            'status' => 'required|in:aktif,lulus,pindah,nonaktif',
        ];
    }

    public function onFailure(Failure ...$failures): void
    {
        foreach ($failures as $failure) {
            $this->errors[] = $failure->errors()[0] ?? 'Baris '.$failure->row().': error validasi.';
        }
    }
}
