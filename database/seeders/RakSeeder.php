<?php

namespace Database\Seeders;

use App\Models\Rak;
use Illuminate\Database\Seeder;

class RakSeeder extends Seeder
{
    public function run(): void
    {
        $kodeRak = [];
        foreach (['A', 'B'] as $letter) {
            for ($i = 1; $i <= 5; $i++) {
                $kodeRak[] = sprintf('%s-%02d', $letter, $i);
            }
        }

        foreach ($kodeRak as $kode) {
            Rak::firstOrCreate(['kode' => $kode], [
                'nama' => "Rak $kode",
                'keterangan' => "Rak untuk koleksi $kode",
            ]);
        }
    }
}
