<?php

namespace Database\Seeders;

use App\Enums\StatusKualitas;
use App\Models\Pengguna;
use App\Models\SumberData;
use App\Services\SumberDataService;
use Illuminate\Database\Seeder;

class SumberDataSeeder extends Seeder
{
    /**
     * Mengisi contoh dataset PBI-03 (internal, eksternal aktif, dan draf nonaktif) secara idempoten.
     */
    public function run(): void
    {
        $admin = Pengguna::query()
            ->whereHas('peran', fn ($query) => $query->where('nama_peran', 'Admin'))
            ->where('status_akun', Pengguna::STATUS_AKTIF)
            ->first();

        $service = app(SumberDataService::class);

        $daftarContoh = [
            [
                'aktifkan' => true,
                'data' => [
                    'nama_sumber' => 'Hotspot NASA FIRMS VIIRS (Demo)',
                    'jenis_sumber' => SumberData::JENIS_EKSTERNAL,
                    'nama_penyedia' => 'NASA EOSDIS FIRMS',
                    'url_sumber' => 'https://firms.modaps.eosdis.nasa.gov/data/active_fire/suomi-npp-viirs-c2/csv/SUOMI_VIIRS_C2_SouthEast_Asia_24h.csv',
                    'nama_lisensi' => 'Public Domain / NASA Open Data',
                    'format_data' => 'CSV',
                    'deskripsi_cakupan' => '[Data Demo] Indikasi anomali suhu permukaan Suomi-NPP VIIRS wilayah Asia Tenggara / Indonesia.',
                    'periode_mulai' => '2024-01-01',
                    'periode_selesai' => '2026-12-31',
                    'kode_crs' => 'EPSG:4326',
                    'interval_penyegaran_jam' => 6,
                    'status_kualitas' => StatusKualitas::VERIFIED->value,
                ],
            ],
            [
                'aktifkan' => true,
                'data' => [
                    'nama_sumber' => 'Hotspot NASA FIRMS NOAA-20 (24 Jam)',
                    'jenis_sumber' => SumberData::JENIS_EKSTERNAL,
                    'nama_penyedia' => 'NASA EOSDIS FIRMS',
                    'url_sumber' => 'https://firms.modaps.eosdis.nasa.gov/data/active_fire/noaa-20-viirs-c2/csv/J1_VIIRS_C2_SouthEast_Asia_24h.csv',
                    'nama_lisensi' => 'Public Domain / NASA Open Data',
                    'format_data' => 'CSV',
                    'deskripsi_cakupan' => '[Data Demo] Feed 24 jam titik panas satelit NOAA-20 VIIRS kawasan Asia Tenggara / Indonesia.',
                    'periode_mulai' => '2025-01-01',
                    'periode_selesai' => null,
                    'kode_crs' => 'EPSG:4326',
                    'interval_penyegaran_jam' => 6,
                    'status_kualitas' => StatusKualitas::VERIFIED->value,
                ],
            ],
            [
                'aktifkan' => true,
                'data' => [
                    'nama_sumber' => 'Kawasan Hutan Indonesia (GFW Open Data)',
                    'jenis_sumber' => SumberData::JENIS_EKSTERNAL,
                    'nama_penyedia' => 'Global Forest Watch / KLHK',
                    'url_sumber' => 'https://data.globalforestwatch.org/',
                    'nama_lisensi' => 'CC BY 4.0',
                    'format_data' => 'GEOJSON',
                    'deskripsi_cakupan' => '[Data Demo] Referensi batas kawasan hutan legal Indonesia dari portal open data GFW.',
                    'periode_mulai' => '2024-01-01',
                    'periode_selesai' => '2026-12-31',
                    'kode_crs' => 'EPSG:4326',
                    'interval_penyegaran_jam' => 720,
                    'status_kualitas' => StatusKualitas::VERIFIED->value,
                ],
            ],
            [
                'aktifkan' => true,
                'data' => [
                    'nama_sumber' => 'Batas Kawasan Hutan Lindung (Demo)',
                    'jenis_sumber' => SumberData::JENIS_INTERNAL,
                    'nama_penyedia' => 'Tim GIS RimbaWatch',
                    'url_sumber' => null,
                    'nama_lisensi' => 'CC BY 4.0',
                    'format_data' => 'GEOJSON',
                    'deskripsi_cakupan' => '[Data Demo] Referensi spasial batas kawasan hasil digitasi internal tim GIS RimbaWatch.',
                    'periode_mulai' => '2025-01-01',
                    'periode_selesai' => '2026-12-31',
                    'kode_crs' => 'EPSG:4326',
                    'interval_penyegaran_jam' => 48,
                    'status_kualitas' => StatusKualitas::VERIFIED->value,
                ],
            ],
            [
                'aktifkan' => false,
                'data' => [
                    'nama_sumber' => 'Tutupan Lahan Regional (Draf Demo)',
                    'jenis_sumber' => SumberData::JENIS_EKSTERNAL,
                    'nama_penyedia' => 'Lembaga Pemetaan Regional',
                    'url_sumber' => null,
                    'nama_lisensi' => 'Terbuka Non-Komersial',
                    'format_data' => 'GEOJSON',
                    'deskripsi_cakupan' => null,
                    'periode_mulai' => null,
                    'periode_selesai' => null,
                    'kode_crs' => 'EPSG:4326',
                    'interval_penyegaran_jam' => null,
                    'status_kualitas' => StatusKualitas::NEEDS_REVIEW->value,
                ],
            ],
        ];

        foreach ($daftarContoh as $item) {
            if (SumberData::query()->where('nama_sumber', $item['data']['nama_sumber'])->exists()) {
                continue;
            }

            $sumber = $service->simpan($item['data'], $admin);

            if ($item['aktifkan']) {
                $service->aktifkan($sumber, $admin);
            }
        }

        $this->command?->info('Seeder contoh sumber data PBI-03 berhasil dijalankan.');
    }
}
