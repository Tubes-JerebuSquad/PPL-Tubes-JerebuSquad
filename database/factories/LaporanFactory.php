<?php

namespace Database\Factories;

use App\Enums\KategoriLaporan;
use App\Enums\StatusLaporan;
use App\Models\Laporan;
use App\Models\Pengguna;
use App\Models\Wilayah;
use App\Support\LokasiSpasial;
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
            'kode_laporan' => 'LAP-'.now()->format('Ymd').'-'.strtoupper(fake()->unique()->bothify('??????')),
            'pelapor_id' => Pengguna::factory()->masyarakat(),
            'wilayah_id' => Wilayah::factory(),
            'kawasan_id' => null,
            'kategori' => KategoriLaporan::DEFORESTASI->value,
            'deskripsi' => fake()->paragraph(),
            'lokasi' => LokasiSpasial::ekspresiTitik(
                fake()->latitude(-6.0, 2.0),
                fake()->longitude(98.0, 117.0),
            ),
            'insiden_pada' => now()->subDays(2),
            'status_laporan' => StatusLaporan::MENUNGGU_VERIFIKASI->value,
            'diserahkan_pada' => now()->subDays(2),
        ];
    }

    public function dikembalikan(): static
    {
        return $this->state(fn (array $attributes) => [
            'status_laporan' => StatusLaporan::DIKEMBALIKAN->value,
        ]);
    }

    public function perluPerbaikan(): static
    {
        return $this->dikembalikan();
    }

    public function status(StatusLaporan|string $status): static
    {
        return $this->state(fn (array $attributes) => [
            'status_laporan' => $status instanceof StatusLaporan ? $status->value : $status,
        ]);
    }
}
