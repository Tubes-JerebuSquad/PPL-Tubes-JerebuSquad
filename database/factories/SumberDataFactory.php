<?php

namespace Database\Factories;

use App\Enums\StatusKualitas;
use App\Models\Pengguna;
use App\Models\SumberData;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SumberData>
 */
class SumberDataFactory extends Factory
{
    protected $model = SumberData::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $periodeMulai = fake()->dateTimeBetween('-6 years', '-18 months');

        return [
            'nama_sumber' => fake()->unique()->sentence(3),
            'jenis_sumber' => fake()->randomElement([SumberData::JENIS_INTERNAL, SumberData::JENIS_EKSTERNAL]),
            'nama_penyedia' => fake()->company(),
            'url_sumber' => fake()->url(),
            'nama_lisensi' => fake()->randomElement([
                'CC BY 4.0',
                'CC BY-SA 4.0',
                'Lisensi Terbuka Pemerintah',
                'Internal RimbaWatch',
            ]),
            'format_data' => fake()->randomElement([
                'GEOJSON',
                'CSV',
                'SHP',
                'GEOPACKAGE',
                'POSTGRESQL',
                'API',
                'LAINNYA',
            ]),
            'deskripsi_cakupan' => fake()->paragraph(),
            'periode_mulai' => $periodeMulai,
            'periode_selesai' => fake()->dateTimeBetween($periodeMulai, 'now'),
            'kode_crs' => 'EPSG:4326',
            'interval_penyegaran_jam' => fake()->randomElement([6, 12, 24, 168, 720]),
            'status_kualitas' => fake()->randomElement(StatusKualitas::cases()),
            'is_aktif' => false,
            'dibuat_oleh' => null,
        ];
    }

    public function aktif(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_aktif' => true,
        ]);
    }

    public function nonaktif(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_aktif' => false,
        ]);
    }

    public function dibuatOleh(Pengguna $pengguna): static
    {
        return $this->state(fn (array $attributes) => [
            'dibuat_oleh' => $pengguna->pengguna_id,
        ]);
    }
}
