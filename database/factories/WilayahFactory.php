<?php

namespace Database\Factories;

use App\Models\Wilayah;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Wilayah>
 */
class WilayahFactory extends Factory
{
    protected $model = Wilayah::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'kode_wilayah' => fake()->unique()->numerify('WIL-####'),
            'nama_wilayah' => 'Kabupaten '.fake()->city(),
            'tingkat_wilayah' => 'KABUPATEN',
            'is_aktif' => true,
        ];
    }
}
