<?php

namespace Database\Factories;

use App\Models\Buku;
use App\Models\Kategori;
use App\Models\Rak;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Buku>
 */
class BukuFactory extends Factory
{
    protected $model = Buku::class;

    public function definition(): array
    {
        $n = Buku::count() + 1;
        while (Buku::where('kode', $kode = 'BUK-'.str_pad((string) $n, 4, '0', STR_PAD_LEFT))->exists()) {
            $n++;
        }

        return [
            'kode' => $kode,
            'isbn' => $this->faker->optional(0.8)->isbn13(),
            'judul' => $this->faker->sentence(4),
            'sub_judul' => $this->faker->optional(0.3)->sentence(3),
            'kategori_id' => Kategori::factory(),
            'pengarang' => $this->faker->name(),
            'penerbit' => $this->faker->company(),
            'tahun' => $this->faker->numberBetween(2000, 2026),
            'bahasa' => $this->faker->randomElement(['Indonesia', 'Inggris']),
            'rak_id' => Rak::factory(),
            'jumlah_eksemplar' => $this->faker->numberBetween(1, 5),
            'deskripsi' => $this->faker->optional(0.5)->paragraph(),
            'cover' => null,
            'status' => $this->faker->randomElement(['aktif', 'aktif', 'aktif', 'tidak']),
        ];
    }
}
