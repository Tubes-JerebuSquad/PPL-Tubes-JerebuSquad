<?php

namespace Database\Seeders;

use App\Models\Peran;
use Illuminate\Database\Seeder;

class PeranSeeder extends Seeder
{
    public function run(): void
    {
        $daftarPeran = [
            'Masyarakat' => 'Pelapor publik yang mengajukan dan memantau laporan',
            'Petugas Lapangan' => 'Melakukan inspeksi dan mengisi hasil inspeksi',
            'Pengawas' => 'Memverifikasi laporan dan menugaskan petugas',
            'Admin' => 'Mengelola pengguna, peran, dan data master',
        ];

        foreach ($daftarPeran as $nama => $deskripsi) {
            Peran::updateOrCreate(
                ['nama_peran' => $nama],
                ['deskripsi' => $deskripsi],
            );
        }
    }
}