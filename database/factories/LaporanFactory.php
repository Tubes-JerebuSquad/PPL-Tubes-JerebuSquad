<?php

namespace Database\Factories;

use App\Enums\KategoriLaporan;
use App\Enums\StatusLaporan;
use App\Models\Laporan;
use App\Models\Pengguna;
use App\Models\Wilayah;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Laporan>
 */
class LaporanFactory extends Factory
{
    protected $model = Laporan::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nomor_laporan' => 'RW-'.now()->format('Ymd').'-'.fake()->unique()->numerify('####'),
            'pelapor_id' => Pengguna::factory()->masyarakat(),
            'wilayah_id' => Wilayah::factory(),
            'kawasan_id' => null,
            'kategori' => KategoriLaporan::DEFORESTASI->value,
            'deskripsi' => fake()->paragraph(),
            'latitude' => fake()->latitude(-6.0, 2.0),
            'longitude' => fake()->longitude(98.0, 117.0),
            'waktu_kejadian' => now()->subDays(2),
            'status' => StatusLaporan::MENUNGGU_VERIFIKASI->value,
        ];
    }

    public function perluPerbaikan(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => StatusLaporan::PERLU_PERBAIKAN->value,
        ]);
    }

    public function status(StatusLaporan|string $status): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => $status instanceof StatusLaporan ? $status->value : $status,
        ]);
    }
}
