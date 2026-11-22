<?php

namespace Tests\Feature\Admin;

use App\Models\Kawasan;
use App\Models\Pengguna;
use App\Models\Wilayah;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Group;
use RuntimeException;
use Tests\TestCase;
use Throwable;

/**
 * Pengujian filter bounding box pada direktori kawasan.
 *
 * Kelas ini sengaja tidak memakai RefreshDatabase karena trait tersebut
 * menjalankan migrate:fresh (db:wipe) yang dapat menghapus tabel milik
 * ekstensi PostGIS. Semua data uji dibuat di dalam transaksi dan di-rollback.
 */
#[Group('spasial')]
class FilterSpasialKawasanTest extends TestCase
{
    use DatabaseTransactions;

    private const DATABASE_UJI = 'rimbawatch_test';

    private bool $databaseTerverifikasi = false;

    /**
     * Dipanggil tepat sebelum setUpTraits(), sehingga guard berjalan sebelum
     * DatabaseTransactions membuka transaksi pada database yang mungkin salah.
     *
     * @return void
     */
    protected function refreshApplication()
    {
        parent::refreshApplication();

        $this->pastikanDatabasePengujian();

        $this->databaseTerverifikasi = true;
    }

    protected function setUp(): void
    {
        parent::setUp();

        // Jaring pengaman bila aplikasi sudah ada dan refreshApplication terlewat.
        if (! $this->databaseTerverifikasi) {
            $this->pastikanDatabasePengujian();
        }
    }

    public function test_koneksi_pengujian_menunjuk_rimbawatch_test(): void
    {
        $this->assertSame(self::DATABASE_UJI, DB::connection()->getDatabaseName());
        $this->assertSame(self::DATABASE_UJI, $this->namaDatabaseAktif());
        $this->assertNotSame('', (string) DB::selectOne('select postgis_version() as versi')->versi);
    }

    public function test_kawasan_di_dalam_bbox_ditampilkan_dan_di_luar_tidak(): void
    {
        $admin = Pengguna::factory()->admin()->create();

        $dalam = $this->buatKawasan(['nama_kawasan' => 'Kawasan Dalam Area']);
        $luar = $this->buatKawasan(['nama_kawasan' => 'Kawasan Luar Area']);

        $this->tambahBatas($dalam, 1, 106.70, -6.30, 106.80, -6.20);
        $this->tambahBatas($luar, 1, 110.00, -7.00, 110.10, -6.90);

        $this->actingAs($admin)
            ->get(route('admin.kawasan.index', $this->bbox(106.60, -6.40, 106.90, -6.10)))
            ->assertOk()
            ->assertSee('Area Koordinat (Bounding Box)')
            ->assertSee('Kawasan Dalam Area')
            ->assertDontSee('Kawasan Luar Area');
    }

    public function test_kawasan_yang_beririsan_sebagian_dengan_bbox_tetap_ditampilkan(): void
    {
        $admin = Pengguna::factory()->admin()->create();

        $kawasan = $this->buatKawasan(['nama_kawasan' => 'Kawasan Beririsan']);
        // Batas hanya melewati tepi kanan bbox, tidak sepenuhnya di dalam.
        $this->tambahBatas($kawasan, 1, 106.80, -6.35, 107.00, -6.25);

        $this->actingAs($admin)
            ->get(route('admin.kawasan.index', $this->bbox(106.70, -6.40, 106.85, -6.20)))
            ->assertOk()
            ->assertSee('Kawasan Beririsan');
    }

