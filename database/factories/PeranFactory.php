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
            'deskripsi' => 'Administrator sistem',
        ]);
    }

    public function relawan(): static
    {
        return $this->state(fn (array $attributes) => [
            'nama_peran' => 'Relawan',
            'deskripsi' => 'Relawan lapangan',
        ]);
    }
}
