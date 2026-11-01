<?php

namespace Database\Factories;

use App\Models\Peran;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Peran>
 */
class PeranFactory extends Factory
{
    protected $model = Peran::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama_peran' => 'Peran '.fake()->unique()->word(),
            'deskripsi' => fake()->sentence(),
        ];
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'nama_peran' => 'Admin',
            'deskripsi' => 'Pengelola data dan akses sistem',
        ]);
    }

    public function pengawas(): static
    {
        return $this->state(fn (array $attributes) => [
            'nama_peran' => 'Pengawas',
            'deskripsi' => 'Pemantau data dan laporan',
        ]);
    }

    public function petugasLapangan(): static
    {
        return $this->state(fn (array $attributes) => [
            'nama_peran' => 'Petugas Lapangan',
            'deskripsi' => 'Pencatat hasil pengamatan lapangan',
        ]);
    }

    public function masyarakat(): static
    {
        return $this->state(fn (array $attributes) => [
            'nama_peran' => 'Masyarakat',
            'deskripsi' => 'Pengguna umum yang mengirim laporan',
        ]);
    }
}
