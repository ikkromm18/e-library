<?php

namespace App\Imports;

use App\Models\Buku;
use App\Models\Kategori;
use App\Models\Rak;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Validators\Failure;

class BukuImport implements SkipsOnFailure, ToModel, WithHeadingRow
{
    use Importable;

    public int $imported = 0;

    public int $skipped = 0;

    public array $errors = [];

    public function model(array $row): ?Buku
    {
        $isbn = trim($row['isbn'] ?? '');

        if ($isbn && Buku::where('isbn', $isbn)->exists()) {
            $this->skipped++;

            return null;
        }

        $kategoriNama = trim($row['kategori'] ?? '');
        $kategori = $kategoriNama ? Kategori::where('nama', $kategoriNama)->first() : null;

        $rakKode = trim($row['rak'] ?? '');
        $rak = $rakKode ? Rak::where('kode', $rakKode)->first() : null;

        $this->imported++;

        return new Buku([
            'kode' => Buku::buatKode(),
            'isbn' => $isbn ?: null,
            'judul' => trim($row['judul'] ?? ''),
            'sub_judul' => trim($row['sub_judul'] ?? ''),
            'kategori_id' => $kategori?->id,
            'pengarang' => trim($row['pengarang'] ?? ''),
            'penerbit' => trim($row['penerbit'] ?? ''),
            'tahun' => $row['tahun'] ?? null,
            'bahasa' => trim($row['bahasa'] ?? ''),
            'rak_id' => $rak?->id,
            'jumlah_eksemplar' => (int) ($row['jumlah_eksemplar'] ?? 1),
            'deskripsi' => trim($row['deskripsi'] ?? ''),
            'status' => strtolower(trim($row['status'] ?? 'aktif')),
        ]);
    }

    public function rules(): array
    {
        return [
            'isbn' => 'nullable|string',
            'judul' => 'required|string',
            'kategori' => 'required|string',
            'rak' => 'required|string',
            'jumlah_eksemplar' => 'required|integer|min:1',
            'status' => 'required|in:aktif,tidak',
        ];
    }

    public function onFailure(Failure ...$failures): void
    {
        foreach ($failures as $failure) {
            $this->errors[] = $failure->errors()[0] ?? 'Baris '.$failure->row().': error validasi.';
        }
    }
}
