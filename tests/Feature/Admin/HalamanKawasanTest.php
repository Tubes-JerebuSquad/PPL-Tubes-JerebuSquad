<?php

namespace Tests\Feature\Admin;

use App\Models\Kawasan;
use App\Models\Pengguna;
use App\Models\Wilayah;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
