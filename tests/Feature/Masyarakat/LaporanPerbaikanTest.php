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

    public function test_pelapor_dapat_membuka_form_perbaikan_untuk_laporan_dikembalikan(): void
    {
        $laporan = Laporan::factory()->dikembalikan()->create([
            'pelapor_id' => $this->pelapor->pengguna_id,
            'wilayah_id' => $this->wilayah->wilayah_id,
        ]);

        PeristiwaLaporan::factory()->create([
            'laporan_id' => $laporan->laporan_id,
            'jenis_peristiwa' => 'DIKEMBALIKAN',
            'status_baru' => StatusLaporan::DIKEMBALIKAN->value,
            'catatan_peristiwa' => 'Koordinat masih berada di luar wilayah laporan.',
        ]);

        $this->actingAs($this->pelapor)
            ->get(route('masyarakat.laporan.edit', $laporan))
            ->assertOk()
            ->assertSee('Perbaiki Laporan #'.$laporan->kode_laporan)
            ->assertSee('Koordinat masih berada di luar wilayah laporan.');
    }

    public function test_perbaikan_membuat_revisi_baru_append_only_dan_mengirim_ulang_laporan(): void
    {
        $laporan = Laporan::factory()->dikembalikan()->create([
            'pelapor_id' => $this->pelapor->pengguna_id,
            'wilayah_id' => $this->wilayah->wilayah_id,
            'kategori' => KategoriLaporan::DEFORESTASI->value,
            'deskripsi' => 'Uraian awal yang masih terlalu singkat untuk verifikasi.',
        ]);

        $revisiAwal = RevisiLaporan::factory()->create([
            'laporan_id' => $laporan->laporan_id,
            'nomor_revisi' => 1,
            'diedit_oleh' => $this->pelapor->pengguna_id,
            'kategori' => KategoriLaporan::DEFORESTASI->value,
            'deskripsi' => 'Uraian awal yang masih terlalu singkat untuk verifikasi.',
        ]);

        $fotoLama = BerkasMedia::factory()->create([
            'nama_file_asli' => 'foto-awal.jpg',
            'kunci_penyimpanan' => 'laporan/awal/foto-awal.jpg',
            'diunggah_oleh' => $this->pelapor->pengguna_id,
        ]);
        Storage::disk('local')->put($fotoLama->kunci_penyimpanan, 'konten-lama');
        $revisiAwal->berkasMedia()->attach($fotoLama->berkas_media_id, ['urutan_tampilan' => 1]);

        $fotoBaru = $this->gambarPalsu('bukti-baru.png');

        $respons = $this->actingAs($this->pelapor)
            ->put(route('masyarakat.laporan.update', $laporan), [
                'kategori' => KategoriLaporan::ALIH_FUNGSI_LAHAN->value,
                'wilayah_id' => $this->wilayah->wilayah_id,
                'kawasan_id' => $this->kawasan->kawasan_id,
                'latitude' => -0.12345,
                'longitude' => 101.67891,
                'insiden_pada' => '2026-11-15T10:30',
                'deskripsi' => 'Pembukaan lahan perkebunan seluas sekitar 2 hektare di batas kawasan.',
                'alasan_revisi' => 'Menambahkan koordinat akurat dan foto kondisi terbaru.',
                'pertahankan_foto' => [$fotoLama->berkas_media_id],
                'foto' => [$fotoBaru],
            ]);

        $respons->assertSessionHasNoErrors();
        $respons->assertRedirect(route('masyarakat.laporan.show', $laporan));

        $laporanSegar = $laporan->fresh();
        $this->assertSame(StatusLaporan::MENUNGGU_VERIFIKASI->value, $laporanSegar->status_laporan);
        $this->assertSame(KategoriLaporan::ALIH_FUNGSI_LAHAN->value, $laporanSegar->kategori);
        $this->assertSame($this->kawasan->kawasan_id, $laporanSegar->kawasan_id);
        $this->assertSame('Pembukaan lahan perkebunan seluas sekitar 2 hektare di batas kawasan.', $laporanSegar->deskripsi);

        $koordinatBaru = $laporanSegar->koordinat();
        $this->assertEqualsWithDelta(-0.12345, $koordinatBaru['latitude'], 0.0001);
        $this->assertEqualsWithDelta(101.67891, $koordinatBaru['longitude'], 0.0001);

        // Revisi awal tidak tertimpa, revisi ke-2 terbuat
        $this->assertDatabaseCount('revisi_laporan', 2);
        $this->assertSame('Uraian awal yang masih terlalu singkat untuk verifikasi.', $revisiAwal->fresh()->deskripsi);

        $revisiKedua = $laporanSegar->revisi()->where('nomor_revisi', 2)->firstOrFail();
        $this->assertSame('Menambahkan koordinat akurat dan foto kondisi terbaru.', $revisiKedua->alasan_revisi);
        $this->assertSame($this->pelapor->pengguna_id, (int) $revisiKedua->diedit_oleh);
        $this->assertCount(2, $revisiKedua->berkasMedia);

        $mediaBaru = $revisiKedua->berkasMedia->firstWhere('nama_file_asli', 'bukti-baru.png');
        $this->assertNotNull($mediaBaru);
        Storage::disk('local')->assertExists($mediaBaru->kunci_penyimpanan);

        // Jejak peristiwa tercatat dengan status lama -> status baru dan aktor
        $peristiwa = $laporanSegar->peristiwa()->firstOrFail();
        $this->assertSame('PERBAIKAN_DIKIRIM', $peristiwa->jenis_peristiwa);
        $this->assertSame(StatusLaporan::DIKEMBALIKAN->value, $peristiwa->status_lama);
        $this->assertSame(StatusLaporan::MENUNGGU_VERIFIKASI->value, $peristiwa->status_baru);
        $this->assertSame($this->pelapor->pengguna_id, (int) $peristiwa->aktor_id);
    }

    public function test_integrasi_pengajuan_pbi08_dapat_dilacak_dan_diperbaiki_di_pbi09(): void
    {
        $this->actingAs($this->pelapor)
            ->post(route('masyarakat.laporan.store'), [
                'kategori' => KategoriLaporan::DEFORESTASI->value,
                'wilayah_id' => $this->wilayah->wilayah_id,
                'latitude' => -0.25,
                'longitude' => 101.55,
                'insiden_pada' => now()->subHours(3)->format('Y-m-d\TH:i'),
                'deskripsi' => 'Penebangan pohon di tepi jalan koridor hutan.',
                'foto' => [$this->gambarPalsu('awal.png')],
            ])
            ->assertRedirect(route('masyarakat.dashboard'));

        $laporan = Laporan::query()->firstOrFail();

        $this->actingAs($this->pelapor)
            ->get(route('masyarakat.laporan.index'))
            ->assertOk()
            ->assertSee($laporan->kode_laporan);

        // Simulasikan pengembalian oleh Pengawas (PBI-10)
        $laporan->forceFill(['status_laporan' => Laporan::STATUS_DIKEMBALIKAN])->save();

        $this->actingAs($this->pelapor)
            ->put(route('masyarakat.laporan.update', $laporan), [
                'kategori' => KategoriLaporan::DEFORESTASI->value,
                'wilayah_id' => $this->wilayah->wilayah_id,
                'latitude' => -0.26,
                'longitude' => 101.56,
                'insiden_pada' => now()->subHours(3)->format('Y-m-d\TH:i'),
                'deskripsi' => 'Penebangan pohon di tepi jalan koridor hutan sekitar 1 hektare.',
                'alasan_revisi' => 'Melengkapi estimasi luas area.',
            ])
            ->assertRedirect(route('masyarakat.laporan.show', $laporan));

        $this->assertSame(2, $laporan->revisi()->count());
        $this->assertSame(Laporan::STATUS_MENUNGGU_VERIFIKASI, $laporan->fresh()->status_laporan);
    }

    public function test_perbaikan_otomatis_menyimpan_snapshot_awal_bila_belum_ada_revisi(): void
    {
        $laporan = Laporan::factory()->dikembalikan()->create([
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
                'deskripsi' => 'Titik asap tebal terlihat dari tepi sungai pada pagi hari.',
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('revisi_laporan', [
            'laporan_id' => $laporan->laporan_id,
            'nomor_revisi' => 1,
            'deskripsi' => 'Catatan awal sebelum ada baris revisi.',
        ]);
        $this->assertDatabaseHas('revisi_laporan', [
            'laporan_id' => $laporan->laporan_id,
            'nomor_revisi' => 2,
            'deskripsi' => 'Titik asap tebal terlihat dari tepi sungai pada pagi hari.',
        ]);
    }

    public function test_validasi_menolak_input_tidak_sah_dan_foto_bukan_gambar(): void
    {
        $laporan = Laporan::factory()->dikembalikan()->create([
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

        $this->assertSame(StatusLaporan::DIKEMBALIKAN->value, $laporan->fresh()->status_laporan);
        $this->assertDatabaseCount('revisi_laporan', 0);
    }

    public function test_total_foto_dipertahankan_dan_baru_melebihi_lima_ditolak(): void
    {
        $laporan = Laporan::factory()->dikembalikan()->create([
            'pelapor_id' => $this->pelapor->pengguna_id,
            'wilayah_id' => $this->wilayah->wilayah_id,
        ]);

        $revisi = RevisiLaporan::factory()->create([
            'laporan_id' => $laporan->laporan_id,
            'nomor_revisi' => 1,
            'diedit_oleh' => $this->pelapor->pengguna_id,
        ]);
        $fotoIds = [];

        foreach (range(1, 3) as $i) {
            $media = BerkasMedia::factory()->create(['diunggah_oleh' => $this->pelapor->pengguna_id]);
            $revisi->berkasMedia()->attach($media->berkas_media_id, ['urutan_tampilan' => $i]);
            $fotoIds[] = $media->berkas_media_id;
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
                'deskripsi' => 'Uraian laporan yang lengkap dan jelas untuk diperiksa.',
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
            'deskripsi' => 'Coba ubah laporan yang sedang diinspeksi di lapangan.',
        ]);
    }
}
