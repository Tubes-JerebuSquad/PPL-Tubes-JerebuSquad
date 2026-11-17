<?php

namespace Database\Factories;

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
            'pengguna_id' => null,
            'jenis_peristiwa' => 'DIAJUKAN',
            'status_lama' => null,
            'status_baru' => 'menunggu_verifikasi',
            'deskripsi' => 'Laporan awal dikirim oleh pelapor.',
            'catatan_publik' => null,
            'waktu_peristiwa' => now(),
        ];
    }
}
