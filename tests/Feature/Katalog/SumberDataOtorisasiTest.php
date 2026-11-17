<?php

namespace Tests\Feature\Katalog;

use App\Models\Pengguna;
use App\Models\SumberData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SumberDataOtorisasiTest extends TestCase
{
    use RefreshDatabase;

    public function test_tamu_diarahkan_ke_login_untuk_semua_rute_katalog(): void
    {
        $sumber = SumberData::factory()->create();

        $this->get(route('katalog.sumber-data.index'))->assertRedirect(route('login'));
        $this->get(route('katalog.sumber-data.create'))->assertRedirect(route('login'));
        $this->post(route('katalog.sumber-data.store'), [])->assertRedirect(route('login'));
        $this->get(route('katalog.sumber-data.show', $sumber))->assertRedirect(route('login'));
        $this->get(route('katalog.sumber-data.edit', $sumber))->assertRedirect(route('login'));
        $this->put(route('katalog.sumber-data.update', $sumber), [])->assertRedirect(route('login'));
        $this->patch(route('katalog.sumber-data.aktifkan', $sumber))->assertRedirect(route('login'));
        $this->patch(route('katalog.sumber-data.nonaktifkan', $sumber))->assertRedirect(route('login'));
    }

    public function test_pengguna_non_admin_ditolak_mengakses_katalog(): void
    {
        $sumber = SumberData::factory()->create();

        $nonAdmin = [
            Pengguna::factory()->masyarakat()->create(),
            Pengguna::factory()->pengawas()->create(),
            Pengguna::factory()->petugasLapangan()->create(),
        ];

        foreach ($nonAdmin as $pengguna) {
            $this->actingAs($pengguna)->get(route('katalog.sumber-data.index'))->assertForbidden();
            $this->actingAs($pengguna)->get(route('katalog.sumber-data.create'))->assertForbidden();
            $this->actingAs($pengguna)->post(route('katalog.sumber-data.store'), [])->assertForbidden();
            $this->actingAs($pengguna)->get(route('katalog.sumber-data.show', $sumber))->assertForbidden();
            $this->actingAs($pengguna)->get(route('katalog.sumber-data.edit', $sumber))->assertForbidden();
            $this->actingAs($pengguna)->put(route('katalog.sumber-data.update', $sumber), [])->assertForbidden();
            $this->actingAs($pengguna)->patch(route('katalog.sumber-data.aktifkan', $sumber))->assertForbidden();
            $this->actingAs($pengguna)->patch(route('katalog.sumber-data.nonaktifkan', $sumber))->assertForbidden();
        }
    }

    public function test_aktor_admin_tercatat_pada_dataset_dan_riwayat(): void
    {
        $admin = Pengguna::factory()->admin()->create();

        $this->actingAs($admin)->post(route('katalog.sumber-data.store'), [
            'nama_sumber' => 'Dataset Audit Aktor',
            'jenis_sumber' => SumberData::JENIS_EKSTERNAL,
            'nama_penyedia' => 'NASA',
            'nama_lisensi' => 'Public Domain',
            'format_data' => 'CSV',
            'kode_crs' => 'EPSG:4326',
            'status_kualitas' => 'VERIFIED',
        ])->assertSessionHasNoErrors();

        $sumber = SumberData::query()->where('nama_sumber', 'Dataset Audit Aktor')->firstOrFail();

        $this->assertSame($admin->pengguna_id, $sumber->dibuat_oleh);
        $this->assertSame($admin->pengguna_id, $sumber->riwayat()->firstOrFail()->pengguna_id);
    }
}
