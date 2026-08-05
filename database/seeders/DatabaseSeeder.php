<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            KategoriSeeder::class,
            RakSeeder::class,
            UserSeeder::class,
            BookSeeder::class,
            AnggotaSeeder::class,
            TransaksiSeeder::class,
        ]);
    }
}
