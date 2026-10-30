<?php

namespace Tests\Feature;

use App\Enums\AksiRiwayat;
use App\Exceptions\MetadataBelumLengkapException;
use App\Models\Pengguna;
use App\Models\RiwayatSumberData;
use App\Models\SumberData;
use App\Services\SumberDataService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class SumberDataServiceTest extends TestCase
{
    use RefreshDatabase;

    private SumberDataService $service;

    private Pengguna $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(SumberDataService::class);
        $this->admin = Pengguna::factory()->admin()->create();
    }

    /**
     * @param  array<string, mixed>  $ganti
     * @return array<string, mixed>
     */
    private function dataLengkap(array $ganti = []): array
    {
        return array_merge([
            'nama_sumber' => 'Hotspot NASA FIRMS',
            'jenis_sumber' => SumberData::JENIS_EKSTERNAL,
            'nama_penyedia' => 'NASA',
            'url_sumber' => 'https://firms.modaps.eosdis.nasa.gov/',
            'nama_lisensi' => 'Public Domain',
            'format_data' => 'CSV',
            'deskripsi_cakupan' => 'Titik panas global',
            'periode_mulai' => '2020-01-01',
            'periode_selesai' => '2024-12-31',
            'kode_crs' => 'EPSG:4326',
            'interval_penyegaran_jam' => 24,
            'status_kualitas' => 'VERIFIED',
        ], $ganti);
    }

    public function test_simpan_membuat_dataset_nonaktif_dan_riwayat_dibuat(): void
    {
        $sumber = $this->service->simpan($this->dataLengkap(), $this->admin);

        $this->assertFalse($sumber->is_aktif);
        $this->assertSame($this->admin->pengguna_id, $sumber->dibuat_oleh);

        $riwayat = $sumber->riwayat()->first();

        $this->assertSame(AksiRiwayat::DIBUAT, $riwayat->aksi);
        $this->assertSame($this->admin->pengguna_id, $riwayat->pengguna_id);
        $this->assertSame('Hotspot NASA FIRMS', $riwayat->perubahan['nama_sumber']['baru']);
        $this->assertNull($riwayat->perubahan['nama_sumber']['lama']);
    }

    public function test_simpan_mengabaikan_is_aktif_dan_dibuat_oleh_dari_payload(): void
    {
        $sumber = $this->service->simpan(
            $this->dataLengkap(['is_aktif' => true, 'dibuat_oleh' => 999]),
            $this->admin,
        );

        $this->assertFalse($sumber->is_aktif);
        $this->assertSame($this->admin->pengguna_id, $sumber->dibuat_oleh);
    }

    public function test_perbarui_hanya_mencatat_field_yang_berubah(): void
    {
        $sumber = $this->service->simpan($this->dataLengkap(), $this->admin);

        $this->service->perbarui($sumber, [
            'nama_penyedia' => 'NASA FIRMS',
            'format_data' => 'CSV',
        ], $this->admin);

        $riwayat = $sumber->riwayat()->first();

        $this->assertSame(AksiRiwayat::DIPERBARUI, $riwayat->aksi);
        $this->assertSame(['nama_penyedia'], array_keys($riwayat->perubahan));
        $this->assertSame('NASA', $riwayat->perubahan['nama_penyedia']['lama']);
        $this->assertSame('NASA FIRMS', $riwayat->perubahan['nama_penyedia']['baru']);
    }

    public function test_perbarui_tanpa_perubahan_tidak_menambah_riwayat(): void
    {
        $sumber = $this->service->simpan($this->dataLengkap(), $this->admin);
        $diperbaruiSebelumnya = $sumber->diperbarui_pada;

        $this->service->perbarui($sumber, $this->dataLengkap(), $this->admin);

        $segar = $sumber->fresh();

        $this->assertSame(1, $segar->riwayat()->count());
        $this->assertEquals($diperbaruiSebelumnya, $segar->diperbarui_pada);
    }

    public function test_riwayat_tidak_mencatat_kolom_diperbarui_pada(): void
    {
        $sumber = $this->service->simpan($this->dataLengkap(), $this->admin);

        $this->service->perbarui($sumber, ['nama_penyedia' => 'NASA FIRMS'], $this->admin);

        $this->assertArrayNotHasKey('diperbarui_pada', $sumber->riwayat()->first()->perubahan);
        $this->assertArrayNotHasKey('is_aktif', $sumber->riwayat()->first()->perubahan);
    }

    public function test_perbarui_menolak_dataset_aktif_menjadi_tidak_lengkap(): void
    {
        $sumber = $this->service->simpan($this->dataLengkap(), $this->admin);
        $this->service->aktifkan($sumber, $this->admin);

        try {
            $this->service->perbarui($sumber->fresh(), ['url_sumber' => null], $this->admin);
            $this->fail('Dataset aktif seharusnya tidak boleh menjadi tidak lengkap.');
        } catch (MetadataBelumLengkapException $e) {
            $this->assertContains('url_sumber', $e->field);
        }

        $segar = $sumber->fresh();

        $this->assertTrue($segar->is_aktif);
        $this->assertSame('https://firms.modaps.eosdis.nasa.gov/', $segar->url_sumber);
        $this->assertSame(2, $segar->riwayat()->count());
    }

    public function test_aktifkan_ditolak_bila_metadata_belum_lengkap(): void
    {
        $sumber = $this->service->simpan(
            $this->dataLengkap(['url_sumber' => null, 'periode_mulai' => null]),
            $this->admin,
        );

        try {
            $this->service->aktifkan($sumber, $this->admin);
            $this->fail('Aktivasi seharusnya ditolak.');
        } catch (MetadataBelumLengkapException $e) {
            $this->assertEqualsCanonicalizing(['url_sumber', 'periode_mulai'], $e->field);
        }

        $segar = $sumber->fresh();

        $this->assertFalse($segar->is_aktif);
        $this->assertSame(1, $segar->riwayat()->count());
    }

    public function test_aktifkan_berhasil_dan_mencatat_riwayat(): void
    {
        $sumber = $this->service->simpan($this->dataLengkap(), $this->admin);

        $this->service->aktifkan($sumber, $this->admin);

        $segar = $sumber->fresh();
        $riwayat = $segar->riwayat()->first();

        $this->assertTrue($segar->is_aktif);
        $this->assertSame(AksiRiwayat::DIAKTIFKAN, $riwayat->aksi);
        $this->assertEquals(['lama' => false, 'baru' => true], $riwayat->perubahan['is_aktif']);
    }

    public function test_aktifkan_idempoten(): void
    {
        $sumber = $this->service->simpan($this->dataLengkap(), $this->admin);
        $this->service->aktifkan($sumber, $this->admin);

        $jumlahRiwayat = $sumber->riwayat()->count();

        $this->service->aktifkan($sumber->fresh(), $this->admin);

        $this->assertSame($jumlahRiwayat, $sumber->riwayat()->count());
    }

    public function test_nonaktifkan_menyimpan_alasan_dan_tidak_menghapus_data(): void
    {
        $sumber = $this->service->simpan($this->dataLengkap(), $this->admin);
        $this->service->aktifkan($sumber, $this->admin);

        $this->service->nonaktifkan($sumber->fresh(), $this->admin, 'Lisensi dicabut');

        $segar = $sumber->fresh();
        $riwayat = $segar->riwayat()->first();

        $this->assertFalse($segar->is_aktif);
        $this->assertSame('Hotspot NASA FIRMS', $segar->nama_sumber);
        $this->assertSame(AksiRiwayat::DINONAKTIFKAN, $riwayat->aksi);
        $this->assertSame('Lisensi dicabut', $riwayat->catatan);
    }

    public function test_nonaktifkan_idempoten(): void
    {
        $sumber = $this->service->simpan($this->dataLengkap(), $this->admin);

        $jumlahRiwayat = $sumber->riwayat()->count();

        $this->service->nonaktifkan($sumber->fresh(), $this->admin);

        $this->assertSame($jumlahRiwayat, $sumber->riwayat()->count());
        $this->assertFalse($sumber->fresh()->is_aktif);
    }

    public function test_transaksi_dibatalkan_bila_pencatatan_riwayat_gagal(): void
    {
        RiwayatSumberData::creating(function (): void {
            throw new RuntimeException('gagal mencatat riwayat');
        });

        try {
            $this->service->simpan($this->dataLengkap(), $this->admin);
            $this->fail('Penyimpanan seharusnya gagal.');
        } catch (RuntimeException $e) {
            $this->assertSame('gagal mencatat riwayat', $e->getMessage());
        } finally {
            RiwayatSumberData::flushEventListeners();
        }

        $this->assertSame(0, SumberData::count());
        $this->assertSame(0, RiwayatSumberData::count());
    }
}