    public function test_kawasan_tanpa_geometri_tidak_muncul_saat_filter_bbox(): void
    {
        $admin = Pengguna::factory()->admin()->create();

        $tanpaBatas = $this->buatKawasan(['nama_kawasan' => 'Kawasan Tanpa Batas']);
        $berbatas = $this->buatKawasan(['nama_kawasan' => 'Kawasan Punya Batas']);

        $this->tambahBatas($berbatas, 1, 106.70, -6.30, 106.80, -6.20);

        $this->actingAs($admin)
            ->get(route('admin.kawasan.index', $this->bbox(106.60, -6.40, 106.90, -6.10)))
            ->assertOk()
            ->assertSee('Kawasan Punya Batas')
            ->assertDontSee($tanpaBatas->nama_kawasan)
            ->assertDontSee('Filter area belum bisa digunakan karena belum ada data batas kawasan.');
    }

    public function test_filter_memakai_versi_batas_terbaru(): void
    {
        $admin = Pengguna::factory()->admin()->create();

        // Versi 1 di dalam bbox, versi 2 di luar: tidak boleh tampil.
        $versiLamaDalam = $this->buatKawasan(['nama_kawasan' => 'Kawasan Versi Lama Dalam']);
        $this->tambahBatas($versiLamaDalam, 1, 106.70, -6.30, 106.80, -6.20);
        $this->tambahBatas($versiLamaDalam, 2, 110.00, -7.00, 110.10, -6.90);

        // Versi 1 di luar bbox, versi 2 di dalam: harus tampil.
        $versiBaruDalam = $this->buatKawasan(['nama_kawasan' => 'Kawasan Versi Baru Dalam']);
        $this->tambahBatas($versiBaruDalam, 1, 110.20, -7.10, 110.30, -7.00);
        $this->tambahBatas($versiBaruDalam, 2, 106.70, -6.30, 106.80, -6.20);

        $this->actingAs($admin)
            ->get(route('admin.kawasan.index', $this->bbox(106.60, -6.40, 106.90, -6.10)))
            ->assertOk()
            ->assertSee('Kawasan Versi Baru Dalam')
            ->assertDontSee('Kawasan Versi Lama Dalam');
    }

    public function test_bbox_dapat_dikombinasikan_dengan_pencarian_dan_filter_atribut(): void
    {
        $admin = Pengguna::factory()->admin()->create();

        $cocok = $this->buatKawasan([
            'nama_kawasan' => 'Kawasan Alfa Hutan',
            'jenis_kawasan' => 'HUTAN_LINDUNG',
            'is_aktif' => true,
        ]);
        $this->tambahBatas($cocok, 1, 106.70, -6.30, 106.80, -6.20);

        $jenisLain = $this->buatKawasan([
            'nama_kawasan' => 'Kawasan Alfa Taman',
            'jenis_kawasan' => 'TAMAN_NASIONAL',
            'is_aktif' => true,
        ]);
        $this->tambahBatas($jenisLain, 1, 106.70, -6.30, 106.80, -6.20);

        $nonaktif = $this->buatKawasan([
            'nama_kawasan' => 'Kawasan Alfa Nonaktif',
            'jenis_kawasan' => 'HUTAN_LINDUNG',
            'is_aktif' => false,
        ]);
        $this->tambahBatas($nonaktif, 1, 106.70, -6.30, 106.80, -6.20);

        $diLuar = $this->buatKawasan([
            'nama_kawasan' => 'Kawasan Alfa Luar',
            'jenis_kawasan' => 'HUTAN_LINDUNG',
            'is_aktif' => true,
        ]);
        $this->tambahBatas($diLuar, 1, 110.00, -7.00, 110.10, -6.90);

        $this->actingAs($admin)
            ->get(route('admin.kawasan.index', array_merge(
                $this->bbox(106.60, -6.40, 106.90, -6.10),
                [
                    'cari' => 'Alfa',
                    'jenis_kawasan' => 'HUTAN_LINDUNG',
                    'status_data' => 'aktif',
                ],
            )))
            ->assertOk()
            ->assertSee('Kawasan Alfa Hutan')
            ->assertDontSee('Kawasan Alfa Taman')
            ->assertDontSee('Kawasan Alfa Nonaktif')
            ->assertDontSee('Kawasan Alfa Luar');
    }

