<?php

namespace Tests\Feature\Admin;

use App\Enums\StatusKualitas;
use App\Models\Kawasan;
use App\Models\Pengguna;
use App\Models\Wilayah;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class HalamanKawasanTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_aktif_bisa_melihat_daftar_kawasan(): void
    {
        $admin = Pengguna::factory()->admin()->create();
        $kawasan = $this->buatKawasan([
            'nama_kawasan' => 'Hutan Lindung Uji',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.kawasan.index'))
            ->assertOk()
            ->assertSee('Data Kawasan')
            ->assertSee('Hutan Lindung Uji')
            ->assertSee($kawasan->wilayah->nama_wilayah);
    }

    public function test_admin_aktif_bisa_membuka_detail_kawasan(): void
    {
        $admin = Pengguna::factory()->admin()->create();
        $kawasan = $this->buatKawasan([
            'nama_kawasan' => 'Taman Nasional Uji',
            'jenis_kawasan' => 'TAMAN_NASIONAL',
            'status_kawasan' => 'AKTIF',
            'catatan' => 'Catatan kawasan uji.',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.kawasan.show', $kawasan))
            ->assertOk()
            ->assertSee('Taman Nasional Uji')
            ->assertSee('TAMAN_NASIONAL')
            ->assertSee('AKTIF')
            ->assertSee('Catatan kawasan uji.')
            ->assertSee($kawasan->wilayah->nama_wilayah);
    }

    public function test_detail_kawasan_tanpa_poligon_menampilkan_pesan_dan_tautan_kelola_batas(): void
    {
        $admin = Pengguna::factory()->admin()->create();
        $kawasan = $this->buatKawasan();
        $urlKelolaBatas = route('admin.kawasan.batas.index', $kawasan->kawasan_id);

        $this->actingAs($admin)
            ->get(route('admin.kawasan.show', $kawasan))
            ->assertOk()
            ->assertSeeText('Informasi Geospasial')
            ->assertSeeText('Belum ada poligon batas kawasan')
            ->assertDontSeeText('Bounding Box')
            ->assertSeeText('Kelola Batas Kawasan')
            ->assertSeeHtml('href="'.$urlKelolaBatas.'"');

        $this->get($urlKelolaBatas)
            ->assertOk()
            ->assertSeeText('Digitasi Batas Kawasan');
    }

    public function test_detail_kawasan_menampilkan_bbox_dan_nomor_versi_poligon_terbaru(): void
    {
        $admin = Pengguna::factory()->admin()->create();
        $kawasan = $this->buatKawasan();

        $insertBatas = <<<'SQL'
            INSERT INTO batas_kawasan (kawasan_id, nomor_versi, data_geometri, kode_crs, status_validasi)
            VALUES (?, ?, ST_Multi(ST_MakeEnvelope(?, ?, ?, ?, 4326)), 'EPSG:4326', 'VALID')
            SQL;

        DB::insert($insertBatas, [$kawasan->kawasan_id, 2, 106.25, -6.75, 106.5, -6.5]);
        DB::insert($insertBatas, [$kawasan->kawasan_id, 1, 107.25, -7.75, 107.5, -7.5]);

        $this->actingAs($admin)
            ->get(route('admin.kawasan.show', $kawasan))
            ->assertOk()
            ->assertSeeText('Informasi Geospasial')
            ->assertSeeText('Versi 2')
            ->assertSeeText('WGS 84 (EPSG:4326)')
            ->assertSeeTextInOrder([
                'Bounding Box',
                'Bujur minimum', '106.25',
                'Bujur maksimum', '106.5',
                'Lintang minimum', '-6.75',
                'Lintang maksimum', '-6.5',
            ])
            ->assertDontSeeText('Versi 1')
            ->assertDontSeeText(['107.25', '107.5', '-7.75', '-7.5'])
            ->assertDontSeeText('Belum ada poligon batas kawasan')
            ->assertSeeText('Kelola Batas Kawasan')
            ->assertSeeHtml('href="'.route('admin.kawasan.batas.index', $kawasan->kawasan_id).'"');
    }

    public function test_id_kawasan_yang_tidak_ditemukan_menghasilkan_404(): void
    {
        $admin = Pengguna::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.kawasan.show', 999999))
            ->assertNotFound();
    }

    public function test_pengguna_belum_login_diarahkan_ke_halaman_login(): void
    {
        $kawasan = $this->buatKawasan();

        $this->get(route('admin.kawasan.index'))
            ->assertRedirect(route('login'));

        $this->get(route('admin.kawasan.show', $kawasan))
            ->assertRedirect(route('login'));
    }

    public function test_pengguna_non_admin_tidak_bisa_mengakses_halaman_kawasan(): void
    {
        $kawasan = $this->buatKawasan();
        $masyarakat = Pengguna::factory()->masyarakat()->create();
        $pengawas = Pengguna::factory()->pengawas()->create();

        $this->actingAs($masyarakat)
            ->get(route('admin.kawasan.index'))
            ->assertForbidden();

        $this->actingAs($masyarakat)
            ->get(route('admin.kawasan.show', $kawasan))
            ->assertForbidden();

        $this->actingAs($pengawas)
            ->get(route('admin.kawasan.index'))
            ->assertForbidden();

        $this->actingAs($pengawas)
            ->get(route('admin.kawasan.show', $kawasan))
            ->assertForbidden();
    }

    public function test_admin_nonaktif_tidak_bisa_mengakses_halaman_kawasan(): void
    {
        $admin = Pengguna::factory()->admin()->nonaktif()->create();
        $kawasan = $this->buatKawasan();

        $this->actingAs($admin)
            ->get(route('admin.kawasan.index'))
            ->assertForbidden();

        $this->actingAs($admin)
            ->get(route('admin.kawasan.show', $kawasan))
            ->assertForbidden();
    }

    public function test_daftar_kawasan_menampilkan_kondisi_kosong(): void
    {
        $admin = Pengguna::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.kawasan.index'))
            ->assertOk()
            ->assertSee('Belum ada data kawasan.');
    }

    public function test_daftar_kawasan_membagi_data_per_halaman(): void
    {
        $admin = Pengguna::factory()->admin()->create();
        $wilayah = $this->buatWilayah();

        foreach (range(1, 16) as $nomor) {
            $this->buatKawasan([
                'wilayah_id' => $wilayah->wilayah_id,
                'nama_kawasan' => sprintf('Kawasan Uji %02d', $nomor),
            ]);
        }

        $this->actingAs($admin)
            ->get(route('admin.kawasan.index'))
            ->assertOk()
            ->assertSee('Kawasan Uji 01')
            ->assertSee('Kawasan Uji 15')
            ->assertDontSee('Kawasan Uji 16');

        $this->actingAs($admin)
            ->get(route('admin.kawasan.index', ['page' => 2]))
            ->assertOk()
            ->assertSee('Kawasan Uji 16')
            ->assertDontSee('Kawasan Uji 01');
    }

    private function buatWilayah(string $nama = 'Blok Barat'): Wilayah
    {
        return Wilayah::create([
            'nama_wilayah' => $nama,
            'kode_wilayah' => 'KW-'.fake()->unique()->numerify('####'),
            'tingkat_wilayah' => 'BLOK',
        ]);
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

    // ── Helper ───────────────────────────────────────────────────────────────

    private function buatSumberData(
        bool $aktif = true,
        string $nama = 'Sumber Uji',
        string $penyedia = 'Penyedia Uji',
        string $lisensi = 'CC BY 4.0',
        string $crs = 'EPSG:4326',
    ): int {
        return DB::table('sumber_data')->insertGetId([
            'nama_sumber'     => $nama,
            'nama_penyedia'   => $penyedia,
            'nama_lisensi'    => $lisensi,
            'format_data'     => 'GeoJSON',
            'kode_crs'        => $crs,
            'status_kualitas' => StatusKualitas::VERIFIED->value,
            'is_aktif'        => $aktif,
        ], 'sumber_id');
    }

    // ── Skenario Sumber Data (Detail Kawasan) ────────────────────────────────

    public function test_detail_kawasan_tanpa_sumber_menampilkan_belum_ditautkan(): void
    {
        $admin   = Pengguna::factory()->admin()->create();
        $kawasan = $this->buatKawasan(['sumber_id' => null]);

        $this->actingAs($admin)
            ->get(route('admin.kawasan.show', $kawasan))
            ->assertOk()
            ->assertSee('Belum ditautkan');
    }

    public function test_detail_kawasan_dengan_sumber_aktif_menampilkan_nama_dan_badge(): void
    {
        $admin    = Pengguna::factory()->admin()->create();
        $sumberId = $this->buatSumberData(aktif: true, nama: 'Sumber BIG');
        $kawasan  = $this->buatKawasan(['sumber_id' => $sumberId]);

        $this->actingAs($admin)
            ->get(route('admin.kawasan.show', $kawasan))
            ->assertOk()
            ->assertSee('Sumber BIG')
            ->assertSee('Aktif');
    }

    public function test_detail_kawasan_dengan_sumber_nonaktif_menampilkan_peringatan(): void
    {
        $admin    = Pengguna::factory()->admin()->create();
        $sumberId = $this->buatSumberData(aktif: false, nama: 'Sumber Nonaktif Lama');
        $kawasan  = $this->buatKawasan(['sumber_id' => $sumberId]);

        $this->actingAs($admin)
            ->get(route('admin.kawasan.show', $kawasan))
            ->assertOk()
            ->assertSee('Sumber Nonaktif Lama')
            ->assertSee('Nonaktif')
            ->assertSee('tidak tersedia untuk pemilihan baru');
    }

    public function test_detail_kawasan_menampilkan_metadata_penyedia_lisensi_crs(): void
    {
        $admin    = Pengguna::factory()->admin()->create();
        $sumberId = $this->buatSumberData(
            aktif:    true,
            nama:     'Sumber Lengkap',
            penyedia: 'Badan Informasi Geospasial',
            lisensi:  'CC BY-SA 4.0',
            crs:      'EPSG:32748',
        );
        $kawasan = $this->buatKawasan(['sumber_id' => $sumberId]);

        $this->actingAs($admin)
            ->get(route('admin.kawasan.show', $kawasan))
            ->assertOk()
            ->assertSee('Badan Informasi Geospasial')
            ->assertSee('CC BY-SA 4.0')
            ->assertSee('EPSG:32748');
    }

    public function test_detail_kawasan_dengan_metadata_opsional_kosong_menampilkan_tanda_pisah(): void
    {
        $admin = Pengguna::factory()->admin()->create();
        $sumberId = $this->buatSumberData(
            aktif:    true,
            nama:     'Sumber Minimal',
            penyedia: '',
            lisensi:  '',
            crs:      '',
        );
        $kawasan = $this->buatKawasan(['sumber_id' => $sumberId]);

        $this->actingAs($admin)
            ->get(route('admin.kawasan.show', $kawasan))
            ->assertOk()
            ->assertSee('Sumber Minimal')
            ->assertSee('—');
    }

    public function test_detail_kawasan_menampilkan_tautan_ke_katalog_sumber_data(): void
    {
        $admin    = Pengguna::factory()->admin()->create();
        $sumberId = $this->buatSumberData(aktif: true, nama: 'Sumber Terhubung');
        $kawasan  = $this->buatKawasan(['sumber_id' => $sumberId]);

        $this->actingAs($admin)
            ->get(route('admin.kawasan.show', $kawasan))
            ->assertOk()
            ->assertSee('Lihat detail sumber data')
            ->assertSee(route('katalog.sumber-data.show', $sumberId), false);
    }
}
