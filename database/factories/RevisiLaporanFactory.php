<?php

namespace Database\Factories;

use App\Enums\KategoriLaporan;
use App\Models\Laporan;
use App\Models\Pengguna;
use App\Models\RevisiLaporan;
use App\Support\LokasiSpasial;
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
            'diedit_oleh' => function (array $attributes) {
                if (isset($attributes['laporan_id'])) {
                    return Laporan::query()
                        ->where('laporan_id', $attributes['laporan_id'])
                        ->value('pelapor_id') ?? Pengguna::factory()->masyarakat();
                }

                return Pengguna::factory()->masyarakat();
            },
            'kategori' => KategoriLaporan::DEFORESTASI->value,
            'deskripsi' => fake()->paragraph(),
            'lokasi' => LokasiSpasial::ekspresiTitik(
                fake()->latitude(-6.0, 2.0),
                fake()->longitude(98.0, 117.0),
            ),
            'insiden_pada' => now()->subDays(2),
            'alasan_revisi' => null,
        ];
    }
}
