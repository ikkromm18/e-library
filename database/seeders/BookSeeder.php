<?php

namespace Database\Seeders;

use App\Models\Buku;
use Illuminate\Database\Seeder;

class BookSeeder extends Seeder
{
    public function run(): void
    {
        foreach (range(1, 40) as $iteration) {
            Buku::factory()->create();
        }
    }
}
