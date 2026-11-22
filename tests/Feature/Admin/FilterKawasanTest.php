<?php

namespace Tests\Feature\Admin;

use App\Models\Kawasan;
use App\Models\Pengguna;
use App\Models\Wilayah;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\TestResponse;
use Mockery;
use Tests\TestCase;

class FilterKawasanTest extends TestCase
{
    use RefreshDatabase;

    public function test_pencarian_berdasarkan_nama_kawasan(): void
    {
        $admin = Pengguna::factory()->admin()->create();
        $this->buatKawasan(['nama_kawasan' => 'Kawasan Alfa']);
        $this->buatKawasan(['nama_kawasan' => 'Kawasan Beta']);

        $this->actingAs($admin)
            ->get(route('admin.kawasan.index', ['cari' => 'Alfa']))
            ->assertOk()
            ->assertSee('Kawasan Alfa')
            ->assertDontSee('Kawasan Beta');
    }

    public function test_pencarian_berdasarkan_nama_wilayah(): void
    {
        $admin = Pengguna::factory()->admin()->create();
        $utara = $this->buatWilayah('Blok Utara');
        $selatan = $this->buatWilayah('Blok Selatan');

        $this->buatKawasan([
            'wilayah_id' => $utara->wilayah_id,
            'nama_kawasan' => 'Kawasan Alfa',
        ]);
        $this->buatKawasan([
            'wilayah_id' => $selatan->wilayah_id,
            'nama_kawasan' => 'Kawasan Beta',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.kawasan.index', ['cari' => 'Blok Utara']))
            ->assertOk()
            ->assertSee('Kawasan Alfa')
            ->assertDontSee('Kawasan Beta');
    }

    public function test_filter_berdasarkan_wilayah(): void
    {
        $admin = Pengguna::factory()->admin()->create();
        $utara = $this->buatWilayah('Blok Utara');
        $selatan = $this->buatWilayah('Blok Selatan');

        $this->buatKawasan([
            'wilayah_id' => $utara->wilayah_id,
            'nama_kawasan' => 'Kawasan Alfa',
        ]);
        $this->buatKawasan([
            'wilayah_id' => $selatan->wilayah_id,
            'nama_kawasan' => 'Kawasan Beta',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.kawasan.index', ['wilayah_id' => $selatan->wilayah_id]))
            ->assertOk()
            ->assertSee('Kawasan Beta')
            ->assertDontSee('Kawasan Alfa');
    }

    public function test_filter_berdasarkan_jenis_kawasan(): void
    {
        $admin = Pengguna::factory()->admin()->create();
        $this->buatKawasan([
            'nama_kawasan' => 'Kawasan Alfa',
            'jenis_kawasan' => 'HUTAN_LINDUNG',
        ]);
        $this->buatKawasan([
            'nama_kawasan' => 'Kawasan Beta',
            'jenis_kawasan' => 'TAMAN_NASIONAL',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.kawasan.index', ['jenis_kawasan' => 'TAMAN_NASIONAL']))
            ->assertOk()
            ->assertSee('Kawasan Beta')
            ->assertDontSee('Kawasan Alfa');
    }

    public function test_filter_berdasarkan_status_kawasan(): void
    {
        $admin = Pengguna::factory()->admin()->create();
        $this->buatKawasan([
            'nama_kawasan' => 'Kawasan Alfa',
            'status_kawasan' => 'AKTIF',
        ]);
        $this->buatKawasan([
            'nama_kawasan' => 'Kawasan Beta',
            'status_kawasan' => 'DICADANGKAN',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.kawasan.index', ['status_kawasan' => 'DICADANGKAN']))
            ->assertOk()
            ->assertSee('Kawasan Beta')
            ->assertDontSee('Kawasan Alfa');
    }

    public function test_filter_berdasarkan_status_data_aktif(): void
    {
        $admin = Pengguna::factory()->admin()->create();
        $this->buatKawasan([
            'nama_kawasan' => 'Kawasan Alfa',
            'is_aktif' => true,
        ]);
        $this->buatKawasan([
            'nama_kawasan' => 'Kawasan Beta',
            'is_aktif' => false,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.kawasan.index', ['status_data' => 'aktif']))
            ->assertOk()
            ->assertSee('Kawasan Alfa')
            ->assertDontSee('Kawasan Beta');
    }

    public function test_filter_berdasarkan_status_data_nonaktif(): void
    {
        $admin = Pengguna::factory()->admin()->create();
        $this->buatKawasan([
            'nama_kawasan' => 'Kawasan Alfa',
            'is_aktif' => true,
        ]);
        $this->buatKawasan([
            'nama_kawasan' => 'Kawasan Beta',
            'is_aktif' => false,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.kawasan.index', ['status_data' => 'nonaktif']))
            ->assertOk()
            ->assertSee('Kawasan Beta')
            ->assertDontSee('Kawasan Alfa');
    }

    public function test_kombinasi_pencarian_dan_beberapa_filter(): void
    {
        $admin = Pengguna::factory()->admin()->create();
        $utara = $this->buatWilayah('Blok Utara');

        $this->buatKawasan([
            'wilayah_id' => $utara->wilayah_id,
            'nama_kawasan' => 'Kawasan Alfa',
            'jenis_kawasan' => 'HUTAN_LINDUNG',
            'is_aktif' => true,
        ]);
        $this->buatKawasan([
            'wilayah_id' => $utara->wilayah_id,
            'nama_kawasan' => 'Kawasan Alfa Cadangan',
            'jenis_kawasan' => 'HUTAN_LINDUNG',
            'is_aktif' => false,
        ]);
        $this->buatKawasan([
            'wilayah_id' => $utara->wilayah_id,
            'nama_kawasan' => 'Kawasan Beta',
            'jenis_kawasan' => 'TAMAN_NASIONAL',
            'is_aktif' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.kawasan.index', [
                'cari' => 'Alfa',
                'jenis_kawasan' => 'HUTAN_LINDUNG',
                'status_data' => 'aktif',
            ]))
            ->assertOk()
            ->assertSee('Kawasan Alfa')
            ->assertDontSee('Kawasan Alfa Cadangan')
            ->assertDontSee('Kawasan Beta');
    }

    public function test_opsi_jenis_dan_status_diambil_dari_data_aktual(): void
    {
        $admin = Pengguna::factory()->admin()->create();
        $this->buatKawasan([
            'jenis_kawasan' => 'HUTAN_MANGROVE',
            'status_kawasan' => 'DICADANGKAN',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.kawasan.index'))
            ->assertOk()
            ->assertSee('value="HUTAN_MANGROVE"', false)
            ->assertSee('value="DICADANGKAN"', false);
    }

    public function test_pagination_mempertahankan_parameter_filter(): void
    {
        $admin = Pengguna::factory()->admin()->create();
        $utara = $this->buatWilayah('Blok Utara');

        foreach (range(1, 16) as $nomor) {
            $this->buatKawasan([
                'wilayah_id' => $utara->wilayah_id,
                'nama_kawasan' => sprintf('Kawasan Filter %02d', $nomor),
            ]);
        }

        $halamanPertama = $this->actingAs($admin)
            ->get(route('admin.kawasan.index', ['wilayah_id' => $utara->wilayah_id]))
            ->assertOk()
            ->assertSee('Kawasan Filter 01')
            ->assertSee('Kawasan Filter 15')
            ->assertDontSee('Kawasan Filter 16');

        // Tautan halaman berikutnya harus tetap membawa parameter filter.
        $halamanPertama->assertSee('wilayah_id='.$utara->wilayah_id, false);

        $this->actingAs($admin)
            ->get(route('admin.kawasan.index', [
                'wilayah_id' => $utara->wilayah_id,
                'page' => 2,
            ]))
            ->assertOk()
            ->assertSee('Kawasan Filter 16')
            ->assertDontSee('Kawasan Filter 01');
    }

    public function test_filter_tanpa_hasil_menampilkan_pesan_khusus(): void
    {
        $admin = Pengguna::factory()->admin()->create();
        $this->buatKawasan(['nama_kawasan' => 'Kawasan Alfa']);

        $this->actingAs($admin)
            ->get(route('admin.kawasan.index', ['cari' => 'Tidak Ada Yang Cocok']))
            ->assertOk()
            ->assertSee('Tidak ada kawasan yang cocok dengan pencarian atau filter.');
    }

    public function test_pengguna_non_admin_tidak_bisa_memakai_filter(): void
    {
        $masyarakat = Pengguna::factory()->masyarakat()->create();
        $pengawas = Pengguna::factory()->pengawas()->create();

        $this->actingAs($masyarakat)
            ->get(route('admin.kawasan.index', ['cari' => 'Alfa']))
            ->assertForbidden();

        $this->actingAs($pengawas)
            ->get(route('admin.kawasan.index', ['status_data' => 'aktif']))
            ->assertForbidden();
    }

    public function test_admin_nonaktif_tidak_bisa_memakai_filter(): void
    {
        $admin = Pengguna::factory()->admin()->nonaktif()->create();

        $this->actingAs($admin)
            ->get(route('admin.kawasan.index', ['cari' => 'Alfa']))
            ->assertForbidden();
    }

    public function test_pengguna_belum_login_diarahkan_ke_halaman_login(): void
    {
        $this->get(route('admin.kawasan.index', ['cari' => 'Alfa']))
            ->assertRedirect(route('login'));
    }

    public function test_parameter_bounding_box_diabaikan_pada_koneksi_non_spasial(): void
    {
        $admin = Pengguna::factory()->admin()->create();
        $this->buatKawasan(['nama_kawasan' => 'Kawasan Alfa']);
        $this->buatKawasan(['nama_kawasan' => 'Kawasan Beta']);

        $this->actingAs($admin);

        $this->getDenganDriverNonspasial(route('admin.kawasan.index', [
            'min_lon' => 106.7,
            'min_lat' => -6.4,
            'max_lon' => 106.9,
            'max_lat' => -6.1,
        ]))
            ->assertOk()
            ->assertSee('Kawasan Alfa')
            ->assertSee('Kawasan Beta')
            ->assertDontSee('Area Koordinat (Bounding Box)');
    }

    public function test_parameter_bounding_box_tidak_lengkap_tidak_menghasilkan_error_pada_koneksi_non_spasial(): void
    {
        $admin = Pengguna::factory()->admin()->create();
        $this->buatKawasan(['nama_kawasan' => 'Kawasan Alfa']);

        $this->actingAs($admin);

        $this->getDenganDriverNonspasial(route('admin.kawasan.index', [
            'min_lon' => 106.7,
            'min_lat' => -6.4,
        ]))
            ->assertOk()
            ->assertSee('Kawasan Alfa')
            ->assertSessionHasNoErrors();
    }

    private function getDenganDriverNonspasial(string $url): TestResponse
    {
        $database = DB::getFacadeRoot();
        $koneksi = Mockery::mock($database->connection());
        $koneksi->shouldReceive('getDriverName')->atLeast()->once()->andReturn('sqlite');

        // Proxy meneruskan operasi lain ke koneksi PostgreSQL yang sama.
        $proxy = Mockery::mock($database);
        $proxy->shouldReceive('connection')->withNoArgs()->andReturn($koneksi);

        DB::swap($proxy);

        try {
            return $this->get($url);
        } finally {
            DB::swap($database);
        }
    }

    private function buatWilayah(string $nama): Wilayah
    {
        return Wilayah::create([
            'nama_wilayah' => $nama,
            'kode_wilayah' => 'KW-'.fake()->unique()->numerify('#####'),
            'tingkat_wilayah' => 'BLOK',
        ]);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function buatKawasan(array $attributes = []): Kawasan
    {
        return Kawasan::create(array_merge([
            'wilayah_id' => $this->buatWilayah('Blok Uji '.fake()->unique()->numerify('###'))->wilayah_id,
            'nama_kawasan' => 'Kawasan Uji',
            'jenis_kawasan' => 'HUTAN_LINDUNG',
            'status_kawasan' => 'AKTIF',
        ], $attributes));
    }
}
