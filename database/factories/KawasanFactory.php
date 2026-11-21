<?php

namespace Database\Factories;

use App\Models\Kawasan;
use App\Models\Wilayah;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Kawasan>
 */
class KawasanFactory extends Factory
{
    protected $model = Kawasan::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'wilayah_id' => Wilayah::factory(),
            'nama_kawasan' => 'Hutan Lindung '.fake()->unique()->words(2, true),
            'jenis_kawasan' => 'HUTAN_LINDUNG',
            'status_kawasan' => 'AKTIF',
            'is_aktif' => true,
        ];
    }
}
