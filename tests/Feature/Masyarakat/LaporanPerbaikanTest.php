<?php

namespace Tests\Feature\Masyarakat;

use App\Enums\KategoriLaporan;
use App\Enums\StatusLaporan;
use App\Exceptions\LaporanTerkunciException;
use App\Models\BerkasMedia;
use App\Models\Kawasan;
use App\Models\Laporan;
use App\Models\Pengguna;
use App\Models\PeristiwaLaporan;
use App\Models\RevisiLaporan;
use App\Models\Wilayah;
use App\Services\LaporanPerbaikanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LaporanPerbaikanTest extends TestCase
{
    use RefreshDatabase;

    private Pengguna $pelapor;

    private Wilayah $wilayah;

    private Kawasan $kawasan;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');

        $this->pelapor = Pengguna::factory()->masyarakat()->create();
        $this->wilayah = Wilayah::factory()->create(['nama_wilayah' => 'Kabupaten Pelalawan']);
        $this->kawasan = Kawasan::factory()->create([
            'wilayah_id' => $this->wilayah->wilayah_id,
            'nama_kawasan' => 'Taman Nasional Tesso Nilo',
        ]);
    }

    /**
     * PNG 1x1 valid agar pengujian unggah gambar tidak bergantung pada ekstensi PHP GD.
     */
    private function gambarPalsu(string $nama = 'bukti.png'): UploadedFile
    {
        $png = base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=',
            true,
        );

        return UploadedFile::fake()->createWithContent($nama, (string) $png);
    }

    public function test_pelapor_dapat_membuka_form_perbaikan_untuk_laporan_perlu_perbaikan(): void
    {
        $laporan = Laporan::factory()->perluPerbaikan()->create([
            'pelapor_id' => $this->pelapor->pengguna_id,
            'wilayah_id' => $this->wilayah->wilayah_id,
        ]);

        PeristiwaLaporan::factory()->create([
            'laporan_id' => $laporan->id,
            'jenis_peristiwa' => 'PERMINTAAN_PERBAIKAN',
            'catatan_publik' => 'Koordinat masih berada di luar wilayah laporan.',
        ]);

        $this->actingAs($this->pelapor)
            ->get(route('masyarakat.laporan.edit', $laporan))
            ->assertOk()
            ->assertSee('Perbaiki Laporan #'.$laporan->nomor_laporan)
            ->assertSee('Koordinat masih berada di luar wilayah laporan.');
    }

    public function test_perbaikan_membuat_revisi_baru_append_only_dan_mengirim_ulang_laporan(): void
    {
        $laporan = Laporan::factory()->perluPerbaikan()->create([
            'pelapor_id' => $this->pelapor->pengguna_id,
            'wilayah_id' => $this->wilayah->wilayah_id,
            'kategori' => KategoriLaporan::DEFORESTASI->value,
            'deskripsi' => 'Uraian awal yang masih terlalu singkat.',
        ]);

        $revisiAwal = RevisiLaporan::factory()->create([
            'laporan_id' => $laporan->id,
            'nomor_revisi' => 1,
            'kategori' => KategoriLaporan::DEFORESTASI->value,
            'deskripsi' => 'Uraian awal yang masih terlalu singkat.',
        ]);

        $fotoLama = BerkasMedia::factory()->create([
            'nama_asli' => 'foto-awal.jpg',
            'path_file' => 'laporan/awal/foto-awal.jpg',
            'diunggah_oleh' => $this->pelapor->pengguna_id,
        ]);
        Storage::disk('local')->put($fotoLama->path_file, 'konten-lama');
        $revisiAwal->foto()->attach($fotoLama->id, ['urutan_tampilan' => 1]);

        $fotoBaru = $this->gambarPalsu('bukti-baru.png');

        $respons = $this->actingAs($this->pelapor)
            ->put(route('masyarakat.laporan.update', $laporan), [
                'kategori' => KategoriLaporan::ALIH_FUNGSI_LAHAN->value,
                'wilayah_id' => $this->wilayah->wilayah_id,
                'kawasan_id' => $this->kawasan->kawasan_id,
                'latitude' => -0.12345,
                'longitude' => 101.67891,
                'waktu_kejadian' => '2026-11-15T10:30',
                'deskripsi' => 'Pembukaan lahan perkebunan seluas sekitar 2 hektare di batas kawasan.',
                'catatan_revisi' => 'Menambahkan koordinat akurat dan foto kondisi terbaru.',
                'pertahankan_foto' => [$fotoLama->id],
                'foto' => [$fotoBaru],
            ]);

        $respons->assertSessionHasNoErrors();
        $respons->assertRedirect(route('masyarakat.laporan.show', $laporan));

        $laporanSegar = $laporan->fresh();
        $this->assertSame(StatusLaporan::MENUNGGU_VERIFIKASI->value, $laporanSegar->status);
        $this->assertSame(KategoriLaporan::ALIH_FUNGSI_LAHAN->value, $laporanSegar->kategori);
        $this->assertSame($this->kawasan->kawasan_id, $laporanSegar->kawasan_id);
        $this->assertSame('Pembukaan lahan perkebunan seluas sekitar 2 hektare di batas kawasan.', $laporanSegar->deskripsi);

        // Revisi awal tidak tertimpa, revisi ke-2 terbuat
        $this->assertDatabaseCount('revisi_laporan', 2);
        $this->assertSame('Uraian awal yang masih terlalu singkat.', $revisiAwal->fresh()->deskripsi);

        $revisiKedua = $laporanSegar->revisi()->where('nomor_revisi', 2)->firstOrFail();
        $this->assertSame('Menambahkan koordinat akurat dan foto kondisi terbaru.', $revisiKedua->catatan_revisi);
        $this->assertCount(2, $revisiKedua->foto);

        $mediaBaru = $revisiKedua->foto->firstWhere('nama_asli', 'bukti-baru.png');
        $this->assertNotNull($mediaBaru);
        Storage::disk('local')->assertExists($mediaBaru->path_file);

        // Jejak peristiwa tercatat dengan status lama -> status baru dan aktor
        $peristiwa = $laporanSegar->peristiwa()->firstOrFail();
        $this->assertSame('PERBAIKAN_DIKIRIM', $peristiwa->jenis_peristiwa);
        $this->assertSame(StatusLaporan::PERLU_PERBAIKAN->value, $peristiwa->status_lama);
        $this->assertSame(StatusLaporan::MENUNGGU_VERIFIKASI->value, $peristiwa->status_baru);
        $this->assertSame($this->pelapor->pengguna_id, $peristiwa->pengguna_id);
    }

    public function test_perbaikan_otomatis_menyimpan_snapshot_awal_bila_belum_ada_revisi(): void
    {
        $laporan = Laporan::factory()->perluPerbaikan()->create([
            'pelapor_id' => $this->pelapor->pengguna_id,
            'wilayah_id' => $this->wilayah->wilayah_id,
            'deskripsi' => 'Catatan awal sebelum ada baris revisi.',
        ]);

        $this->actingAs($this->pelapor)
            ->put(route('masyarakat.laporan.update', $laporan), [
                'kategori' => KategoriLaporan::KEBAKARAN->value,
                'wilayah_id' => $this->wilayah->wilayah_id,
                'latitude' => -0.5,
                'longitude' => 102.5,
                'deskripsi' => 'Titik asap tebal terlihat dari tepi sungai.',
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('revisi_laporan', [
            'laporan_id' => $laporan->id,
            'nomor_revisi' => 1,
            'deskripsi' => 'Catatan awal sebelum ada baris revisi.',
        ]);
        $this->assertDatabaseHas('revisi_laporan', [
            'laporan_id' => $laporan->id,
            'nomor_revisi' => 2,
            'deskripsi' => 'Titik asap tebal terlihat dari tepi sungai.',
        ]);
    }

    public function test_validasi_menolak_input_tidak_sah_dan_foto_bukan_gambar(): void
    {
        $laporan = Laporan::factory()->perluPerbaikan()->create([
            'pelapor_id' => $this->pelapor->pengguna_id,
            'wilayah_id' => $this->wilayah->wilayah_id,
        ]);

        $berkasSalah = UploadedFile::fake()->create('dokumen.pdf', 100, 'application/pdf');

        $this->actingAs($this->pelapor)
            ->put(route('masyarakat.laporan.update', $laporan), [
                'kategori' => 'tidak_dikenal',
                'wilayah_id' => 999999,
                'latitude' => 95,
                'longitude' => -200,
                'deskripsi' => 'Pendek',
                'foto' => [$berkasSalah],
            ])
            ->assertSessionHasErrors(['kategori', 'wilayah_id', 'latitude', 'longitude', 'deskripsi', 'foto.0']);

        $this->assertSame(StatusLaporan::PERLU_PERBAIKAN->value, $laporan->fresh()->status);
        $this->assertDatabaseCount('revisi_laporan', 0);
    }

    public function test_total_foto_dipertahankan_dan_baru_melebihi_lima_ditolak(): void
    {
        $laporan = Laporan::factory()->perluPerbaikan()->create([
            'pelapor_id' => $this->pelapor->pengguna_id,
            'wilayah_id' => $this->wilayah->wilayah_id,
        ]);

        $revisi = RevisiLaporan::factory()->create(['laporan_id' => $laporan->id, 'nomor_revisi' => 1]);
        $fotoIds = [];

        foreach (range(1, 3) as $i) {
            $media = BerkasMedia::factory()->create(['diunggah_oleh' => $this->pelapor->pengguna_id]);
            $revisi->foto()->attach($media->id, ['urutan_tampilan' => $i]);
            $fotoIds[] = $media->id;
        }

        $fotoBaru = [
            $this->gambarPalsu('a.png'),
            $this->gambarPalsu('b.png'),
            $this->gambarPalsu('c.png'),
        ];

        $this->actingAs($this->pelapor)
            ->put(route('masyarakat.laporan.update', $laporan), [
                'kategori' => KategoriLaporan::DEFORESTASI->value,
                'wilayah_id' => $this->wilayah->wilayah_id,
                'latitude' => -0.5,
                'longitude' => 101.5,
                'deskripsi' => 'Uraian laporan yang lengkap dan jelas.',
                'pertahankan_foto' => $fotoIds,
                'foto' => $fotoBaru,
            ])
            ->assertSessionHasErrors('foto');
    }

    public function test_service_menolak_laporan_terkunci_dengan_exception(): void
    {
        $laporan = Laporan::factory()->status(StatusLaporan::DALAM_INSPEKSI)->create([
            'pelapor_id' => $this->pelapor->pengguna_id,
            'wilayah_id' => $this->wilayah->wilayah_id,
        ]);

        $this->expectException(LaporanTerkunciException::class);

        app(LaporanPerbaikanService::class)->kirimPerbaikan($laporan, $this->pelapor, [
            'kategori' => KategoriLaporan::DEFORESTASI->value,
            'wilayah_id' => $this->wilayah->wilayah_id,
            'latitude' => -0.5,
            'longitude' => 101.5,
            'deskripsi' => 'Coba ubah laporan yang sedang diinspeksi.',
        ]);
    }
}
