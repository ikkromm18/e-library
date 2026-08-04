<?php

namespace Database\Factories;

use App\Models\Anggota;
use App\Models\Peminjaman;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Peminjaman>
 */
class PeminjamanFactory extends Factory
{
    protected $model = Peminjaman::class;

    public function definition(): array
    {
        $prefix = 'PJM-'.now()->format('Ymd');
        $count = Peminjaman::whereDate('created_at', today())->count() + 1;
        $no = $prefix.'-'.$count;

        return [
            'no_transaksi' => $no,
            'tanggal' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'tanggal_jatuh_tempo' => $this->faker->dateTimeBetween('+3 days', '+14 days'),
            'petugas_id' => User::factory()->petugas(),
            'anggota_id' => Anggota::factory(),
            'status' => 'dipinjam',
        ];
    }

    public function selesai(): static
    {
        return $this->state(fn (array $attrs) => [
            'status' => 'selesai',
        ]);
    }
}
