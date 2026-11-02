<?php

namespace Tests\Feature\Katalog;

use App\Models\SumberData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SumberDataValidasiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @param  array<string, mixed>  $ganti
     * @return array<string, mixed>
     */
    private function dataValid(array $ganti = []): array
    {
        return array_merge([
            'nama_sumber' => 'Hotspot NASA FIRMS',
            'jenis_sumber' => SumberData::JENIS_EKSTERNAL,
            'nama_penyedia' => 'NASA',
            'url_sumber' => 'https://firms.modaps.eosdis.nasa.gov/',
            'nama_lisensi' => 'Public Domain',
            'format_data' => 'CSV',
            'deskripsi_cakupan' => 'Titik panas global',
            'periode_mulai' => '2020-01-01',
            'periode_selesai' => '2024-12-31',
            'kode_crs' => 'EPSG:4326',
            'interval_penyegaran_jam' => 24,
            'status_kualitas' => 'VERIFIED',
        ], $ganti);
    }

    public function test_store_menyimpan_dataset_nonaktif_beserta_riwayat(): void
    {
        $respons = $this->post(route('katalog.sumber-data.store'), $this->dataValid());

        $sumber = SumberData::query()->firstOrFail();

        $respons->assertSessionHasNoErrors();
        $respons->assertRedirect(route('katalog.sumber-data.show', $sumber));

        $this->assertFalse($sumber->is_aktif);
        $this->assertSame(1, $sumber->riwayat()->count());
    }

    public function test_store_menolak_nama_sumber_kosong(): void
    {
        $respons = $this->post(route('katalog.sumber-data.store'), $this->dataValid(['nama_sumber' => '']));

        $respons->assertSessionHasErrors('nama_sumber');
        $this->assertDatabaseCount('sumber_data', 0);
    }

    public function test_store_menolak_nama_sumber_duplikat(): void
    {
        SumberData::factory()->create(['nama_sumber' => 'Hotspot NASA FIRMS']);

        $respons = $this->post(route('katalog.sumber-data.store'), $this->dataValid());

        $respons->assertSessionHasErrors('nama_sumber');
        $this->assertDatabaseCount('sumber_data', 1);
    }

    public function test_store_menolak_jenis_sumber_tidak_dikenal(): void
    {
        $respons = $this->post(route('katalog.sumber-data.store'), $this->dataValid(['jenis_sumber' => 'MITRA']));

        $respons->assertSessionHasErrors('jenis_sumber');
    }

    public function test_store_menolak_url_dengan_skema_bukan_http(): void
    {
        $respons = $this->post(
            route('katalog.sumber-data.store'),
            $this->dataValid(['url_sumber' => 'ftp://example.com/data.csv']),
        );

        $respons->assertSessionHasErrors('url_sumber');
    }

    public function test_store_menolak_url_yang_memuat_kredensial(): void
    {
        $respons = $this->post(
            route('katalog.sumber-data.store'),
            $this->dataValid(['url_sumber' => 'https://admin:rahasia@example.com/data.csv']),
        );

        $respons->assertSessionHasErrors('url_sumber');
    }

    public function test_store_menerima_url_http_biasa(): void
    {
        $respons = $this->post(
            route('katalog.sumber-data.store'),
            $this->dataValid(['url_sumber' => 'http://data.example.com/hotspot.csv']),
        );

        $respons->assertSessionHasNoErrors();
        $this->assertDatabaseHas('sumber_data', ['url_sumber' => 'http://data.example.com/hotspot.csv']);
    }

    public function test_store_menolak_kode_crs_bukan_epsg(): void
    {
        $respons = $this->post(route('katalog.sumber-data.store'), $this->dataValid(['kode_crs' => '4326']));

        $respons->assertSessionHasErrors('kode_crs');
    }

    public function test_store_menolak_periode_selesai_sebelum_periode_mulai(): void
    {
        $respons = $this->post(
            route('katalog.sumber-data.store'),
            $this->dataValid(['periode_mulai' => '2024-01-01', 'periode_selesai' => '2023-12-31']),
        );

        $respons->assertSessionHasErrors('periode_selesai');
    }

    public function test_store_menerima_periode_selesai_sama_dengan_periode_mulai(): void
    {
        $respons = $this->post(
            route('katalog.sumber-data.store'),
            $this->dataValid(['periode_mulai' => '2024-01-01', 'periode_selesai' => '2024-01-01']),
        );

        $respons->assertSessionHasNoErrors();
    }

    public function test_store_menolak_interval_penyegaran_kurang_dari_satu_jam(): void
    {
        $respons = $this->post(
            route('katalog.sumber-data.store'),
            $this->dataValid(['interval_penyegaran_jam' => 0]),
        );

        $respons->assertSessionHasErrors('interval_penyegaran_jam');
    }

    public function test_store_menolak_status_kualitas_tidak_dikenal(): void
    {
        $respons = $this->post(
            route('katalog.sumber-data.store'),
            $this->dataValid(['status_kualitas' => 'TRUSTED']),
        );

        $respons->assertSessionHasErrors('status_kualitas');
    }

    public function test_update_mengizinkan_nama_sendiri(): void
    {
        $sumber = SumberData::factory()->create(['nama_sumber' => 'Hotspot NASA FIRMS']);

        $respons = $this->put(
            route('katalog.sumber-data.update', $sumber),
            $this->dataValid(['nama_sumber' => 'Hotspot NASA FIRMS', 'nama_penyedia' => 'NASA FIRMS']),
        );

        $respons->assertSessionHasNoErrors();
        $this->assertSame('NASA FIRMS', $sumber->fresh()->nama_penyedia);
    }

    public function test_update_menolak_nama_milik_dataset_lain(): void
    {
        SumberData::factory()->create(['nama_sumber' => 'Dataset Lain']);
        $sumber = SumberData::factory()->create(['nama_sumber' => 'Hotspot NASA FIRMS']);

        $respons = $this->put(
            route('katalog.sumber-data.update', $sumber),
            $this->dataValid(['nama_sumber' => 'Dataset Lain']),
        );

        $respons->assertSessionHasErrors('nama_sumber');
        $this->assertSame('Hotspot NASA FIRMS', $sumber->fresh()->nama_sumber);
    }

    public function test_update_membandingkan_periode_selesai_dengan_periode_mulai_tersimpan(): void
    {
        $sumber = SumberData::factory()->create([
            'periode_mulai' => '2020-01-01',
            'periode_selesai' => '2024-12-31',
        ]);

        $respons = $this->put(
            route('katalog.sumber-data.update', $sumber),
            ['periode_selesai' => '2019-12-31'],
        );

        $respons->assertSessionHasErrors('periode_selesai');
        $this->assertSame('2024-12-31', $sumber->fresh()->periode_selesai->format('Y-m-d'));
    }

    public function test_update_menolak_dataset_aktif_menjadi_tidak_lengkap(): void
    {
        $sumber = SumberData::factory()->aktif()->create([
            'url_sumber' => 'https://firms.modaps.eosdis.nasa.gov/',
        ]);

        $respons = $this->put(
            route('katalog.sumber-data.update', $sumber),
            ['url_sumber' => null],
        );

        $respons->assertSessionHasErrors('metadata');
        $this->assertTrue($sumber->fresh()->is_aktif);
        $this->assertSame('https://firms.modaps.eosdis.nasa.gov/', $sumber->fresh()->url_sumber);
    }

    public function test_aktifkan_mengubah_status_dataset_lengkap(): void
    {
        $sumber = SumberData::factory()->create();

        $respons = $this->patch(route('katalog.sumber-data.aktifkan', $sumber));

        $respons->assertSessionHasNoErrors();
        $this->assertTrue($sumber->fresh()->is_aktif);
        $this->assertDatabaseHas('riwayat_sumber_data', ['aksi' => 'DIAKTIFKAN', 'sumber_id' => $sumber->sumber_id]);
    }

    public function test_aktifkan_ditolak_bila_metadata_belum_lengkap(): void
    {
        $sumber = SumberData::factory()->create(['url_sumber' => null]);

        $respons = $this->patch(route('katalog.sumber-data.aktifkan', $sumber));

        $respons->assertSessionHasErrors('metadata');
        $this->assertFalse($sumber->fresh()->is_aktif);
    }

    public function test_nonaktifkan_menyimpan_alasan(): void
    {
        $sumber = SumberData::factory()->aktif()->create();

        $respons = $this->patch(
            route('katalog.sumber-data.nonaktifkan', $sumber),
            ['catatan' => 'Lisensi dicabut'],
        );

        $respons->assertSessionHasNoErrors();
        $this->assertFalse($sumber->fresh()->is_aktif);
        $this->assertDatabaseHas('riwayat_sumber_data', [
            'aksi' => 'DINONAKTIFKAN',
            'sumber_id' => $sumber->sumber_id,
            'catatan' => 'Lisensi dicabut',
        ]);
    }

    public function test_index_menampilkan_dan_memfilter_dataset(): void
    {
        SumberData::factory()->create(['nama_sumber' => 'Dataset Internal', 'jenis_sumber' => SumberData::JENIS_INTERNAL]);
        SumberData::factory()->create(['nama_sumber' => 'Dataset Eksternal', 'jenis_sumber' => SumberData::JENIS_EKSTERNAL]);

        $this->get(route('katalog.sumber-data.index'))
            ->assertOk()
            ->assertSee('Dataset Internal')
            ->assertSee('Dataset Eksternal');

        $this->get(route('katalog.sumber-data.index', ['jenis_sumber' => SumberData::JENIS_INTERNAL]))
            ->assertOk()
            ->assertSee('Dataset Internal')
            ->assertDontSee('Dataset Eksternal');
    }

    public function test_show_menampilkan_metadata_dataset(): void
    {
        $sumber = SumberData::factory()->create([
            'nama_sumber' => 'Hotspot NASA FIRMS',
            'terakhir_diambil_pada' => '2026-11-01 08:30:00',
            'status_kebaruan' => 'FRESH',
        ]);

        $this->get(route('katalog.sumber-data.show', $sumber))
            ->assertOk()
            ->assertSee('Hotspot NASA FIRMS')
            ->assertSee('EPSG:4326')
            ->assertSee('01-11-2026 08:30')
            ->assertSee('FRESH');
    }

    public function test_show_menampilkan_data_belum_pernah_diambil(): void
    {
        $sumber = SumberData::factory()->create([
            'terakhir_diambil_pada' => null,
            'status_kebaruan' => 'UNKNOWN',
        ]);

        $this->get(route('katalog.sumber-data.show', $sumber))
            ->assertOk()
            ->assertSee('Belum pernah diambil')
            ->assertSee('Belum diketahui');
    }
}
