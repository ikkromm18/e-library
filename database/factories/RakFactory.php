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
        return [
            'kode' => $this->faker->unique()->regexify('[A-Z]-[0-9]{2}'),
            'nama' => $this->faker->words(2, true),
            'keterangan' => $this->faker->optional(0.3)->sentence(),
        ];
    }
}
