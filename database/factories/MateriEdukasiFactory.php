<?php

namespace Database\Factories;

use App\Enums\StatusPublikasi;
use App\Models\MateriEdukasi;
use App\Models\Pengguna;
use App\Models\Peran;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MateriEdukasi>
 */
class MateriEdukasiFactory extends Factory
{
    protected $model = MateriEdukasi::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'dibuat_oleh' => fn () => Pengguna::factory()->create([
                'peran_id' => Peran::firstOrCreate(
                    ['nama_peran' => 'Pengawas'],
                    ['deskripsi' => 'Memverifikasi laporan dan menugaskan petugas'],
                )->peran_id,
            ])->pengguna_id,
            'diperbarui_oleh' => null,
            'judul' => fake()->sentence(6),
            'konten' => fake()->paragraphs(3, true),
            'kategori' => fake()->randomElement([
                'Pelestarian Hutan',
                'Pencegahan Kebakaran',
                'Rehabilitasi Lahan',
                'Edukasi Masyarakat',
            ]),
            'referensi_sumber' => 'Kementerian Lingkungan Hidup dan Kehutanan (KLHK) · Panduan Konservasi',
            'status_publikasi' => StatusPublikasi::DRAFT->value,
            'dipublikasikan_pada' => null,
            'diarsipkan_pada' => null,
        ];
    }

    public function draf(): static
    {
        return $this->state(fn () => [
            'status_publikasi' => StatusPublikasi::DRAFT->value,
            'dipublikasikan_pada' => null,
            'diarsipkan_pada' => null,
        ]);
    }

    public function terbit(): static
    {
        return $this->state(fn () => [
            'status_publikasi' => StatusPublikasi::PUBLISHED->value,
            'dipublikasikan_pada' => now(),
            'diarsipkan_pada' => null,
        ]);
    }

    public function arsip(): static
    {
        return $this->state(fn () => [
            'status_publikasi' => StatusPublikasi::ARCHIVED->value,
            'diarsipkan_pada' => now(),
        ]);
    }
}
