<?php

namespace Tests\Feature\Pengawas;

use App\Enums\StatusKegiatan;
use App\Models\Kawasan;
use App\Models\KegiatanKonservasi;
use App\Models\Pengguna;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KegiatanTest extends TestCase
{
    use RefreshDatabase;

    private function dataValid(Kawasan $kawasan, array $ubah = []): array
    {
        return array_merge([
            'aksi' => 'terbitkan',
            'kawasan_id' => $kawasan->kawasan_id,
            'nama_kegiatan' => 'Penanaman Mangrove',
            'tujuan' => 'Memulihkan tutupan mangrove',
            'jenis_kegiatan' => 'Penanaman',
            'tanggal_mulai' => '2026-12-01',
            'tanggal_selesai' => '2026-12-05',
            'deskripsi_target' => '500 bibit tertanam',
            'penanggung_jawab' => 'Budi Santoso',
            'deskripsi_sumber_daya' => '500 bibit dan 10 relawan',
            'kuota_peserta' => 30,
        ], $ubah);
    }

    public function test_tamu_diarahkan_ke_halaman_login(): void
    {
        $this->get(route('pengawas.kegiatan.index'))->assertRedirect(route('login'));
    }

    public function test_peran_selain_pengawas_ditolak(): void
    {
        $this->actingAs(Pengguna::factory()->masyarakat()->create())
            ->get(route('pengawas.kegiatan.index'))
            ->assertForbidden();

        $this->actingAs(Pengguna::factory()->admin()->create())
            ->get(route('pengawas.kegiatan.create'))
            ->assertForbidden();
    }

    public function test_pengawas_dapat_melihat_daftar_dan_form(): void
    {
        $kegiatan = KegiatanKonservasi::factory()->create(['nama_kegiatan' => 'Penanaman Contoh']);

        $this->actingAs(Pengguna::factory()->pengawas()->create())
            ->get(route('pengawas.kegiatan.index'))
            ->assertOk()
            ->assertSee('Penanaman Contoh');

        $this->get(route('pengawas.kegiatan.create'))->assertOk();
        $this->get(route('pengawas.kegiatan.edit', $kegiatan))->assertOk();
    }

    public function test_rencana_lengkap_dapat_diterbitkan(): void
    {
        $kawasan = Kawasan::factory()->create();

        $this->actingAs(Pengguna::factory()->pengawas()->create())
            ->post(route('pengawas.kegiatan.store'), $this->dataValid($kawasan))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('kegiatan_konservasi', [
            'nama_kegiatan' => 'Penanaman Mangrove',
            'kawasan_id' => $kawasan->kawasan_id,
            'status_kegiatan' => StatusKegiatan::DITERBITKAN->value,
        ]);
    }

    public function test_draf_boleh_disimpan_tanpa_target_dan_penanggung_jawab(): void
    {
        $kawasan = Kawasan::factory()->create();

        $this->actingAs(Pengguna::factory()->pengawas()->create())
            ->post(route('pengawas.kegiatan.store'), $this->dataValid($kawasan, [
                'aksi' => 'draf',
                'deskripsi_target' => '',
                'penanggung_jawab' => '',
            ]))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('kegiatan_konservasi', [
            'nama_kegiatan' => 'Penanaman Mangrove',
            'status_kegiatan' => StatusKegiatan::DRAF->value,
        ]);
    }

    public function test_penerbitan_ditolak_bila_target_atau_penanggung_jawab_kosong(): void
    {
        $kawasan = Kawasan::factory()->create();
        $this->actingAs(Pengguna::factory()->pengawas()->create());

        foreach (['deskripsi_target', 'penanggung_jawab'] as $kolom) {
            $this->post(route('pengawas.kegiatan.store'), $this->dataValid($kawasan, [$kolom => '']))
                ->assertSessionHasErrors($kolom);
        }

        $this->assertDatabaseCount('kegiatan_konservasi', 0);
    }

    public function test_kawasan_tujuan_dan_jadwal_selalu_wajib(): void
    {
        $kawasan = Kawasan::factory()->create();
        $this->actingAs(Pengguna::factory()->pengawas()->create());

        foreach (['kawasan_id', 'tujuan', 'tanggal_mulai', 'tanggal_selesai'] as $kolom) {
            $this->post(route('pengawas.kegiatan.store'), $this->dataValid($kawasan, ['aksi' => 'draf', $kolom => '']))
                ->assertSessionHasErrors($kolom);
        }

        $this->assertDatabaseCount('kegiatan_konservasi', 0);
    }

    public function test_tanggal_selesai_sebelum_tanggal_mulai_ditolak(): void
    {
        $kawasan = Kawasan::factory()->create();

        $this->actingAs(Pengguna::factory()->pengawas()->create())
            ->post(route('pengawas.kegiatan.store'), $this->dataValid($kawasan, [
                'tanggal_mulai' => '2026-12-10',
                'tanggal_selesai' => '2026-12-01',
            ]))
            ->assertSessionHasErrors('tanggal_selesai');

        $this->assertDatabaseCount('kegiatan_konservasi', 0);
    }

    public function test_kawasan_nonaktif_ditolak(): void
    {
        $kawasan = Kawasan::factory()->create(['is_aktif' => false]);

        $this->actingAs(Pengguna::factory()->pengawas()->create())
            ->post(route('pengawas.kegiatan.store'), $this->dataValid($kawasan))
            ->assertSessionHasErrors('kawasan_id');
    }

    public function test_rencana_dapat_diperbarui_dan_diterbitkan(): void
    {
        $kegiatan = KegiatanKonservasi::factory()->create(['nama_kegiatan' => 'Nama Lama']);

        $this->actingAs(Pengguna::factory()->pengawas()->create())
            ->put(route('pengawas.kegiatan.update', $kegiatan), $this->dataValid($kegiatan->kawasan, [
                'nama_kegiatan' => 'Nama Baru',
            ]))
            ->assertSessionHasNoErrors();

        $kegiatan->refresh();

        $this->assertSame('Nama Baru', $kegiatan->nama_kegiatan);
        $this->assertSame(StatusKegiatan::DITERBITKAN, $kegiatan->status_kegiatan);
    }

    public function test_kegiatan_terbit_tidak_bisa_dikembalikan_menjadi_draf(): void
    {
        $kegiatan = KegiatanKonservasi::factory()->diterbitkan()->create();

        $this->actingAs(Pengguna::factory()->pengawas()->create())
            ->put(route('pengawas.kegiatan.update', $kegiatan), $this->dataValid($kegiatan->kawasan, ['aksi' => 'draf']))
            ->assertSessionHasErrors('aksi');

        $this->assertSame(StatusKegiatan::DITERBITKAN, $kegiatan->fresh()->status_kegiatan);
    }

    public function test_status_rencana_terlihat_pada_detail(): void
    {
        $kegiatan = KegiatanKonservasi::factory()->diterbitkan()->create();

        $this->actingAs(Pengguna::factory()->pengawas()->create())
            ->get(route('pengawas.kegiatan.show', $kegiatan))
            ->assertOk()
            ->assertSee('Status rencana: Diterbitkan');
    }

    public function test_kegiatan_dapat_dibatalkan_dan_tidak_bisa_diubah_lagi(): void
    {
        $kegiatan = KegiatanKonservasi::factory()->diterbitkan()->create();

        $this->actingAs(Pengguna::factory()->pengawas()->create())
            ->patch(route('pengawas.kegiatan.batalkan', $kegiatan))
            ->assertSessionHasNoErrors();

        $this->assertSame(StatusKegiatan::DIBATALKAN, $kegiatan->fresh()->status_kegiatan);

        $this->get(route('pengawas.kegiatan.edit', $kegiatan))->assertForbidden();
        $this->patch(route('pengawas.kegiatan.batalkan', $kegiatan))->assertForbidden();
    }
}
