<?php

namespace Database\Factories;

use App\Models\Rak;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Rak>
 */
class RakFactory extends Factory
{
    protected $model = Rak::class;

    public function definition(): array
    {
        $letter = $this->faker->randomElement(['A', 'B', 'C']);
        $number = $this->faker->numberBetween(1, 10);

        return [
            'kode' => $letter.'-'.str_pad((string) $number, 2, '0', STR_PAD_LEFT),
            'nama' => 'Rak '.$letter.'-'.$number,
            'keterangan' => $this->faker->optional(0.3)->sentence(),
        ];
    }
}