    public function test_bbox_tanpa_batas_kawasan_menampilkan_pesan_khusus(): void
    {
        $admin = Pengguna::factory()->admin()->create();
        $this->buatKawasan(['nama_kawasan' => 'Kawasan Tanpa Batas Sama Sekali']);

        $this->actingAs($admin)
            ->get(route('admin.kawasan.index', $this->bbox(106.70, -6.40, 106.90, -6.10)))
            ->assertOk()
            ->assertSee('Filter area belum bisa digunakan karena belum ada data batas kawasan.')
            ->assertDontSee('Kawasan Tanpa Batas Sama Sekali');
    }

    public function test_bbox_tidak_lengkap_menghasilkan_error_validasi(): void
    {
        $admin = Pengguna::factory()->admin()->create();

        $this->actingAs($admin)
            ->from(route('admin.kawasan.index'))
            ->get(route('admin.kawasan.index', ['min_lon' => 106.7]))
            ->assertRedirect(route('admin.kawasan.index'))
            ->assertSessionHasErrors('bbox');
    }

    public function test_bbox_dengan_batas_terbalik_menghasilkan_error_validasi(): void
    {
        $admin = Pengguna::factory()->admin()->create();

        $this->actingAs($admin)
            ->from(route('admin.kawasan.index'))
            ->get(route('admin.kawasan.index', $this->bbox(106.90, -6.10, 106.70, -6.40)))
            ->assertRedirect(route('admin.kawasan.index'))
            ->assertSessionHasErrors(['max_lon', 'max_lat']);
    }

    public function test_koordinat_di_luar_rentang_menghasilkan_error_validasi(): void
    {
        $admin = Pengguna::factory()->admin()->create();

        $this->actingAs($admin)
            ->from(route('admin.kawasan.index'))
            ->get(route('admin.kawasan.index', $this->bbox(200.0, -95.0, 210.0, 95.0)))
            ->assertRedirect(route('admin.kawasan.index'))
            ->assertSessionHasErrors(['min_lon', 'min_lat', 'max_lon', 'max_lat']);
    }

    public function test_pagination_mempertahankan_parameter_bbox(): void
    {
        $admin = Pengguna::factory()->admin()->create();

        foreach (range(1, 16) as $nomor) {
            $kawasan = $this->buatKawasan([
                'nama_kawasan' => sprintf('Kawasan Spasial %02d', $nomor),
            ]);
            $this->tambahBatas($kawasan, 1, 106.70, -6.30, 106.80, -6.20);
        }

        $halamanPertama = $this->actingAs($admin)
            ->get(route('admin.kawasan.index', $this->bbox(106.60, -6.40, 106.90, -6.10)))
            ->assertOk()
            ->assertSee('Kawasan Spasial 01')
            ->assertSee('Kawasan Spasial 15')
            ->assertDontSee('Kawasan Spasial 16');

        // Tautan halaman berikutnya harus tetap membawa parameter bounding box.
        $halamanPertama->assertSee('min_lon=106.6', false);

        $this->actingAs($admin)
            ->get(route('admin.kawasan.index', array_merge(
                $this->bbox(106.60, -6.40, 106.90, -6.10),
                ['page' => 2],
            )))
            ->assertOk()
            ->assertSee('Kawasan Spasial 16')
            ->assertDontSee('Kawasan Spasial 01');
    }

