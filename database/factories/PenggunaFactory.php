<?php

namespace Database\Factories;

use App\Models\Pengguna;
use App\Models\Peran;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Pengguna>
 */
class PenggunaFactory extends Factory
{
    protected $model = Pengguna::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'peran_id' => Peran::factory(),
            'nama_lengkap' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password_hash' => 'rahasia123',
            'telepon' => fake()->numerify('08##########'),
            'status_akun' => Pengguna::STATUS_AKTIF,
        ];
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'peran_id' => Peran::factory()->admin(),
        ]);
    }

    public function nonaktif(): static
    {
        return $this->state(fn (array $attributes) => [
            'status_akun' => 'INACTIVE',
        ]);
    }
}
