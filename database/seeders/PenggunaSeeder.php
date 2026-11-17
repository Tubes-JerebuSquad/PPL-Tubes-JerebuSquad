<?php

namespace Database\Seeders;

use App\Models\Pengguna;
use App\Models\Peran;
use Illuminate\Database\Seeder;

class PenggunaSeeder extends Seeder
{
    /**
     * Jalankan seeder akun pengguna untuk semua peran.
     */
    public function run(): void
    {
        // Pastikan tabel peran sudah terisi terlebih dahulu
        if (Peran::count() === 0) {
            $this->call(PeranSeeder::class);
        }

        $adminEmail = env('ADMIN_EMAIL', 'admin@rimbawatch.org');
        $adminPassword = env('ADMIN_PASSWORD', 'admin123');

        $daftarAkun = [
            [
                'peran' => 'Admin',
                'nama_lengkap' => 'Administrator RimbaWatch',
                'email' => $adminEmail,
                'telepon' => '081200000001',
                'password' => $adminPassword,
                'status_akun' => Pengguna::STATUS_AKTIF,
                'deskripsi' => 'Akun Administrator utama',
            ],
            [
                'peran' => 'Pengawas',
                'nama_lengkap' => 'Budi Santoso',
                'email' => 'pengawas@rimbawatch.org',
                'telepon' => '081200000002',
                'password' => 'password123',
                'status_akun' => Pengguna::STATUS_AKTIF,
                'deskripsi' => 'Akun Pengawas verifikasi laporan',
            ],
            [
                'peran' => 'Petugas Lapangan',
                'nama_lengkap' => 'Joko Pratama',
                'email' => 'petugas@rimbawatch.org',
                'telepon' => '081200000003',
                'password' => 'password123',
                'status_akun' => Pengguna::STATUS_AKTIF,
                'deskripsi' => 'Akun Petugas inspeksi lapangan',
            ],
            [
                'peran' => 'Masyarakat',
                'nama_lengkap' => 'Siti Rahma',
                'email' => 'masyarakat@rimbawatch.org',
                'telepon' => '081200000004',
                'password' => 'password123',
                'status_akun' => Pengguna::STATUS_AKTIF,
                'deskripsi' => 'Akun Masyarakat / pelapor',
            ],
            [
                'peran' => 'Masyarakat',
                'nama_lengkap' => 'Dewi Warga Rimba',
                'email' => 'warga@rimbawatch.org',
                'telepon' => '081200000005',
                'password' => 'password123',
                'status_akun' => Pengguna::STATUS_AKTIF,
                'deskripsi' => 'Akun Warga umum',
            ],
            [
                'peran' => 'Masyarakat',
                'nama_lengkap' => 'Akun Nonaktif Test',
                'email' => 'nonaktif@rimbawatch.org',
                'telepon' => '081200000099',
                'password' => 'password123',
                'status_akun' => Pengguna::STATUS_NONAKTIF,
                'dinonaktifkan_pada' => now(),
                'deskripsi' => 'Akun untuk pengujian status nonaktif',
            ],
        ];

        foreach ($daftarAkun as $item) {
            $peran = Peran::where('nama_peran', $item['peran'])->first();

            if (! $peran) {
                $this->command?->warn("Peran [{$item['peran']}] belum ada di basis data, akun [{$item['email']}] dilewati.");
                continue;
            }

            Pengguna::updateOrCreate(
                ['email' => $item['email']],
                [
                    'peran_id' => $peran->peran_id,
                    'nama_lengkap' => $item['nama_lengkap'],
                    'telepon' => $item['telepon'],
                    'password_hash' => $item['password'],
                    'status_akun' => $item['status_akun'],
                    'dinonaktifkan_pada' => $item['dinonaktifkan_pada'] ?? null,
                ]
            );
        }

        $this->command?->info('Seeder pengguna untuk semua peran berhasil dijalankan.');
    }
}
