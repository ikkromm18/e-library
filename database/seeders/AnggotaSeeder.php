<?php

namespace Database\Seeders;

use App\Models\Anggota;
use Illuminate\Database\Seeder;

class AnggotaSeeder extends Seeder
{
    public function run(): void
    {
        $anggota = Anggota::factory()->count(200)->create();
        $anggota->take(30)->each(fn ($a) => $a->update(['status' => 'nonaktif']));
        $anggota->take(20)->skip(30)->each(fn ($a) => $a->update(['status' => 'lulus']));
        $anggota->take(10)->skip(50)->each(fn ($a) => $a->update(['status' => 'pindah']));
    }
}
