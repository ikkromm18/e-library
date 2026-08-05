<?php

namespace Database\Seeders;

use App\Models\Buku;
use App\Models\Kategori;
use App\Models\Rak;
use Illuminate\Database\Seeder;

class BookSeeder extends Seeder
{
    public function run(): void
    {
        $kategoriIds = Kategori::pluck('id')->all();
        $rakIds = Rak::pluck('id')->all();

        foreach (range(1, 40) as $iteration) {
            Buku::factory()->create([
                'kategori_id' => $kategoriIds[array_rand($kategoriIds)],
                'rak_id' => $rakIds[array_rand($rakIds)],
            ]);
        }
    }
}
