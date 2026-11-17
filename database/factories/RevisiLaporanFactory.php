<?php

namespace Database\Factories;

use App\Enums\KategoriLaporan;
use App\Models\Laporan;
use App\Models\RevisiLaporan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RevisiLaporan>
 */
class RevisiLaporanFactory extends Factory
{
    protected $model = RevisiLaporan::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'laporan_id' => Laporan::factory(),
            'nomor_revisi' => 1,
            'kategori' => KategoriLaporan::DEFORESTASI->value,
            'deskripsi' => fake()->paragraph(),
            'latitude' => fake()->latitude(-6.0, 2.0),
            'longitude' => fake()->longitude(98.0, 117.0),
            'waktu_kejadian' => now()->subDays(2),
            'catatan_revisi' => null,
        ];
    }
}
