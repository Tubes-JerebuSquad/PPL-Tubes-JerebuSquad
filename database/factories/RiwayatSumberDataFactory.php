<?php

namespace Database\Factories;

use App\Enums\AksiRiwayat;
use App\Models\Pengguna;
use App\Models\RiwayatSumberData;
use App\Models\SumberData;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RiwayatSumberData>
 */
class RiwayatSumberDataFactory extends Factory
{
    protected $model = RiwayatSumberData::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sumber_id' => SumberData::factory(),
            'pengguna_id' => null,
            'aksi' => AksiRiwayat::DIBUAT,
            'perubahan' => null,
            'catatan' => null,
        ];
    }

    public function aksi(AksiRiwayat $aksi): static
    {
        return $this->state(fn (array $attributes) => [
            'aksi' => $aksi,
        ]);
    }

    /**
     * @param  array<string, array{lama: mixed, baru: mixed}>  $perubahan
     */
    public function perubahan(array $perubahan): static
    {
        return $this->state(fn (array $attributes) => [
            'perubahan' => $perubahan,
        ]);
    }

    public function oleh(Pengguna $pengguna): static
    {
        return $this->state(fn (array $attributes) => [
            'pengguna_id' => $pengguna->pengguna_id,
        ]);
    }
}
