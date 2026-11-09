<?php

namespace Tests\Feature\Katalog;

use App\Enums\StatusKualitas;
use App\Models\SumberData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SumberDataKatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_pencarian_nama_tidak_peka_huruf_besar_kecil(): void
    {
        SumberData::factory()->create(['nama_sumber' => 'Hotspot NASA FIRMS']);
        SumberData::factory()->create(['nama_sumber' => 'Batas Kawasan Lindung']);

        $this->get(route('katalog.sumber-data.index', ['cari' => 'hotspot']))
            ->assertOk()
            ->assertSee('Hotspot NASA FIRMS')
            ->assertDontSee('Batas Kawasan Lindung');

        $this->get(route('katalog.sumber-data.index', ['cari' => 'tidak-ada']))
            ->assertOk()
            ->assertSee('Belum ada sumber data.');
    }

    public function test_filter_status_aktif(): void
    {
        SumberData::factory()->create(['nama_sumber' => 'Dataset Aktif', 'is_aktif' => true]);
        SumberData::factory()->create(['nama_sumber' => 'Dataset Nonaktif', 'is_aktif' => false]);

        $this->get(route('katalog.sumber-data.index', ['is_aktif' => '1']))
            ->assertOk()
            ->assertSee('Dataset Aktif')
            ->assertDontSee('Dataset Nonaktif');

        $this->get(route('katalog.sumber-data.index', ['is_aktif' => '0']))
            ->assertOk()
            ->assertSee('Dataset Nonaktif')
            ->assertDontSee('Dataset Aktif');
    }

    public function test_filter_dengan_nilai_tidak_dikenal_ditolak(): void
    {
        $this->get(route('katalog.sumber-data.index', ['jenis_sumber' => 'MITRA']))
            ->assertSessionHasErrors('jenis_sumber');

        $this->get(route('katalog.sumber-data.index', ['is_aktif' => '2']))
            ->assertSessionHasErrors('is_aktif');
    }

    public function test_katalog_menampilkan_lima_belas_baris_per_halaman(): void
    {
        SumberData::factory()->count(16)->create();

        $respons = $this->get(route('katalog.sumber-data.index'));

        $respons->assertOk();
        $this->assertSame(15, $respons->viewData('sumberData')->perPage());
        $this->assertSame(16, $respons->viewData('sumberData')->total());
    }

    public function test_detail_menampilkan_riwayat_pembuatan(): void
    {
        $this->post(route('katalog.sumber-data.store'), [
            'nama_sumber' => 'Dataset Baru',
            'jenis_sumber' => SumberData::JENIS_EKSTERNAL,
            'nama_penyedia' => 'NASA',
            'nama_lisensi' => 'Public Domain',
            'format_data' => 'CSV',
            'kode_crs' => 'EPSG:4326',
            'status_kualitas' => 'VERIFIED',
        ])->assertSessionHasNoErrors();

        $baru = SumberData::query()->where('nama_sumber', 'Dataset Baru')->firstOrFail();

        $this->get(route('katalog.sumber-data.show', $baru))
            ->assertOk()
            ->assertSee('Dataset Baru')
            ->assertSee('Dibuat');
    }

    public function test_halaman_katalog_memakai_layout_global_admin(): void
    {
        $this->get(route('katalog.sumber-data.index'))
            ->assertOk()
            ->assertSee('PBI-03 · Admin')
            ->assertSee('Sistem demo')
            ->assertSee('Admin RimbaWatch')
            ->assertSee('Mode demo akademik')
            ->assertSee('aria-disabled="true"', false);
    }

    public function test_form_tambah_dan_ubah_dirender_dalam_layout(): void
    {
        $this->get(route('katalog.sumber-data.create'))
            ->assertOk()
            ->assertSee('Tambah Sumber Data')
            ->assertSee('PBI-03 · Admin')
            ->assertSee('Batal');

        $sumber = SumberData::factory()->create(['nama_sumber' => 'Dataset Ubah']);

        $this->get(route('katalog.sumber-data.edit', $sumber))
            ->assertOk()
            ->assertSee('Ubah Sumber Data')
            ->assertSee('Dataset Ubah')
            ->assertSee('Simpan');
    }

    public function test_label_metadata_belum_lengkap_memakai_nama_field_yang_mudah_dibaca(): void
    {
        $sumber = SumberData::factory()->make([
            'url_sumber' => null,
            'deskripsi_cakupan' => null,
            'interval_penyegaran_jam' => null,
        ]);

        $this->assertSame(
            ['URL sumber', 'Deskripsi cakupan', 'Interval penyegaran'],
            $sumber->labelMetadataBelumLengkap(),
        );
    }

    public function test_tombol_aktivasi_nonaktif_dan_menampilkan_field_yang_kurang(): void
    {
        $sumber = SumberData::factory()->create([
            'url_sumber' => null,
            'deskripsi_cakupan' => null,
            'interval_penyegaran_jam' => null,
            'is_aktif' => false,
        ]);

        $this->get(route('katalog.sumber-data.show', $sumber))
            ->assertOk()
            ->assertSee('Belum bisa diaktifkan')
            ->assertSee('Lengkapi metadata wajib berikut:')
            ->assertSee('Lengkapi metadata')
            ->assertSee('cursor-not-allowed', false)
            ->assertSee('aria-disabled="true"', false)
            ->assertDontSee(route('katalog.sumber-data.aktifkan', $sumber), false);
    }

    public function test_tombol_aktivasi_tersedia_bila_metadata_lengkap(): void
    {
        $sumber = SumberData::factory()->create(['is_aktif' => false]);

        $this->get(route('katalog.sumber-data.show', $sumber))
            ->assertOk()
            ->assertDontSee('Belum bisa diaktifkan')
            ->assertSee(route('katalog.sumber-data.aktifkan', $sumber), false);
    }

    public function test_katalog_menampilkan_badge_status_kualitas_berwarna(): void
    {
        SumberData::factory()->create([
            'nama_sumber' => 'Dataset Terverifikasi',
            'status_kualitas' => StatusKualitas::VERIFIED,
        ]);
        SumberData::factory()->create([
            'nama_sumber' => 'Dataset Perlu Ditinjau',
            'status_kualitas' => StatusKualitas::NEEDS_REVIEW,
        ]);

        $this->get(route('katalog.sumber-data.index'))
            ->assertOk()
            ->assertSee('Terverifikasi')
            ->assertSee('Perlu ditinjau')
            ->assertSee('bg-amber-soft', false);
    }

    public function test_empty_state_menampilkan_cta_tambah_dataset(): void
    {
        $this->get(route('katalog.sumber-data.index'))
            ->assertOk()
            ->assertSee('Belum ada sumber data.')
            ->assertSee('Tambah dataset')
            ->assertSee(route('katalog.sumber-data.create'), false)
            ->assertDontSee('Tidak ada dataset yang cocok dengan filter saat ini.');
    }

    public function test_empty_state_karena_filter_menampilkan_tombol_reset(): void
    {
        SumberData::factory()->create(['nama_sumber' => 'Dataset Lain']);

        $this->get(route('katalog.sumber-data.index', ['cari' => 'tidak-ada']))
            ->assertOk()
            ->assertSee('Belum ada sumber data.')
            ->assertSee('Tidak ada dataset yang cocok dengan filter saat ini.')
            ->assertSee('Reset filter');
    }

    public function test_form_menampilkan_error_di_field_terkait(): void
    {
        $this->from(route('katalog.sumber-data.create'))
            ->followingRedirects()
            ->post(route('katalog.sumber-data.store'), [
                'nama_sumber' => '',
                'jenis_sumber' => SumberData::JENIS_EKSTERNAL,
                'nama_penyedia' => 'NASA',
                'nama_lisensi' => 'Public Domain',
                'format_data' => 'CSV',
                'kode_crs' => '4326',
                'status_kualitas' => 'VERIFIED',
            ])
            ->assertOk()
            ->assertSee('Nama sumber wajib diisi.')
            ->assertSee('Kode CRS harus berformat EPSG diikuti 4-6 digit, contoh EPSG:4326.')
            ->assertSee('aria-invalid="true"', false)
            ->assertSee('nama_sumber-error', false)
            ->assertSee('kode_crs-error', false);
    }

    public function test_detail_meminta_konfirmasi_sebelum_menonaktifkan(): void
    {
        $sumber = SumberData::factory()->aktif()->create();

        $this->get(route('katalog.sumber-data.show', $sumber))
            ->assertOk()
            ->assertSee('Nonaktifkan dataset')
            ->assertSee('confirm(', false);
    }
}
