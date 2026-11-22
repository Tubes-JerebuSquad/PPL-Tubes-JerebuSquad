<?php

namespace Database\Seeders;

use App\Enums\StatusPublikasi;
use App\Models\MateriEdukasi;
use App\Models\Pengguna;
use App\Services\MateriEdukasiService;
use Illuminate\Database\Seeder;

class MateriEdukasiSeeder extends Seeder
{
    /**
     * Mengisi contoh materi edukasi PBI-16 (terbit, draf, dan arsip) secara idempoten.
     */
    public function run(): void
    {
        $pengawas = Pengguna::query()
            ->whereHas('peran', fn ($query) => $query->where('nama_peran', 'Pengawas'))
            ->where('status_akun', Pengguna::STATUS_AKTIF)
            ->first();

        if (! $pengawas) {
            $this->command?->warn('Akun Pengawas aktif belum tersedia, MateriEdukasiSeeder dilewati.');

            return;
        }

        $service = app(MateriEdukasiService::class);

        $daftarContoh = [
            [
                'status' => StatusPublikasi::PUBLISHED,
                'data' => [
                    'judul' => 'Panduan Pencegahan Kebakaran Lahan Gambut (Demo)',
                    'kategori' => 'Pencegahan Kebakaran',
                    'konten' => "Lahan gambut menyimpan cadangan karbon dalam jumlah besar dan rentan terbakar saat muka air tanah menurun drastis di musim kemarau.\n\nLangkah pencegahan utama meliputi pemantauan sekat kanal secara berkala, menghindari pembukaan lahan dengan cara membakar, serta melaporkan indikasi asap atau titik panas sedini mungkin melalui fitur pengaduan RimbaWatch.\n\nPerlu diingat bahwa kemunculan titik panas dari satelit merupakan indikasi awal suhu permukaan yang tetap memerlukan pengecekan lapangan.",
                    'referensi_sumber' => 'Badan Restorasi Gambut dan Mangrove (BRGM) · Pedoman Pembasahan Gambut & Pencegahan Karhutla (Data Demo Akademik)',
                ],
            ],
            [
                'status' => StatusPublikasi::PUBLISHED,
                'data' => [
                    'judul' => 'Mengenal Tahapan Rehabilitasi Kawasan Hutan Kritis (Demo)',
                    'kategori' => 'Rehabilitasi Lahan',
                    'konten' => "Rehabilitasi hutan bertujuan memulihkan fungsi ekologis kawasan yang mengalami degradasi melalui penanaman jenis pohon lokal yang adaptif.\n\nTahapan utama mencakup pemetaan kondisi tapak, penyiapan bibit endemik, penanaman partisipatif bersama masyarakat sekitar, serta pemantauan tingkat kelangsungan hidup tanaman secara berkala agar pemulihan berjalan berkelanjutan.",
                    'referensi_sumber' => 'Kementerian Lingkungan Hidup dan Kehutanan (KLHK) · Modul Rehabilitasi Hutan dan Lahan (Data Demo Akademik)',
                ],
            ],
            [
                'status' => StatusPublikasi::DRAFT,
                'data' => [
                    'judul' => 'Draf Panduan Identifikasi Jalur Patroli Partisipatif (Draf Demo)',
                    'kategori' => 'Pelestarian Hutan',
                    'konten' => 'Materi draf mengenai koordinasi patroli warga sekitar kawasan lindung. Sedang ditinjau oleh Pengawas sebelum diterbitkan ke halaman publik.',
                    'referensi_sumber' => 'Tim Konservasi RimbaWatch · Draf Internal',
                ],
            ],
            [
                'status' => StatusPublikasi::ARCHIVED,
                'data' => [
                    'judul' => 'Arsip Sosialisasi Musim Kemarau Tahun Lalu (Arsip Demo)',
                    'kategori' => 'Edukasi Masyarakat',
                    'konten' => 'Materi sosialisasi periode sebelumnya yang sudah diarsipkan sehingga tidak tampil di daftar publik.',
                    'referensi_sumber' => 'Arsip Dokumentasi RimbaWatch',
                ],
            ],
        ];

        foreach ($daftarContoh as $item) {
            if (MateriEdukasi::query()->where('judul', $item['data']['judul'])->exists()) {
                continue;
            }

            $materi = $service->simpan($item['data'], $pengawas);

            if ($item['status'] === StatusPublikasi::PUBLISHED) {
                $service->terbitkan($materi, $pengawas);
            } elseif ($item['status'] === StatusPublikasi::ARCHIVED) {
                $service->arsipkan($materi, $pengawas);
            }
        }

        $this->command?->info('Seeder contoh materi edukasi PBI-16 berhasil dijalankan.');
    }
}
