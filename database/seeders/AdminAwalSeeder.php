<?php

namespace Database\Seeders;

use App\Models\Pengguna;
use App\Models\Peran;
use Illuminate\Database\Seeder;

class AdminAwalSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('ADMIN_EMAIL');
        $password = env('ADMIN_PASSWORD');

        if (! $email || ! $password) {
            $this->command?->warn('ADMIN_EMAIL / ADMIN_PASSWORD belum diisi di .env, akun Admin awal dilewati.');

            return;
        }

        $peranAdmin = Peran::where('nama_peran', 'Admin')->firstOrFail();

        Pengguna::updateOrCreate(
            ['email' => $email],
            [
                'peran_id' => $peranAdmin->peran_id,
                'nama_lengkap' => 'Administrator',
                'password_hash' => $password,
                'status_akun' => Pengguna::STATUS_AKTIF,
            ],
        );
    }
}