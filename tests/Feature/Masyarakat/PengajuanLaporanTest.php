<?php

namespace Tests\Feature\Masyarakat;

use App\Models\Pengguna;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PengajuanLaporanTest extends TestCase
{
    use RefreshDatabase;

    private function dataValid(array $ganti = []): array
    {
        return array_merge([
            'kategori' => 'deforestasi',
            'deskripsi' => 'Ada penebangan pohon besar-besaran di tepi hutan lindung.',
            'wilayah_id' => $this->buatWilayah(),
            'latitude' => '-6.2000000',
            'longitude' => '106.8166660',
            'insiden_pada' => now()->subHour()->format('Y-m-d\TH:i'),
            'foto' => [
                UploadedFile::fake()->image('foto1.jpg'),
            ],
        ], $ganti);
    }

    public function test_tamu_diarahkan_ke_login(): void
    {
        $this->get(route('masyarakat.laporan.create'))
            ->assertRedirect(route('login'));
    }

    public function test_masyarakat_bisa_membuka_form(): void
    {
        $warga = Pengguna::factory()->masyarakat()->create();

        $this->actingAs($warga)
            ->get(route('masyarakat.laporan.create'))
            ->assertOk()
            ->assertSee('Ajukan Laporan Kerusakan Hutan');
    }

    private function buatWilayah(): int
    {
    return DB::table('wilayah')->insertGetId([
        'nama_wilayah' => 'Wilayah Pengujian',
        'tingkat_wilayah' => 'KABUPATEN',
        'is_aktif' => true,
        'dibuat_pada' => now(),
    ], 'wilayah_id');
    }

    public function test_peran_lain_tidak_boleh_membuka_atau_mengirim(): void
    {
        $admin = Pengguna::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('masyarakat.laporan.create'))
            ->assertForbidden();

        $this->actingAs($admin)
            ->post(route('masyarakat.laporan.store'), $this->dataValid())
            ->assertForbidden();

        $this->assertSame(0, DB::table('laporan')->count());
    }

    public function test_pengajuan_valid_menyimpan_laporan_revisi_peristiwa_dan_berkas(): void
    {
        Storage::fake('local');

        $warga = Pengguna::factory()->masyarakat()->create();

        $this->actingAs($warga)
            ->post(route('masyarakat.laporan.store'), $this->dataValid())
            ->assertRedirect(route('masyarakat.dashboard'))
            ->assertSessionHas('status');

        $laporan = DB::table('laporan')->first();

        $this->assertNotNull($laporan);
        $this->assertSame($warga->pengguna_id, (int) $laporan->pelapor_id);
        $this->assertSame('MENUNGGU_VERIFIKASI', $laporan->status_laporan);
        $this->assertStringStartsWith('LAP-', $laporan->kode_laporan);
        $this->assertNotNull($laporan->diserahkan_pada);

        $this->assertDatabaseHas('revisi_laporan', [
            'laporan_id' => $laporan->laporan_id,
            'nomor_revisi' => 1,
            'diedit_oleh' => $warga->pengguna_id,
        ]);

        $this->assertDatabaseHas('peristiwa_laporan', [
            'laporan_id' => $laporan->laporan_id,
            'jenis_peristiwa' => 'DIAJUKAN',
            'status_baru' => 'MENUNGGU_VERIFIKASI',
        ]);

        $this->assertSame(1, DB::table('berkas_media')->count());
        $this->assertSame(1, DB::table('foto_revisi_laporan')->count());

        $berkas = DB::table('berkas_media')->first();

        $this->assertSame('PRIVATE', $berkas->tingkat_akses);

        Storage::disk('local')->assertExists(
            $berkas->kunci_penyimpanan
        );
    }

    public function test_video_opsional_ikut_tersimpan(): void
    {
        Storage::fake('local');

        $warga = Pengguna::factory()->masyarakat()->create();

        $video = UploadedFile::fake()->create(
            'rekaman.mp4',
            10,
            'video/mp4'
        );

        $this->actingAs($warga)
            ->post(route('masyarakat.laporan.store'), $this->dataValid([
                'video' => $video,
            ]))
            ->assertRedirect(route('masyarakat.dashboard'));

        $this->assertSame(2, DB::table('berkas_media')->count());
        $this->assertSame(2, DB::table('foto_revisi_laporan')->count());
    }

    public function test_foto_wajib_dan_tidak_menyimpan_apa_pun_bila_gagal(): void
    {
        Storage::fake('local');

        $warga = Pengguna::factory()->masyarakat()->create();

        $this->actingAs($warga)
            ->post(route('masyarakat.laporan.store'), $this->dataValid([
                'foto' => [],
            ]))
            ->assertSessionHasErrors('foto');

        $this->assertSame(0, DB::table('laporan')->count());
        $this->assertSame(0, DB::table('berkas_media')->count());
    }

    public function test_validasi_lokasi_kategori_dan_waktu(): void
    {
        Storage::fake('local');

        $warga = Pengguna::factory()->masyarakat()->create();

        $this->actingAs($warga)
            ->post(route('masyarakat.laporan.store'), $this->dataValid([
                'kategori' => 'ngawur',
                'latitude' => '123',
                'longitude' => '',
                'insiden_pada' => now()->addDay()->format('Y-m-d\TH:i'),
            ]))
            ->assertSessionHasErrors([
                'kategori',
                'latitude',
                'longitude',
                'insiden_pada',
            ]);

        $this->assertSame(0, DB::table('laporan')->count());
    }
}
