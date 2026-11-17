<?php

namespace Database\Factories;

use App\Enums\StatusLaporan;
use App\Models\Laporan;
use App\Models\PeristiwaLaporan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PeristiwaLaporan>
 */
class PeristiwaLaporanFactory extends Factory
{
    protected $model = PeristiwaLaporan::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'laporan_id' => Laporan::factory(),
            'aktor_id' => null,
            'jenis_peristiwa' => 'DIAJUKAN',
            'status_lama' => null,
            'status_baru' => StatusLaporan::MENUNGGU_VERIFIKASI->value,
            'catatan_peristiwa' => 'Laporan awal dikirim oleh pelapor.',
            'terjadi_pada' => now(),
        ];
    }
}
