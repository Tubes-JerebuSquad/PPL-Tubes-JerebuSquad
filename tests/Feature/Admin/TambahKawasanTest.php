<?php

namespace Tests\Feature\Admin;

use App\Enums\StatusKualitas;
use App\Models\Kawasan;
use App\Models\Pengguna;
use App\Models\Wilayah;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TambahKawasanTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_bisa_membuka_form_tambah_kawasan(): void
    {
        $admin = Pengguna::factory()->admin()->create();
        $aktif = $this->buatWilayah('Blok Timur');
        $nonaktif = $this->buatWilayah('Blok Lama', false);

        $this->actingAs($admin)
            ->get(route('admin.kawasan.create'))
            ->assertOk()
            ->assertSee('Tambah Kawasan')
            ->assertSee($aktif->nama_wilayah)
            ->assertDontSee($nonaktif->nama_wilayah)
            ->assertSee('Simpan');
    }

    public function test_admin_dapat_menambah_kawasan(): void
    {
        $admin = Pengguna::factory()->admin()->create();
        $wilayah = $this->buatWilayah();

        $this->actingAs($admin)
            ->post(route('admin.kawasan.store'), $this->dataValid($wilayah))
            ->assertRedirect(route('admin.kawasan.index'))
            ->assertSessionHas('status', 'Kawasan berhasil ditambahkan.');

        $kawasan = Kawasan::where('nama_kawasan', 'Hutan Lindung Uji')->first();

        $this->assertNotNull($kawasan);
        $this->assertSame($wilayah->wilayah_id, $kawasan->wilayah_id);
        $this->assertSame('HUTAN_LINDUNG', $kawasan->jenis_kawasan);
        $this->assertSame('AKTIF', $kawasan->status_kawasan);
        $this->assertSame('Catatan kawasan uji.', $kawasan->catatan);
        $this->assertTrue($kawasan->is_aktif);
        $this->assertSame($admin->pengguna_id, $kawasan->dibuat_oleh);
    }

    public function test_kawasan_baru_aktif_secara_default(): void
    {
        $admin = Pengguna::factory()->admin()->create();
        $wilayah = $this->buatWilayah();

        $data = $this->dataValid($wilayah);
        unset($data['is_aktif']);

        $this->actingAs($admin)
            ->post(route('admin.kawasan.store'), $data)
            ->assertRedirect(route('admin.kawasan.index'));

        $this->assertTrue(Kawasan::where('nama_kawasan', 'Hutan Lindung Uji')->firstOrFail()->is_aktif);
    }

    public function test_checkbox_tidak_dicentang_tersimpan_tidak_aktif(): void
    {
        $admin = Pengguna::factory()->admin()->create();
        $wilayah = $this->buatWilayah();

        $this->actingAs($admin)
            ->post(route('admin.kawasan.store'), $this->dataValid($wilayah, ['is_aktif' => '0']))
            ->assertRedirect(route('admin.kawasan.index'));

        $this->assertFalse(Kawasan::where('nama_kawasan', 'Hutan Lindung Uji')->firstOrFail()->is_aktif);
    }

    public function test_wilayah_nonaktif_ditolak(): void
    {
        $admin = Pengguna::factory()->admin()->create();
        $wilayah = $this->buatWilayah('Blok Lama', false);

        $this->actingAs($admin)
            ->post(route('admin.kawasan.store'), $this->dataValid($wilayah))
            ->assertSessionHasErrors('wilayah_id');

        $this->assertDatabaseMissing('kawasan', ['nama_kawasan' => 'Hutan Lindung Uji']);
    }

    public function test_isian_wajib_harus_diisi(): void
    {
        $admin = Pengguna::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('admin.kawasan.store'), [])
            ->assertSessionHasErrors(['wilayah_id', 'nama_kawasan', 'jenis_kawasan', 'status_kawasan']);

        $this->assertSame(0, Kawasan::count());
    }

    public function test_panjang_isian_dibatasi_sesuai_migration(): void
    {
        $admin = Pengguna::factory()->admin()->create();
        $wilayah = $this->buatWilayah();

        $this->actingAs($admin)
            ->post(route('admin.kawasan.store'), $this->dataValid($wilayah, [
                'nama_kawasan' => str_repeat('a', 201),
                'jenis_kawasan' => str_repeat('b', 101),
                'status_kawasan' => str_repeat('c', 51),
                'catatan' => str_repeat('d', 2001),
            ]))
            ->assertSessionHasErrors(['nama_kawasan', 'jenis_kawasan', 'status_kawasan', 'catatan']);

        $this->assertSame(0, Kawasan::count());
    }

    public function test_bukan_admin_tidak_boleh_menambah_kawasan(): void
    {
        $masyarakat = Pengguna::factory()->masyarakat()->create();
        $wilayah = $this->buatWilayah();

        $this->actingAs($masyarakat)
            ->post(route('admin.kawasan.store'), $this->dataValid($wilayah))
            ->assertForbidden();

        $this->assertDatabaseMissing('kawasan', ['nama_kawasan' => 'Hutan Lindung Uji']);
    }

    public function test_admin_nonaktif_tidak_boleh_menambah_kawasan(): void
    {
        $admin = Pengguna::factory()->admin()->nonaktif()->create();
        $wilayah = $this->buatWilayah();

        $this->actingAs($admin)
            ->post(route('admin.kawasan.store'), $this->dataValid($wilayah))
            ->assertForbidden();

        $this->assertDatabaseMissing('kawasan', ['nama_kawasan' => 'Hutan Lindung Uji']);
    }

    public function test_tamu_diarahkan_ke_halaman_login(): void
    {
        $wilayah = $this->buatWilayah();

        $this->get(route('admin.kawasan.create'))
            ->assertRedirect(route('login'));

        $this->post(route('admin.kawasan.store'), $this->dataValid($wilayah))
            ->assertRedirect(route('login'));
    }

    public function test_daftar_kawasan_menampilkan_tombol_tambah_dan_edit(): void
    {
        $admin = Pengguna::factory()->admin()->create();
        $kawasan = $this->buatKawasan($this->buatWilayah());

        $this->actingAs($admin)
            ->get(route('admin.kawasan.index'))
            ->assertOk()
            ->assertSee('Tambah Kawasan')
            ->assertSee(route('admin.kawasan.create'), false)
            ->assertSee(route('admin.kawasan.edit', $kawasan), false);
    }

    // ── Skenario Sumber Data (Tambah) ────────────────────────────────────────

    public function test_tambah_kawasan_tanpa_sumber_data_berhasil_disimpan(): void
    {
        $admin   = Pengguna::factory()->admin()->create();
        $wilayah = $this->buatWilayah();

        $this->actingAs($admin)
            ->post(route('admin.kawasan.store'), $this->dataValid($wilayah))
            ->assertRedirect(route('admin.kawasan.index'))
            ->assertSessionHasNoErrors();

        $kawasan = Kawasan::where('nama_kawasan', 'Hutan Lindung Uji')->firstOrFail();
        $this->assertNull($kawasan->sumber_id);
    }

    public function test_tambah_kawasan_dengan_sumber_aktif_berhasil_disimpan(): void
    {
        $admin    = Pengguna::factory()->admin()->create();
        $wilayah  = $this->buatWilayah();
        $sumberId = $this->buatSumberData(aktif: true);

        $this->actingAs($admin)
            ->post(route('admin.kawasan.store'), $this->dataValid($wilayah, [
                'sumber_id' => $sumberId,
            ]))
            ->assertRedirect(route('admin.kawasan.index'))
            ->assertSessionHasNoErrors();

        $kawasan = Kawasan::where('nama_kawasan', 'Hutan Lindung Uji')->firstOrFail();
        $this->assertSame($sumberId, $kawasan->sumber_id);
    }

    public function test_sumber_nonaktif_ditolak_saat_tambah(): void
    {
        $admin    = Pengguna::factory()->admin()->create();
        $wilayah  = $this->buatWilayah();
        $sumberId = $this->buatSumberData(aktif: false);

        $this->actingAs($admin)
            ->post(route('admin.kawasan.store'), $this->dataValid($wilayah, [
                'sumber_id' => $sumberId,
            ]))
            ->assertSessionHasErrors('sumber_id');

        $this->assertDatabaseMissing('kawasan', ['nama_kawasan' => 'Hutan Lindung Uji']);
    }

    public function test_sumber_id_fiktif_ditolak_saat_tambah(): void
    {
        $admin   = Pengguna::factory()->admin()->create();
        $wilayah = $this->buatWilayah();

        $this->actingAs($admin)
            ->post(route('admin.kawasan.store'), $this->dataValid($wilayah, [
                'sumber_id' => 999999,
            ]))
            ->assertSessionHasErrors('sumber_id');

        $this->assertDatabaseMissing('kawasan', ['nama_kawasan' => 'Hutan Lindung Uji']);
    }

    public function test_form_tambah_menampilkan_dropdown_sumber_aktif(): void
    {
        $admin          = Pengguna::factory()->admin()->create();
        $sumberAktif    = $this->buatSumberData(aktif: true,  nama: 'Sumber Aktif Terlihat');
        $sumberNonaktif = $this->buatSumberData(aktif: false, nama: 'Sumber Nonaktif Tersembunyi');

        $this->actingAs($admin)
            ->get(route('admin.kawasan.create'))
            ->assertOk()
            ->assertSee('Sumber Data Kawasan')
            ->assertSee('Sumber Aktif Terlihat')
            ->assertDontSee('Sumber Nonaktif Tersembunyi');
    }

    // ── Helper ───────────────────────────────────────────────────────────────

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

    private function buatWilayah(string $nama = 'Blok Barat', bool $aktif = true): Wilayah
    {
        return Wilayah::create([
            'nama_wilayah' => $nama,
            'kode_wilayah' => 'KW-'.fake()->unique()->numerify('####'),
            'tingkat_wilayah' => 'BLOK',
            'is_aktif' => $aktif,
        ]);
    }

    private function buatKawasan(Wilayah $wilayah): Kawasan
    {
        return Kawasan::create([
            'wilayah_id' => $wilayah->wilayah_id,
            'nama_kawasan' => 'Kawasan Uji',
            'jenis_kawasan' => 'HUTAN_LINDUNG',
            'status_kawasan' => 'AKTIF',
        ]);
    }

    /**
     * @param  array<string, mixed>  $ganti
     * @return array<string, mixed>
     */
    private function dataValid(Wilayah $wilayah, array $ganti = []): array
    {
        return array_merge([
            'wilayah_id' => $wilayah->wilayah_id,
            'nama_kawasan' => 'Hutan Lindung Uji',
            'jenis_kawasan' => 'HUTAN_LINDUNG',
            'status_kawasan' => 'AKTIF',
            'catatan' => 'Catatan kawasan uji.',
            'is_aktif' => '1',
        ], $ganti);
    }
}
