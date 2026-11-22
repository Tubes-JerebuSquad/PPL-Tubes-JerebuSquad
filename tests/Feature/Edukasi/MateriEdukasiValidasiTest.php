<?php

namespace Tests\Feature\Edukasi;

use App\Enums\StatusPublikasi;
use App\Models\MateriEdukasi;
use App\Models\Pengguna;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MateriEdukasiValidasiTest extends TestCase
{
    use RefreshDatabase;

    private Pengguna $pengawas;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pengawas = Pengguna::factory()->pengawas()->create();
        $this->actingAs($this->pengawas);
    }

    /**
     * @param  array<string, mixed>  $ganti
     * @return array<string, mixed>
     */
    private function dataValid(array $ganti = []): array
    {
        return array_merge([
            'judul' => 'Teknik Penanaman Bibit Mangrove di Pesisir',
            'kategori' => 'Rehabilitasi Lahan',
            'konten' => 'Penanaman mangrove membutuhkan pemetaan pasang surut serta pemilihan bibit yang sesuai.',
            'referensi_sumber' => 'KLHK · Modul Rehabilitasi Mangrove 2025',
        ], $ganti);
    }

    public function test_store_menyimpan_materi_baru_sebagai_draf_dan_mencatat_pembuat(): void
    {
        $respons = $this->post(route('pengawas.edukasi.store'), $this->dataValid());

        $materi = MateriEdukasi::query()->firstOrFail();

        $respons->assertSessionHasNoErrors();
        $respons->assertRedirect(route('pengawas.edukasi.show', $materi));

        $this->assertSame(StatusPublikasi::DRAFT, $materi->status_publikasi);
        $this->assertSame($this->pengawas->pengguna_id, $materi->dibuat_oleh);
        $this->assertSame($this->pengawas->pengguna_id, $materi->diperbarui_oleh);
        $this->assertNull($materi->dipublikasikan_pada);
    }

    public function test_store_menolak_field_wajib_kosong(): void
    {
        $this->post(route('pengawas.edukasi.store'), [
            'judul' => '',
            'kategori' => '',
            'konten' => '',
            'referensi_sumber' => '',
        ])->assertSessionHasErrors(['judul', 'kategori', 'konten', 'referensi_sumber']);

        $this->assertSame(0, MateriEdukasi::query()->count());
    }

    public function test_store_menolak_judul_dan_kategori_melebihi_batas(): void
    {
        $this->post(route('pengawas.edukasi.store'), $this->dataValid([
            'judul' => str_repeat('a', 251),
            'kategori' => str_repeat('b', 101),
        ]))->assertSessionHasErrors(['judul', 'kategori']);
    }

    public function test_update_memperbarui_materi_dan_mencatat_pembaru(): void
    {
        $materi = MateriEdukasi::factory()->draf()->create([
            'dibuat_oleh' => $this->pengawas->pengguna_id,
        ]);
        $pengawasLain = Pengguna::factory()->create(['peran_id' => $this->pengawas->peran_id]);

        $respons = $this->actingAs($pengawasLain)->put(
            route('pengawas.edukasi.update', $materi),
            [
                'judul' => 'Judul Materi Diperbarui',
                'referensi_sumber' => 'BRGM · Pedoman Terbaru',
            ],
        );

        $respons->assertSessionHasNoErrors();
        $respons->assertRedirect(route('pengawas.edukasi.show', $materi));

        $segar = $materi->fresh();
        $this->assertSame('Judul Materi Diperbarui', $segar->judul);
        $this->assertSame('BRGM · Pedoman Terbaru', $segar->referensi_sumber);
        $this->assertSame(StatusPublikasi::DRAFT, $segar->status_publikasi);
        $this->assertSame($pengawasLain->pengguna_id, $segar->diperbarui_oleh);
    }

    public function test_terbitkan_dan_arsipkan_mengubah_status_materi(): void
    {
        $materi = MateriEdukasi::factory()->draf()->create([
            'dibuat_oleh' => $this->pengawas->pengguna_id,
        ]);

        $this->patch(route('pengawas.edukasi.terbitkan', $materi))
            ->assertSessionHasNoErrors();

        $segar = $materi->fresh();
        $this->assertTrue($segar->sudahTerbit());
        $this->assertNotNull($segar->dipublikasikan_pada);

        $this->patch(route('pengawas.edukasi.arsipkan', $materi))
            ->assertSessionHasNoErrors();

        $arsip = $materi->fresh();
        $this->assertTrue($arsip->sudahDiarsipkan());
        $this->assertNotNull($arsip->diarsipkan_pada);
    }
}
