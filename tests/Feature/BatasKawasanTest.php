<?php

namespace Tests\Feature;

use App\Models\Pengguna;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class BatasKawasanTest extends TestCase
{
    use RefreshDatabase;

    private Pengguna $admin;
    private Pengguna $masyarakat;
    private int $kawasanId;
    private int $sumberId;

    protected function setUp(): void
    {
        parent::setUp();

       $this->admin = Pengguna::factory()->admin()->create();
$this->masyarakat = Pengguna::factory()->masyarakat()->create();

$adminId = $this->admin->pengguna_id;

        $wilayahId = DB::table('wilayah')->insertGetId([
            'kode_wilayah' => 'TEST-PBI02',
            'nama_wilayah' => 'Wilayah Uji PBI-02',
            'tingkat_wilayah' => 'KABUPATEN',
            'is_aktif' => true,
            'dibuat_pada' => now(),
        ], 'wilayah_id');

        $this->sumberId = DB::table('sumber_data')->insertGetId([
            'nama_sumber' => 'Sumber Uji GeoJSON',
            'nama_penyedia' => 'Tim GIS',
            'url_sumber' => 'https://rimbawatch.org/test',
            'nama_lisensi' => 'Open Data',
            'format_data' => 'GeoJSON',
            'deskripsi_cakupan' => 'Uji otomatis',
            'kode_crs' => 'EPSG:4326',
            'status_kualitas' => 'TERVERIFIKASI',
            'terakhir_diambil_pada' => now(),
            'is_aktif' => true,
            'dibuat_oleh' => $adminId,
            'dibuat_pada' => now(),
        ], 'sumber_id');

        $this->kawasanId = DB::table('kawasan')->insertGetId([
            'wilayah_id' => $wilayahId,
            'nama_kawasan' => 'Kawasan Uji Cendana',
            'jenis_kawasan' => 'Hutan Lindung',
            'status_kawasan' => 'AKTIF',
            'sumber_id' => $this->sumberId,
            'is_aktif' => true,
            'dibuat_oleh' => $adminId,
            'dibuat_pada' => now(),
        ], 'kawasan_id');
    }

    public function test_admin_dapat_membuka_halaman_digitasi_batas_kawasan(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.kawasan.batas.index', $this->kawasanId));

        $response->assertStatus(200);
        $response->assertSee('Digitasi Batas Kawasan');
        $response->assertSee('Kawasan Uji Cendana');
    }

    public function test_non_admin_ditolak_mengakses_halaman_batas_kawasan(): void
    {
        $response = $this->actingAs($this->masyarakat)
            ->get(route('admin.kawasan.batas.index', $this->kawasanId));

        $response->assertStatus(403);
    }

    public function test_digitasi_dan_impor_geojson_valid_tersimpan_sebagai_versi_berurutan(): void
    {
        $polygonDigitasi = json_encode([
            'type' => 'Polygon',
            'coordinates' => [[
                [101.440, -0.500],
                [101.450, -0.500],
                [101.450, -0.510],
                [101.440, -0.510],
                [101.440, -0.500],
            ]],
        ]);

        $res1 = $this->actingAs($this->admin)
            ->post(route('admin.kawasan.batas.digitasi', $this->kawasanId), [
                'geojson_digitasi' => $polygonDigitasi,
                'sumber_id' => $this->sumberId,
                'mode_simpan' => 'final',
            ]);

        $res1->assertRedirect(route('admin.kawasan.batas.index', $this->kawasanId));
        $res1->assertSessionHas('success');

        $geojsonImpor = json_encode([
            'type' => 'FeatureCollection',
            'features' => [[
                'type' => 'Feature',
                'properties' => ['nama' => 'Revisi Batas'],
                'geometry' => [
                    'type' => 'Polygon',
                    'coordinates' => [[
                        [101.441, -0.501],
                        [101.452, -0.501],
                        [101.452, -0.512],
                        [101.441, -0.512],
                        [101.441, -0.501],
                    ]],
                ],
            ]],
        ]);

        $file = UploadedFile::fake()->createWithContent('batas_baru.geojson', $geojsonImpor);

        $res2 = $this->actingAs($this->admin)
            ->post(route('admin.kawasan.batas.impor', $this->kawasanId), [
                'file_geojson' => $file,
                'sumber_id' => $this->sumberId,
            ]);

        $res2->assertRedirect(route('admin.kawasan.batas.index', $this->kawasanId));
        $res2->assertSessionHas('success');

        $versiTersimpan = DB::table('batas_kawasan')
            ->where('kawasan_id', $this->kawasanId)
            ->orderBy('nomor_versi')
            ->pluck('nomor_versi')
            ->toArray();

        $this->assertEquals([1, 2], $versiTersimpan);
    }

    public function test_geometri_self_intersecting_dan_file_rusak_ditolak_tanpa_data_parsial(): void
    {
        // Poligon menyilang membentuk angka 8 (self-intersecting)
        $poligonMenyilang = json_encode([
            'type' => 'Polygon',
            'coordinates' => [[
                [101.440, -0.500],
                [101.450, -0.510],
                [101.450, -0.500],
                [101.440, -0.510],
                [101.440, -0.500],
            ]],
        ]);

        $resSilang = $this->actingAs($this->admin)
            ->post(route('admin.kawasan.batas.digitasi', $this->kawasanId), [
                'geojson_digitasi' => $poligonMenyilang,
                'sumber_id' => $this->sumberId,
            ]);

        $resSilang->assertSessionHas('error');

        // File korup / bukan JSON valid
        $fileKorup = UploadedFile::fake()->createWithContent('rusak.geojson', 'INI BUKAN FILE GEOJSON');

        $resKorup = $this->actingAs($this->admin)
            ->post(route('admin.kawasan.batas.impor', $this->kawasanId), [
                'file_geojson' => $fileKorup,
                'sumber_id' => $this->sumberId,
            ]);

        $resKorup->assertSessionHas('error');

        $this->assertEquals(
            0,
            DB::table('batas_kawasan')->where('kawasan_id', $this->kawasanId)->count()
        );
    }
}