    /**
     * Menolak berjalan pada database selain rimbawatch_test, memastikan
     * PostGIS aktif, dan memastikan skema sudah dimigrasi.
     *
     * @throws RuntimeException
     */
    private function pastikanDatabasePengujian(): void
    {
        $koneksi = DB::connection();

        if ($koneksi->getDriverName() !== 'pgsql') {
            $this->markTestSkipped('Test spasial dilewati: koneksi aktif bukan PostgreSQL/PostGIS.');
        }

        $terkonfigurasi = $koneksi->getDatabaseName();

        if ($terkonfigurasi !== self::DATABASE_UJI) {
            throw new RuntimeException($this->pesanDatabaseSalah((string) $terkonfigurasi));
        }

        try {
            $namaAktif = $this->namaDatabaseAktif();
        } catch (Throwable $e) {
            throw new RuntimeException(
                'Database '.self::DATABASE_UJI.' belum tersedia atau tidak dapat dihubungi. '.
                'Ikuti prosedur persiapan database testing sebelum menjalankan test spasial.',
                0,
                $e
            );
        }

        if ($namaAktif !== self::DATABASE_UJI) {
            throw new RuntimeException($this->pesanDatabaseSalah($namaAktif));
        }

        try {
            DB::selectOne('select postgis_version() as versi');
        } catch (Throwable $e) {
            throw new RuntimeException(
                'PostGIS belum aktif pada '.self::DATABASE_UJI.'. '.
                'Jalankan CREATE EXTENSION postgis pada database testing.',
                0,
                $e
            );
        }

        foreach (['kawasan', 'batas_kawasan'] as $tabel) {
            if (! Schema::hasTable($tabel)) {
                throw new RuntimeException(
                    'Skema '.self::DATABASE_UJI.' belum siap: tabel ['.$tabel.'] tidak ditemukan. '.
                    'Jalankan php artisan migrate pada database testing.'
                );
            }
        }
    }

    private function pesanDatabaseSalah(string $nama): string
    {
        return 'Dibatalkan: test spasial hanya boleh berjalan pada database '.self::DATABASE_UJI.
            ', tetapi koneksi mengarah ke ['.$nama.']. '.
            'Database utama tidak boleh dipakai untuk pengujian.';
    }

    private function namaDatabaseAktif(): string
    {
        return (string) DB::selectOne('select current_database() as nama')->nama;
    }

    /**
     * Pertahanan terakhir tepat sebelum menulis data geometri.
     */
    private function pastikanTargetTulis(): void
    {
        $this->assertSame(
            self::DATABASE_UJI,
            $this->namaDatabaseAktif(),
            'Penulisan dibatalkan: database aktif bukan '.self::DATABASE_UJI.'.'
        );
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function buatKawasan(array $attributes = []): Kawasan
    {
        return Kawasan::create(array_merge([
            'wilayah_id' => $this->buatWilayah()->wilayah_id,
            'nama_kawasan' => 'Kawasan Uji',
            'jenis_kawasan' => 'HUTAN_LINDUNG',
            'status_kawasan' => 'AKTIF',
        ], $attributes));
    }

    private function buatWilayah(string $nama = 'Blok Uji'): Wilayah
    {
        return Wilayah::create([
            'nama_wilayah' => $nama,
            'kode_wilayah' => 'KW-'.fake()->unique()->numerify('#####'),
            'tingkat_wilayah' => 'BLOK',
        ]);
    }

    private function tambahBatas(
        Kawasan $kawasan,
        int $versi,
        float $minLon,
        float $minLat,
        float $maxLon,
        float $maxLat,
    ): void {
        $this->pastikanTargetTulis();

        DB::statement(
            'insert into batas_kawasan (kawasan_id, nomor_versi, data_geometri, kode_crs, status_validasi)
             values (?, ?, ST_MakeEnvelope(?::double precision, ?::double precision, '.
            '?::double precision, ?::double precision, 4326), ?, ?)',
            [$kawasan->kawasan_id, $versi, $minLon, $minLat, $maxLon, $maxLat, 'EPSG:4326', 'VALID']
        );
    }

    /**
     * @return array{min_lon: float, min_lat: float, max_lon: float, max_lat: float}
     */
    private function bbox(float $minLon, float $minLat, float $maxLon, float $maxLat): array
    {
        return [
            'min_lon' => $minLon,
            'min_lat' => $minLat,
            'max_lon' => $maxLon,
            'max_lat' => $maxLat,
        ];
    }
}
