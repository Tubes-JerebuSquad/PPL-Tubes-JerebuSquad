<?php

namespace Tests\Feature\Katalog;

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
            ->assertSee('<td>Dibuat</td>', false);
    }
}
