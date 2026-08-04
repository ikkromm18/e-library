<?php

namespace Database\Factories;

use App\Models\Anggota;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Anggota>
 */
class AnggotaFactory extends Factory
{
    protected $model = Anggota::class;

    public function definition(): array
    {
        $nis = str_pad((string) $this->faker->unique()->randomNumber(6), 10, '0', STR_PAD_LEFT);

        return [
            'nis' => $nis,
            'nama' => $this->faker->name(),
            'jenis_kelamin' => $this->faker->randomElement(['L', 'P']),
            'kelas' => $this->faker->randomElement(['7A', '7B', '7C', '8A', '8B', '8C', '9A', '9B', '9C']),
            'alamat' => $this->faker->optional(0.8)->address(),
            'no_hp' => $this->faker->optional(0.6)->numerify('08##########'),
            'tanggal_masuk' => $this->faker->dateTimeBetween('-3 years', 'now'),
            'status' => 'aktif',
        ];
    }
}
