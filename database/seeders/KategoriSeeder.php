<?php

namespace Database\Seeders;

use App\Models\Kategori;
use Illuminate\Database\Seeder;

class KategoriSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Pelajaran', 'Novel', 'Agama', 'Ensiklopedia', 'Komik Edukasi', 'Majalah'] as $nama) {
            Kategori::create(['nama' => $nama]);
        }
    }
}
