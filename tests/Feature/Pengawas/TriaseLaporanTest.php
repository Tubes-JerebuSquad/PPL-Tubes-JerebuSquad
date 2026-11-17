<?php

namespace Tests\Feature\Pengawas;

use App\Models\Laporan;
use App\Models\PeristiwaLaporan;
use App\Models\Pengguna;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TriaseLaporanTest extends TestCase
{
    use RefreshDatabase;

    public function test_pengawas_dapat_melihat_daftar_dan_detail_laporan(): void
    {
        $pengawas = Pengguna::factory()->pengawas()->create();
        $laporan  = Laporan::factory()->create();

        $this->actingAs($pengawas)
            ->get(route('pengawas.dashboard'))
            ->assertOk()
            ->assertSee($laporan->kode_laporan)
            ->assertSee('Daftar Laporan Terkini');

        $this->actingAs($pengawas)
            ->get(route('pengawas.laporan.index'))
            ->assertOk()
            ->assertSee($laporan->kode_laporan);

        $this->actingAs($pengawas)
            ->get(route('pengawas.laporan.show', $laporan))
            ->assertOk()
            ->assertSee($laporan->kode_laporan)
            ->assertSee('Simpan keputusan');
    }

    public function test_tamu_diarahkan_ke_login(): void
    {
        $laporan = Laporan::factory()->create();

        $this->get(route('pengawas.laporan.index'))->assertRedirect(route('login'));
        $this->post(route('pengawas.laporan.triase', $laporan), ['tindakan' => 'terima'])
            ->assertRedirect(route('login'));
    }

    public function test_peran_selain_pengawas_tidak_dapat_mengakses_atau_menetapkan_triase(): void
    {
        $laporan = Laporan::factory()->create();

        $penggunaLain = [
            Pengguna::factory()->admin()->create(),
            $laporan->pelapor, // Peran Masyarakat yang sudah dibuat oleh LaporanFactory
            Pengguna::factory()->petugasLapangan()->create(),
        ];

        foreach ($penggunaLain as $pengguna) {
            $this->actingAs($pengguna)
                ->get(route('pengawas.laporan.index'))
                ->assertForbidden();

            $this->actingAs($pengguna)
                ->post(route('pengawas.laporan.triase', $laporan), ['tindakan' => 'terima'])
                ->assertForbidden();
        }

        $this->assertSame(Laporan::STATUS_MENUNGGU_VERIFIKASI, $laporan->fresh()->status_laporan);
        $this->assertDatabaseCount('peristiwa_laporan', 0);
    }

    public function test_pengawas_nonaktif_ditolak(): void
    {
        $pengawas = Pengguna::factory()->pengawas()->nonaktif()->create();
        $laporan  = Laporan::factory()->create();

        $this->actingAs($pengawas)
            ->post(route('pengawas.laporan.triase', $laporan), ['tindakan' => 'terima'])
            ->assertForbidden();
    }

    public function test_pengawas_dapat_menerima_laporan_untuk_inspeksi_dan_riwayat_tercatat(): void
    {
        $pengawas = Pengguna::factory()->pengawas()->create();
        $laporan  = Laporan::factory()->create();

        $this->actingAs($pengawas)
            ->post(route('pengawas.laporan.triase', $laporan), [
                'tindakan'        => 'terima',
                'kategori_triase' => 'kebakaran',
                'prioritas_triase' => 'tinggi',
                'catatan_internal' => 'Koordinat sesuai peta kawasan.',
            ])
            ->assertRedirect(route('pengawas.laporan.show', $laporan))
            ->assertSessionHas('status');

        $laporan->refresh();

        $this->assertSame(Laporan::STATUS_DIVERIFIKASI, $laporan->status_laporan);
        $this->assertSame('kebakaran', $laporan->kategori_triase);
        $this->assertSame('tinggi', $laporan->prioritas_triase);
        $this->assertSame($pengawas->pengguna_id, (int) $laporan->ditriase_oleh);
        $this->assertNotNull($laporan->ditriase_pada);

        $this->assertDatabaseHas('peristiwa_laporan', [
            'laporan_id'      => $laporan->laporan_id,
            'aktor_id'        => $pengawas->pengguna_id,
            'jenis_peristiwa' => PeristiwaLaporan::JENIS_DITERIMA,
            'status_lama'     => Laporan::STATUS_MENUNGGU_VERIFIKASI,
            'status_baru'     => Laporan::STATUS_DIVERIFIKASI,
        ]);
        $this->assertNotNull(PeristiwaLaporan::first()->terjadi_pada);
    }

    public function test_minta_perbaikan_dan_tolak_wajib_menyertakan_alasan(): void
    {
        $pengawas = Pengguna::factory()->pengawas()->create();
        $laporan  = Laporan::factory()->create();
        $halaman  = route('pengawas.laporan.show', $laporan);

        foreach (['minta_perbaikan', 'tolak'] as $tindakan) {
            $this->actingAs($pengawas)
                ->from($halaman)
                ->post(route('pengawas.laporan.triase', $laporan), [
                    'tindakan'       => $tindakan,
                    'catatan_publik' => '',
                ])
                ->assertRedirect($halaman)
                ->assertSessionHasErrors('catatan_publik');
        }

        $this->assertSame(Laporan::STATUS_MENUNGGU_VERIFIKASI, $laporan->fresh()->status_laporan);
        $this->assertDatabaseCount('peristiwa_laporan', 0);
    }

    public function test_minta_perbaikan_dengan_alasan_tersimpan_dan_dapat_dilihat_pelapor_nanti(): void
    {
        $pengawas = Pengguna::factory()->pengawas()->create();
        $laporan  = Laporan::factory()->create();

        $this->actingAs($pengawas)
            ->post(route('pengawas.laporan.triase', $laporan), [
                'tindakan'        => 'minta_perbaikan',
                'catatan_publik'  => 'Mohon tambahkan foto lokasi yang lebih jelas.',
                'catatan_internal' => 'Foto buram, curiga lokasi berbeda.',
            ])
            ->assertRedirect(route('pengawas.laporan.show', $laporan));

        $laporan->refresh();

        $this->assertSame(Laporan::STATUS_DIKEMBALIKAN, $laporan->status_laporan);
        $this->assertSame('Mohon tambahkan foto lokasi yang lebih jelas.', $laporan->catatan_publik);

        // Catatan internal tidak boleh masuk riwayat.
        $peristiwa = PeristiwaLaporan::where('laporan_id', $laporan->laporan_id)->firstOrFail();
        $this->assertSame('Mohon tambahkan foto lokasi yang lebih jelas.', $peristiwa->catatan_peristiwa);
        $this->assertStringNotContainsString('curiga', (string) $peristiwa->catatan_peristiwa);
    }

    public function test_pengawas_dapat_menolak_laporan_dengan_alasan(): void
    {
        $pengawas = Pengguna::factory()->pengawas()->create();
        $laporan  = Laporan::factory()->create();

        $this->actingAs($pengawas)
            ->post(route('pengawas.laporan.triase', $laporan), [
                'tindakan'       => 'tolak',
                'catatan_publik' => 'Lokasi berada di luar wilayah pemantauan.',
            ])
            ->assertRedirect(route('pengawas.laporan.show', $laporan));

        $this->assertSame(Laporan::STATUS_DITOLAK, $laporan->fresh()->status_laporan);
        $this->assertDatabaseHas('peristiwa_laporan', [
            'laporan_id'      => $laporan->laporan_id,
            'jenis_peristiwa' => PeristiwaLaporan::JENIS_DITOLAK,
            'status_baru'     => Laporan::STATUS_DITOLAK,
        ]);
    }

    public function test_laporan_yang_sudah_diputuskan_tidak_dapat_ditriase_ulang(): void
    {
        $pengawas = Pengguna::factory()->pengawas()->create();
        $laporan  = Laporan::factory()->status(Laporan::STATUS_DITOLAK)->create();
        $halaman  = route('pengawas.laporan.show', $laporan);

        $this->actingAs($pengawas)
            ->from($halaman)
            ->post(route('pengawas.laporan.triase', $laporan), ['tindakan' => 'terima'])
            ->assertSessionHasErrors('tindakan');

        $this->assertSame(Laporan::STATUS_DITOLAK, $laporan->fresh()->status_laporan);
        $this->assertDatabaseCount('peristiwa_laporan', 0);
    }

    public function test_tindakan_tidak_dikenal_ditolak(): void
    {
        $pengawas = Pengguna::factory()->pengawas()->create();
        $laporan  = Laporan::factory()->create();

        $this->actingAs($pengawas)
            ->post(route('pengawas.laporan.triase', $laporan), ['tindakan' => 'hapus'])
            ->assertSessionHasErrors('tindakan');

        $this->assertSame(Laporan::STATUS_MENUNGGU_VERIFIKASI, $laporan->fresh()->status_laporan);
    }
}
