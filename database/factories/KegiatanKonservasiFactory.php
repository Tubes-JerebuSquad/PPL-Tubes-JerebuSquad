<?php

namespace Database\Factories;

use App\Enums\StatusKegiatan;
use App\Models\Kawasan;
use App\Models\KegiatanKonservasi;
use App\Models\Pengguna;
use Illuminate\Database\Eloquent\Factories\Factory;

class KegiatanKonservasiFactory extends Factory
{
    protected $model = KegiatanKonservasi::class;

    public function definition(): array
    {
        return [
            'kawasan_id' => Kawasan::factory(),
            'direncanakan_oleh' => Pengguna::factory(),
            'nama_kegiatan' => 'Penanaman '.fake()->unique()->words(2, true),
            'tujuan' => fake()->sentence(),
            'jenis_kegiatan' => 'PENANAMAN',
            'tanggal_mulai' => now()->addDays(7)->toDateString(),
            'tanggal_selesai' => now()->addDays(14)->toDateString(),
            'deskripsi_target' => fake()->sentence(),
            'penanggung_jawab' => fake()->name(),
            'deskripsi_sumber_daya' => fake()->sentence(),
            'kuota_peserta' => 30,
            'status_kegiatan' => StatusKegiatan::DRAF->value,
        ];
    }

    public function diterbitkan(): static
    {
        return $this->state(fn (array $attributes) => [
            'status_kegiatan' => StatusKegiatan::DITERBITKAN->value,
        ]);
    }

    public function dibatalkan(): static
    {
        return $this->state(fn (array $attributes) => [
            'status_kegiatan' => StatusKegiatan::DIBATALKAN->value,
        ]);
    }
}