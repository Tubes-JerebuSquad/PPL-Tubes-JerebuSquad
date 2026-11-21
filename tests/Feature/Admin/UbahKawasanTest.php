<?php

namespace Tests\Feature\Admin;

use App\Enums\StatusKualitas;
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

    // ── Skenario Sumber Data (Edit) ──────────────────────────────────────────

    public function test_edit_kawasan_ganti_ke_sumber_aktif_baru(): void
    {
        $admin    = Pengguna::factory()->admin()->create();
        $wilayah  = $this->buatWilayah();
        $lamaSumberId = $this->buatSumberData(aktif: true, nama: 'Sumber Lama');
        $baruSumberId = $this->buatSumberData(aktif: true, nama: 'Sumber Baru');
        $kawasan  = $this->buatKawasan($wilayah, ['sumber_id' => $lamaSumberId]);

        $this->actingAs($admin)
            ->put(route('admin.kawasan.update', $kawasan), $this->dataUbah($kawasan, [
                'sumber_id' => $baruSumberId,
            ]))
            ->assertRedirect(route('admin.kawasan.index'))
            ->assertSessionHasNoErrors();

        $this->assertSame($baruSumberId, $kawasan->refresh()->sumber_id);
    }

    public function test_edit_kawasan_lepas_sumber_dengan_nilai_kosong(): void
    {
        $admin   = Pengguna::factory()->admin()->create();
        $wilayah = $this->buatWilayah();
        $sumberId = $this->buatSumberData(aktif: true);
        $kawasan  = $this->buatKawasan($wilayah, ['sumber_id' => $sumberId]);

        $this->actingAs($admin)
            ->put(route('admin.kawasan.update', $kawasan), $this->dataUbah($kawasan, [
                'sumber_id' => '',
            ]))
            ->assertRedirect(route('admin.kawasan.index'))
            ->assertSessionHasNoErrors();

        $this->assertNull($kawasan->refresh()->sumber_id);
    }

    public function test_sumber_nonaktif_yang_terpasang_tetap_bisa_dipertahankan(): void
    {
        $admin    = Pengguna::factory()->admin()->create();
        $wilayah  = $this->buatWilayah();
        $sumberId = $this->buatSumberData(aktif: false);
        $kawasan  = $this->buatKawasan($wilayah, ['sumber_id' => $sumberId]);

        // Kirim sumber_id yang sama (nonaktif tapi sudah terpasang) → harus lolos
        $this->actingAs($admin)
            ->put(route('admin.kawasan.update', $kawasan), $this->dataUbah($kawasan, [
                'sumber_id' => $sumberId,
            ]))
            ->assertRedirect(route('admin.kawasan.index'))
            ->assertSessionHasNoErrors();

        $this->assertSame($sumberId, $kawasan->refresh()->sumber_id);
    }

    public function test_sumber_nonaktif_lain_ditolak_saat_edit(): void
    {
        $admin          = Pengguna::factory()->admin()->create();
        $wilayah        = $this->buatWilayah();
        $sumberAktif    = $this->buatSumberData(aktif: true,  nama: 'Sumber Aktif');
        $sumberNonaktif = $this->buatSumberData(aktif: false, nama: 'Sumber Nonaktif');
        $kawasan        = $this->buatKawasan($wilayah, ['sumber_id' => $sumberAktif]);

        // Mencoba mengganti ke sumber nonaktif LAIN → harus ditolak
        $this->actingAs($admin)
            ->put(route('admin.kawasan.update', $kawasan), $this->dataUbah($kawasan, [
                'sumber_id' => $sumberNonaktif,
            ]))
            ->assertSessionHasErrors('sumber_id');

        $this->assertSame($sumberAktif, $kawasan->refresh()->sumber_id);
    }

    public function test_sumber_id_fiktif_ditolak_saat_edit(): void
    {
        $admin   = Pengguna::factory()->admin()->create();
        $kawasan = $this->buatKawasan($this->buatWilayah());

        $this->actingAs($admin)
            ->put(route('admin.kawasan.update', $kawasan), $this->dataUbah($kawasan, [
                'sumber_id' => 999999,
            ]))
            ->assertSessionHasErrors('sumber_id');

        $this->assertNull($kawasan->refresh()->sumber_id);
    }

    public function test_form_edit_menampilkan_sumber_nonaktif_yang_terpasang(): void
    {
        $admin    = Pengguna::factory()->admin()->create();
        $wilayah  = $this->buatWilayah();
        $sumberId = $this->buatSumberData(aktif: false, nama: 'Sumber Nonaktif Lama');
        $kawasan  = $this->buatKawasan($wilayah, ['sumber_id' => $sumberId]);

        $this->actingAs($admin)
            ->get(route('admin.kawasan.edit', $kawasan))
            ->assertOk()
            ->assertSee('Sumber Nonaktif Lama')
            ->assertSee('(nonaktif)');
    }

    public function test_edit_kawasan_tidak_mengubah_sumber_id_jika_field_absen(): void
    {
        $admin    = Pengguna::factory()->admin()->create();
        $wilayah  = $this->buatWilayah();
        $sumberId = $this->buatSumberData(aktif: true);
        $kawasan  = $this->buatKawasan($wilayah, ['sumber_id' => $sumberId]);

        // dataUbah() tidak menyertakan sumber_id → field absen → 'sometimes' → tidak divalidasi
        $this->actingAs($admin)
            ->put(route('admin.kawasan.update', $kawasan), $this->dataUbah($kawasan, [
                'nama_kawasan' => 'Nama Diperbarui',
            ]))
            ->assertRedirect(route('admin.kawasan.index'))
            ->assertSessionHasNoErrors();

        $kawasan->refresh();
        $this->assertSame('Nama Diperbarui', $kawasan->nama_kawasan);
        $this->assertSame($sumberId, $kawasan->sumber_id); // sumber tidak berubah
    }

    public function test_edit_kawasan_tidak_mengubah_sumber_id_kawasan_lain(): void
    {
        $admin    = Pengguna::factory()->admin()->create();
        $wilayah  = $this->buatWilayah();
        $sumberA  = $this->buatSumberData(aktif: true, nama: 'Sumber A');
        $sumberB  = $this->buatSumberData(aktif: true, nama: 'Sumber B');
        $kawasanA = $this->buatKawasan($wilayah, ['sumber_id' => $sumberA]);
        $kawasanB = $this->buatKawasan($wilayah, ['sumber_id' => $sumberB]);

        $this->actingAs($admin)
            ->put(route('admin.kawasan.update', $kawasanA), $this->dataUbah($kawasanA, [
                'sumber_id' => '',
            ]))
            ->assertRedirect(route('admin.kawasan.index'))
            ->assertSessionHasNoErrors();

        $this->assertNull($kawasanA->refresh()->sumber_id);
        $this->assertSame($sumberB, $kawasanB->refresh()->sumber_id);
    }

    public function test_edit_kawasan_tidak_mengubah_sumber_geometri_pada_batas_kawasan(): void
    {
        $admin          = Pengguna::factory()->admin()->create();
        $wilayah        = $this->buatWilayah();
        $sumberAtribut  = $this->buatSumberData(aktif: true, nama: 'Sumber Atribut Lama');
        $sumberBaru     = $this->buatSumberData(aktif: true, nama: 'Sumber Atribut Baru');
        $sumberGeometri = $this->buatSumberData(aktif: true, nama: 'Sumber Geometri Batas');
        $kawasan        = $this->buatKawasan($wilayah, ['sumber_id' => $sumberAtribut]);

        try {
            DB::statement("
                INSERT INTO batas_kawasan (
                    kawasan_id,
                    nomor_versi,
                    data_geometri,
                    kode_crs,
                    sumber_id,
                    status_validasi,
                    dibuat_oleh,
                    dibuat_pada
                ) VALUES (
                    ?,
                    1,
                    ST_GeomFromText('POLYGON((101.440 -0.500, 101.450 -0.500, 101.450 -0.510, 101.440 -0.510, 101.440 -0.500))', 4326),
                    'EPSG:4326',
                    ?,
                    'VALID',
                    ?,
                    NOW()
                )
            ", [$kawasan->kawasan_id, $sumberGeometri, $admin->pengguna_id]);
        } catch (\Throwable $e) {
            $this->markTestSkipped('Fixture PostGIS batas_kawasan tidak dapat dibuat: ' . $e->getMessage());
        }

        $this->actingAs($admin)
            ->put(route('admin.kawasan.update', $kawasan), $this->dataUbah($kawasan, [
                'sumber_id' => $sumberBaru,
            ]))
            ->assertRedirect(route('admin.kawasan.index'))
            ->assertSessionHasNoErrors();

        $this->assertSame($sumberBaru, $kawasan->refresh()->sumber_id);
        $this->assertSame(
            $sumberGeometri,
            DB::table('batas_kawasan')->where('kawasan_id', $kawasan->kawasan_id)->value('sumber_id')
        );
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

    private function buatSumberData(bool $aktif = true, string $nama = 'Sumber Uji'): int
    {
        return DB::table('sumber_data')->insertGetId([
            'nama_sumber'     => $nama,
            'nama_penyedia'   => 'Penyedia Uji',
            'nama_lisensi'    => 'CC BY 4.0',
            'format_data'     => 'GeoJSON',
            'kode_crs'        => 'EPSG:4326',
            'status_kualitas' => StatusKualitas::VERIFIED->value,
            'is_aktif'        => $aktif,
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
