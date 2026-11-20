<?php

namespace Tests\Feature\Admin;

use App\Models\Kawasan;
use App\Models\Pengguna;
use App\Models\Wilayah;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class UbahKawasanTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_bisa_membuka_form_ubah_kawasan(): void
    {
        $admin = Pengguna::factory()->admin()->create();
        $wilayah = $this->buatWilayah('Blok Timur');
        $kawasan = $this->buatKawasan($wilayah, [
            'nama_kawasan' => 'Taman Nasional Uji',
            'jenis_kawasan' => 'TAMAN_NASIONAL',
            'status_kawasan' => 'DITETAPKAN',
            'catatan' => 'Catatan kawasan uji.',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.kawasan.edit', $kawasan))
            ->assertOk()
            ->assertSee('Ubah Kawasan')
            ->assertSee('Taman Nasional Uji')
            ->assertSee('TAMAN_NASIONAL')
            ->assertSee('DITETAPKAN')
            ->assertSee('Catatan kawasan uji.')
            ->assertSee($wilayah->nama_wilayah)
            ->assertSee('Simpan perubahan');
    }

    public function test_admin_dapat_mengubah_kawasan_tanpa_mengubah_pembuat_dan_sumber(): void
    {
        $admin = Pengguna::factory()->admin()->create();
        // Peran bersifat unik dan dipakai bersama, jadi pengguna kedua memakai peran Admin yang sama.
        $pembuat = Pengguna::factory()->create([
            'peran_id' => $admin->peran_id,
        ]);
        $wilayah = $this->buatWilayah();
        $sumberId = $this->buatSumberData();
        $kawasan = $this->buatKawasan($wilayah, [
            'sumber_id' => $sumberId,
            'dibuat_oleh' => $pembuat->pengguna_id,
        ]);

        $this->actingAs($admin)
            ->put(route('admin.kawasan.update', $kawasan), $this->dataUbah($kawasan, [
                'nama_kawasan' => 'Nama Kawasan Baru',
                'jenis_kawasan' => 'CAGAR_ALAM',
                'status_kawasan' => 'DICADANGKAN',
                'catatan' => 'Catatan baru.',
                'is_aktif' => '0',
            ]))
            ->assertRedirect(route('admin.kawasan.index'))
            ->assertSessionHas('status', 'Data kawasan berhasil diperbarui.');

        $kawasan->refresh();

        $this->assertSame('Nama Kawasan Baru', $kawasan->nama_kawasan);
        $this->assertSame('CAGAR_ALAM', $kawasan->jenis_kawasan);
        $this->assertSame('DICADANGKAN', $kawasan->status_kawasan);
        $this->assertSame('Catatan baru.', $kawasan->catatan);
        $this->assertFalse($kawasan->is_aktif);
        $this->assertSame($sumberId, $kawasan->sumber_id);
        $this->assertSame($pembuat->pengguna_id, $kawasan->dibuat_oleh);
        $this->assertNotNull($kawasan->diperbarui_pada);
    }

    public function test_wilayah_nonaktif_yang_sedang_dipakai_tetap_bisa_dipertahankan(): void
    {
        $admin = Pengguna::factory()->admin()->create();
        $wilayah = $this->buatWilayah('Blok Lama', false);
        $kawasan = $this->buatKawasan($wilayah);

        $this->actingAs($admin)
            ->get(route('admin.kawasan.edit', $kawasan))
            ->assertOk()
            ->assertSee('Blok Lama')
            ->assertSee('(nonaktif)');

        $this->actingAs($admin)
            ->put(route('admin.kawasan.update', $kawasan), $this->dataUbah($kawasan))
            ->assertRedirect(route('admin.kawasan.index'))
            ->assertSessionHasNoErrors();

        $this->assertSame($wilayah->wilayah_id, $kawasan->refresh()->wilayah_id);
    }

    public function test_wilayah_nonaktif_lain_tidak_bisa_dipilih(): void
    {
        $admin = Pengguna::factory()->admin()->create();
        $aktif = $this->buatWilayah('Blok Aktif');
        $nonaktif = $this->buatWilayah('Blok Nonaktif', false);
        $kawasan = $this->buatKawasan($aktif);

        $this->actingAs($admin)
            ->put(route('admin.kawasan.update', $kawasan), $this->dataUbah($kawasan, [
                'wilayah_id' => $nonaktif->wilayah_id,
            ]))
            ->assertSessionHasErrors('wilayah_id');

        $this->assertSame($aktif->wilayah_id, $kawasan->refresh()->wilayah_id);
    }

    public function test_isian_wajib_harus_diisi_saat_mengubah(): void
    {
        $admin = Pengguna::factory()->admin()->create();
        $kawasan = $this->buatKawasan($this->buatWilayah());

        $this->actingAs($admin)
            ->put(route('admin.kawasan.update', $kawasan), [])
            ->assertSessionHasErrors(['wilayah_id', 'nama_kawasan', 'jenis_kawasan', 'status_kawasan']);
    }

    public function test_panjang_isian_dibatasi_saat_mengubah(): void
    {
        $admin = Pengguna::factory()->admin()->create();
        $kawasan = $this->buatKawasan($this->buatWilayah());

        $this->actingAs($admin)
            ->put(route('admin.kawasan.update', $kawasan), $this->dataUbah($kawasan, [
                'nama_kawasan' => str_repeat('a', 201),
                'jenis_kawasan' => str_repeat('b', 101),
                'status_kawasan' => str_repeat('c', 51),
                'catatan' => str_repeat('d', 2001),
            ]))
            ->assertSessionHasErrors(['nama_kawasan', 'jenis_kawasan', 'status_kawasan', 'catatan']);
    }

    public function test_id_kawasan_yang_tidak_ditemukan_menghasilkan_404(): void
    {
        $admin = Pengguna::factory()->admin()->create();
        $wilayah = $this->buatWilayah();
        $kawasan = $this->buatKawasan($wilayah);

        $this->actingAs($admin)
            ->get(route('admin.kawasan.edit', 999999))
            ->assertNotFound();

        $this->actingAs($admin)
            ->put(route('admin.kawasan.update', 999999), $this->dataUbah($kawasan))
            ->assertNotFound();
    }

    public function test_bukan_admin_tidak_boleh_mengubah_kawasan(): void
    {
        $masyarakat = Pengguna::factory()->masyarakat()->create();
        $kawasan = $this->buatKawasan($this->buatWilayah());

        $this->actingAs($masyarakat)
            ->get(route('admin.kawasan.edit', $kawasan))
            ->assertForbidden();

        $this->actingAs($masyarakat)
            ->put(route('admin.kawasan.update', $kawasan), $this->dataUbah($kawasan, [
                'nama_kawasan' => 'Diubah Masyarakat',
            ]))
            ->assertForbidden();

        $this->assertSame('Kawasan Uji', $kawasan->refresh()->nama_kawasan);
    }

    public function test_admin_nonaktif_tidak_boleh_mengubah_kawasan(): void
    {
        $admin = Pengguna::factory()->admin()->nonaktif()->create();
        $kawasan = $this->buatKawasan($this->buatWilayah());

        $this->actingAs($admin)
            ->put(route('admin.kawasan.update', $kawasan), $this->dataUbah($kawasan, [
                'nama_kawasan' => 'Diubah Admin Nonaktif',
            ]))
            ->assertForbidden();

        $this->assertSame('Kawasan Uji', $kawasan->refresh()->nama_kawasan);
    }

    public function test_tamu_diarahkan_ke_halaman_login(): void
    {
        $kawasan = $this->buatKawasan($this->buatWilayah());

        $this->get(route('admin.kawasan.edit', $kawasan))
            ->assertRedirect(route('login'));

        $this->put(route('admin.kawasan.update', $kawasan), $this->dataUbah($kawasan))
            ->assertRedirect(route('login'));
    }

    public function test_halaman_detail_menampilkan_tombol_edit(): void
    {
        $admin = Pengguna::factory()->admin()->create();
        $kawasan = $this->buatKawasan($this->buatWilayah());

        $this->actingAs($admin)
            ->get(route('admin.kawasan.show', $kawasan))
            ->assertOk()
            ->assertSee(route('admin.kawasan.edit', $kawasan), false);
    }

    private function buatWilayah(string $nama = 'Blok Barat', bool $aktif = true): Wilayah
    {
        return Wilayah::create([
            'nama_wilayah' => $nama,
            'kode_wilayah' => 'KW-'.fake()->unique()->numerify('####'),
            'tingkat_wilayah' => 'BLOK',
            'is_aktif' => $aktif,
        ]);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function buatKawasan(Wilayah $wilayah, array $attributes = []): Kawasan
    {
        return Kawasan::create(array_merge([
            'wilayah_id' => $wilayah->wilayah_id,
            'nama_kawasan' => 'Kawasan Uji',
            'jenis_kawasan' => 'HUTAN_LINDUNG',
            'status_kawasan' => 'AKTIF',
        ], $attributes));
    }

    private function buatSumberData(): int
    {
        return DB::table('sumber_data')->insertGetId([
            'nama_sumber' => 'Sumber Uji',
            'nama_penyedia' => 'Penyedia Uji',
            'nama_lisensi' => 'CC BY 4.0',
            'format_data' => 'GeoJSON',
            'kode_crs' => 'EPSG:4326',
            'status_kualitas' => 'TERVERIFIKASI',
        ], 'sumber_id');
    }

    /**
     * @param  array<string, mixed>  $ganti
     * @return array<string, mixed>
     */
    private function dataUbah(Kawasan $kawasan, array $ganti = []): array
    {
        return array_merge([
            'wilayah_id' => $kawasan->wilayah_id,
            'nama_kawasan' => $kawasan->nama_kawasan,
            'jenis_kawasan' => $kawasan->jenis_kawasan,
            'status_kawasan' => $kawasan->status_kawasan,
            'catatan' => $kawasan->catatan,
            'is_aktif' => $kawasan->is_aktif ? '1' : '0',
        ], $ganti);
    }
}
