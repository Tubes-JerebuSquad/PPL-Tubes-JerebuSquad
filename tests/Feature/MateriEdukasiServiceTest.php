<?php

namespace Tests\Feature;

use App\Enums\StatusPublikasi;
use App\Models\MateriEdukasi;
use App\Models\Pengguna;
use App\Services\MateriEdukasiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MateriEdukasiServiceTest extends TestCase
{
    use RefreshDatabase;

    private MateriEdukasiService $service;

    private Pengguna $pengawas;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(MateriEdukasiService::class);
        $this->pengawas = Pengguna::factory()->pengawas()->create();
    }

    /**
     * @param  array<string, mixed>  $ganti
     * @return array<string, mixed>
     */
    private function dataValid(array $ganti = []): array
    {
        return array_merge([
            'judul' => 'Panduan Pencegahan Kebakaran Lahan Gambut',
            'konten' => 'Menjaga muka air tanah gambut membantu menekan risiko kebakaran lahan.',
            'kategori' => 'Pencegahan Kebakaran',
            'referensi_sumber' => 'Badan Restorasi Gambut dan Mangrove (BRGM)',
        ], $ganti);
    }

    public function test_simpan_membuat_materi_berstatus_draf_dan_mencatat_pembuat(): void
    {
        $materi = $this->service->simpan(
            $this->dataValid([
                'status_publikasi' => StatusPublikasi::PUBLISHED->value,
                'dibuat_oleh' => 999999,
            ]),
            $this->pengawas,
        );

        $this->assertSame(StatusPublikasi::DRAFT, $materi->status_publikasi);
        $this->assertSame($this->pengawas->pengguna_id, $materi->dibuat_oleh);
        $this->assertSame($this->pengawas->pengguna_id, $materi->diperbarui_oleh);
        $this->assertNull($materi->dipublikasikan_pada);
        $this->assertNull($materi->diarsipkan_pada);
    }

    public function test_perbarui_mengubah_atribut_dan_mencatat_pengguna_pembaru(): void
    {
        $materi = $this->service->simpan($this->dataValid(), $this->pengawas);
        $pengawasLain = Pengguna::factory()->create(['peran_id' => $this->pengawas->peran_id]);

        $diperbarui = $this->service->perbarui($materi, [
            'judul' => 'Panduan Pemulihan Ekosistem Gambut',
            'kategori' => 'Rehabilitasi Lahan',
        ], $pengawasLain);

        $this->assertSame('Panduan Pemulihan Ekosistem Gambut', $diperbarui->judul);
        $this->assertSame('Rehabilitasi Lahan', $diperbarui->kategori);
        $this->assertSame(StatusPublikasi::DRAFT, $diperbarui->status_publikasi);
        $this->assertSame($this->pengawas->pengguna_id, $diperbarui->dibuat_oleh);
        $this->assertSame($pengawasLain->pengguna_id, $diperbarui->diperbarui_oleh);
    }

    public function test_terbitkan_dan_arsipkan_mengatur_status_serta_cap_waktu(): void
    {
        $materi = $this->service->simpan($this->dataValid(), $this->pengawas);

        $terbit = $this->service->terbitkan($materi, $this->pengawas);

        $this->assertTrue($terbit->sudahTerbit());
        $this->assertNotNull($terbit->dipublikasikan_pada);
        $this->assertNull($terbit->diarsipkan_pada);

        $arsip = $this->service->arsipkan($terbit, $this->pengawas);

        $this->assertTrue($arsip->sudahDiarsipkan());
        $this->assertNotNull($arsip->diarsipkan_pada);

        $terbitUlang = $this->service->terbitkan($arsip, $this->pengawas);

        $this->assertTrue($terbitUlang->sudahTerbit());
        $this->assertNull($terbitUlang->diarsipkan_pada);
    }

    public function test_terbitkan_dan_arsipkan_idempoten(): void
    {
        $materi = MateriEdukasi::factory()->terbit()->create();
        $waktuTerbitAwal = $materi->dipublikasikan_pada;

        $hasilTerbit = $this->service->terbitkan($materi, $this->pengawas);

        $this->assertSame($waktuTerbitAwal?->toIso8601String(), $hasilTerbit->dipublikasikan_pada?->toIso8601String());

        $arsip = $this->service->arsipkan($materi, $this->pengawas);
        $waktuArsipAwal = $arsip->diarsipkan_pada;

        $hasilArsip = $this->service->arsipkan($arsip, $this->pengawas);

        $this->assertSame($waktuArsipAwal?->toIso8601String(), $hasilArsip->diarsipkan_pada?->toIso8601String());
    }

    public function test_scope_terbit_hanya_mengembalikan_materi_dipublikasikan(): void
    {
        $draf = MateriEdukasi::factory()->draf()->create(['judul' => 'Materi Draf']);
        $terbit = MateriEdukasi::factory()->terbit()->create(['judul' => 'Materi Terbit']);
        $arsip = MateriEdukasi::factory()->arsip()->create(['judul' => 'Materi Arsip']);

        $idsTerbit = MateriEdukasi::query()->terbit()->pluck('materi_id')->all();

        $this->assertSame([$terbit->materi_id], $idsTerbit);
        $this->assertNotContains($draf->materi_id, $idsTerbit);
        $this->assertNotContains($arsip->materi_id, $idsTerbit);
    }
}
