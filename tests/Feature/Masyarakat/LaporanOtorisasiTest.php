<?php

namespace Tests\Feature\Masyarakat;

use App\Enums\StatusLaporan;
use App\Models\BerkasMedia;
use App\Models\Laporan;
use App\Models\Pengguna;
use App\Models\RevisiLaporan;
use App\Models\Wilayah;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LaporanOtorisasiTest extends TestCase
{
    use RefreshDatabase;

    private Pengguna $pelaporA;

    private Pengguna $pelaporB;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');

        $this->pelaporA = Pengguna::factory()->masyarakat()->create();
        $this->pelaporB = Pengguna::factory()->create([
            'peran_id' => $this->pelaporA->peran_id,
        ]);
    }

    public function test_tamu_diarahkan_ke_login_untuk_semua_rute_laporan_saya(): void
    {
        $laporan = Laporan::factory()->perluPerbaikan()->create([
            'pelapor_id' => $this->pelaporA->pengguna_id,
        ]);

        $this->get(route('masyarakat.laporan.index'))->assertRedirect(route('login'));
        $this->get(route('masyarakat.laporan.show', $laporan))->assertRedirect(route('login'));
        $this->get(route('masyarakat.laporan.edit', $laporan))->assertRedirect(route('login'));
        $this->put(route('masyarakat.laporan.update', $laporan), [])->assertRedirect(route('login'));
    }

    public function test_peran_non_masyarakat_ditolak_mengakses_rute_laporan_saya(): void
    {
        $laporan = Laporan::factory()->perluPerbaikan()->create([
            'pelapor_id' => $this->pelaporA->pengguna_id,
        ]);

        $peranLain = [
            Pengguna::factory()->admin()->create(),
            Pengguna::factory()->pengawas()->create(),
            Pengguna::factory()->petugasLapangan()->create(),
        ];

        foreach ($peranLain as $pengguna) {
            $this->actingAs($pengguna)->get(route('masyarakat.laporan.index'))->assertForbidden();
            $this->actingAs($pengguna)->get(route('masyarakat.laporan.show', $laporan))->assertForbidden();
            $this->actingAs($pengguna)->get(route('masyarakat.laporan.edit', $laporan))->assertForbidden();
            $this->actingAs($pengguna)->put(route('masyarakat.laporan.update', $laporan), [])->assertForbidden();
        }
    }

    public function test_pelapor_tidak_dapat_melihat_atau_mengubah_laporan_milik_orang_lain(): void
    {
        $laporanMilikA = Laporan::factory()->perluPerbaikan()->create([
            'pelapor_id' => $this->pelaporA->pengguna_id,
        ]);

        $revisi = RevisiLaporan::factory()->create(['laporan_id' => $laporanMilikA->id, 'nomor_revisi' => 1]);
        $media = BerkasMedia::factory()->create([
            'path_file' => 'laporan/privat/rahasia.jpg',
            'diunggah_oleh' => $this->pelaporA->pengguna_id,
        ]);
        Storage::disk('local')->put($media->path_file, 'gambar');
        $revisi->foto()->attach($media->id, ['urutan_tampilan' => 1]);

        $this->actingAs($this->pelaporB)
            ->get(route('masyarakat.laporan.show', $laporanMilikA))
            ->assertForbidden();

        $this->actingAs($this->pelaporB)
            ->get(route('masyarakat.laporan.edit', $laporanMilikA))
            ->assertForbidden();

        $this->actingAs($this->pelaporB)
            ->put(route('masyarakat.laporan.update', $laporanMilikA), [
                'kategori' => $laporanMilikA->kategori,
                'wilayah_id' => $laporanMilikA->wilayah_id,
                'latitude' => 0.1,
                'longitude' => 101.1,
                'deskripsi' => 'Percobaan ubah laporan orang lain.',
            ])
            ->assertForbidden();

        $this->actingAs($this->pelaporB)
            ->get(route('masyarakat.laporan.media', [$laporanMilikA, $media]))
            ->assertForbidden();
    }

    public function test_pelapor_tidak_dapat_mengubah_laporan_yang_terkunci_dalam_pemeriksaan(): void
    {
        $statusTerkunci = [
            StatusLaporan::MENUNGGU_VERIFIKASI,
            StatusLaporan::DIVERIFIKASI,
            StatusLaporan::DALAM_INSPEKSI,
            StatusLaporan::DITOLAK,
            StatusLaporan::SELESAI,
        ];

        $wilayah = Wilayah::factory()->create();

        foreach ($statusTerkunci as $status) {
            $laporan = Laporan::factory()->status($status)->create([
                'pelapor_id' => $this->pelaporA->pengguna_id,
                'wilayah_id' => $wilayah->wilayah_id,
                'deskripsi' => 'Deskripsi asli sebelum terkunci.',
            ]);

            $this->actingAs($this->pelaporA)
                ->get(route('masyarakat.laporan.edit', $laporan))
                ->assertForbidden();

            $this->actingAs($this->pelaporA)
                ->put(route('masyarakat.laporan.update', $laporan), [
                    'kategori' => $laporan->kategori,
                    'wilayah_id' => $wilayah->wilayah_id,
                    'latitude' => 0.2,
                    'longitude' => 101.2,
                    'deskripsi' => 'Percobaan kirim ulang paksa pada laporan terkunci.',
                ])
                ->assertForbidden();

            $this->assertSame('Deskripsi asli sebelum terkunci.', $laporan->fresh()->deskripsi);
            $this->assertSame($status->value, $laporan->fresh()->status);
        }
    }

    public function test_pelapor_tidak_dapat_mencantumkan_media_milik_laporan_orang_lain(): void
    {
        $laporanA = Laporan::factory()->perluPerbaikan()->create([
            'pelapor_id' => $this->pelaporA->pengguna_id,
        ]);
        $revisiA = RevisiLaporan::factory()->create(['laporan_id' => $laporanA->id, 'nomor_revisi' => 1]);
        $mediaMilikA = BerkasMedia::factory()->create(['diunggah_oleh' => $this->pelaporA->pengguna_id]);
        $revisiA->foto()->attach($mediaMilikA->id, ['urutan_tampilan' => 1]);

        $laporanB = Laporan::factory()->perluPerbaikan()->create([
            'pelapor_id' => $this->pelaporB->pengguna_id,
        ]);

        $this->actingAs($this->pelaporB)
            ->put(route('masyarakat.laporan.update', $laporanB), [
                'kategori' => $laporanB->kategori,
                'wilayah_id' => $laporanB->wilayah_id,
                'latitude' => 0.5,
                'longitude' => 101.5,
                'deskripsi' => 'Mencoba menyematkan media dari laporan orang lain.',
                'pertahankan_foto' => [$mediaMilikA->id],
            ])
            ->assertSessionHasNoErrors();

        $revisiBaruB = $laporanB->fresh()->revisiTerbaru;
        $this->assertNotNull($revisiBaruB);
        $this->assertCount(0, $revisiBaruB->foto);
    }

    public function test_pelapor_dapat_mengakses_media_miliknya_dan_ditolak_jika_bukan_media_laporan_tersebut(): void
    {
        $laporanA1 = Laporan::factory()->create(['pelapor_id' => $this->pelaporA->pengguna_id]);
        $revisiA1 = RevisiLaporan::factory()->create(['laporan_id' => $laporanA1->id, 'nomor_revisi' => 1]);
        $mediaA1 = BerkasMedia::factory()->create([
            'path_file' => 'laporan/a1/foto.jpg',
            'mime_type' => 'image/jpeg',
            'diunggah_oleh' => $this->pelaporA->pengguna_id,
        ]);
        Storage::disk('local')->put($mediaA1->path_file, 'isi-gambar-valid');
        $revisiA1->foto()->attach($mediaA1->id, ['urutan_tampilan' => 1]);

        $laporanA2 = Laporan::factory()->create(['pelapor_id' => $this->pelaporA->pengguna_id]);

        $this->actingAs($this->pelaporA)
            ->get(route('masyarakat.laporan.media', [$laporanA1, $mediaA1]))
            ->assertOk();

        $this->actingAs($this->pelaporA)
            ->get(route('masyarakat.laporan.media', [$laporanA2, $mediaA1]))
            ->assertNotFound();
    }
}
