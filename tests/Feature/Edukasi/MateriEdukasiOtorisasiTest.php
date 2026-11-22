<?php

namespace Tests\Feature\Edukasi;

use App\Models\MateriEdukasi;
use App\Models\Pengguna;
use App\Models\Peran;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MateriEdukasiOtorisasiTest extends TestCase
{
    use RefreshDatabase;

    public function test_tamu_diarahkan_ke_login_untuk_semua_rute_pengelolaan_edukasi(): void
    {
        $materi = MateriEdukasi::factory()->create();

        $this->get(route('pengawas.edukasi.index'))->assertRedirect(route('login'));
        $this->get(route('pengawas.edukasi.create'))->assertRedirect(route('login'));
        $this->post(route('pengawas.edukasi.store'), [])->assertRedirect(route('login'));
        $this->get(route('pengawas.edukasi.show', $materi))->assertRedirect(route('login'));
        $this->get(route('pengawas.edukasi.edit', $materi))->assertRedirect(route('login'));
        $this->put(route('pengawas.edukasi.update', $materi), [])->assertRedirect(route('login'));
        $this->patch(route('pengawas.edukasi.terbitkan', $materi))->assertRedirect(route('login'));
        $this->patch(route('pengawas.edukasi.arsipkan', $materi))->assertRedirect(route('login'));
    }

    public function test_peran_selain_pengawas_dan_pengawas_nonaktif_ditolak_mengelola_edukasi(): void
    {
        $materi = MateriEdukasi::factory()->create();
        $peranPengawas = Peran::query()->where('nama_peran', 'Pengawas')->firstOrFail();

        $tanpaIzin = [
            Pengguna::factory()->masyarakat()->create(),
            Pengguna::factory()->admin()->create(),
            Pengguna::factory()->petugasLapangan()->create(),
            Pengguna::factory()->nonaktif()->create(['peran_id' => $peranPengawas->peran_id]),
        ];

        foreach ($tanpaIzin as $pengguna) {
            $this->actingAs($pengguna)->get(route('pengawas.edukasi.index'))->assertForbidden();
            $this->actingAs($pengguna)->get(route('pengawas.edukasi.create'))->assertForbidden();
            $this->actingAs($pengguna)->post(route('pengawas.edukasi.store'), [])->assertForbidden();
            $this->actingAs($pengguna)->get(route('pengawas.edukasi.show', $materi))->assertForbidden();
            $this->actingAs($pengguna)->get(route('pengawas.edukasi.edit', $materi))->assertForbidden();
            $this->actingAs($pengguna)->put(route('pengawas.edukasi.update', $materi), [])->assertForbidden();
            $this->actingAs($pengguna)->patch(route('pengawas.edukasi.terbitkan', $materi))->assertForbidden();
            $this->actingAs($pengguna)->patch(route('pengawas.edukasi.arsipkan', $materi))->assertForbidden();
        }
    }

    public function test_halaman_publik_menolak_detail_materi_draf_dan_arsip(): void
    {
        $draf = MateriEdukasi::factory()->draf()->create();
        $arsip = MateriEdukasi::factory()->arsip()->create();
        $masyarakat = Pengguna::factory()->masyarakat()->create();

        $this->get(route('edukasi.show', $draf))->assertNotFound();
        $this->get(route('edukasi.show', $arsip))->assertNotFound();

        $this->actingAs($masyarakat)->get(route('edukasi.show', $draf))->assertNotFound();
        $this->actingAs($masyarakat)->get(route('edukasi.show', $arsip))->assertNotFound();
    }
}
