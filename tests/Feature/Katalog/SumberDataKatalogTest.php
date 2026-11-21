<?php

namespace Tests\Feature\Katalog;

use App\Enums\StatusKualitas;
use App\Exceptions\MetadataBelumLengkapException;
use App\Models\Kawasan;
use App\Models\Pengguna;
use App\Models\RiwayatSumberData;
use App\Models\SumberData;
use App\Models\Wilayah;
use Database\Seeders\SumberDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SumberDataKatalogTest extends TestCase
{
    use RefreshDatabase;

    private Pengguna $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Pengguna::factory()->admin()->create();
        $this->actingAs($this->admin);
    }

    public function test_pencarian_nama_tidak_peka_huruf_besar_kecil(): void
    {
        SumberData::factory()->create(['nama_sumber' => 'Hotspot NASA FIRMS']);
        SumberData::factory()->create(['nama_sumber' => 'Batas Kawasan Lindung']);

        $this->get(route('katalog.sumber-data.index', ['cari' => 'hotspot']))
            ->assertOk()
            ->assertSee('Hotspot NASA FIRMS')
            ->assertSee('Detail')
            ->assertSee('Ubah')
            ->assertDontSee('Batas Kawasan Lindung');

        $this->get(route('katalog.sumber-data.index', ['cari' => 'tidak-ada']))
            ->assertOk()
            ->assertSee('Belum ada sumber data.');
    }

    public function test_filter_status_aktif(): void
    {
        SumberData::factory()->create(['nama_sumber' => 'Dataset Aktif', 'is_aktif' => true]);
        SumberData::factory()->create(['nama_sumber' => 'Dataset Nonaktif', 'is_aktif' => false]);

        $this->get(route('katalog.sumber-data.index', ['is_aktif' => '1']))
            ->assertOk()
            ->assertSee('Dataset Aktif')
            ->assertDontSee('Dataset Nonaktif');

        $this->get(route('katalog.sumber-data.index', ['is_aktif' => '0']))
            ->assertOk()
            ->assertSee('Dataset Nonaktif')
            ->assertDontSee('Dataset Aktif');
    }

    public function test_filter_dengan_nilai_tidak_dikenal_ditolak(): void
    {
        $this->get(route('katalog.sumber-data.index', ['jenis_sumber' => 'MITRA']))
            ->assertSessionHasErrors('jenis_sumber');

        $this->get(route('katalog.sumber-data.index', ['is_aktif' => '2']))
            ->assertSessionHasErrors('is_aktif');
    }

    public function test_katalog_menampilkan_lima_belas_baris_per_halaman(): void
    {
        SumberData::factory()->count(16)->create();

        $respons = $this->get(route('katalog.sumber-data.index'));

        $respons->assertOk();
        $this->assertSame(15, $respons->viewData('sumberData')->perPage());
        $this->assertSame(16, $respons->viewData('sumberData')->total());
    }

    public function test_detail_menampilkan_riwayat_pembuatan(): void
    {
        $this->post(route('katalog.sumber-data.store'), [
            'nama_sumber' => 'Dataset Baru',
            'jenis_sumber' => SumberData::JENIS_EKSTERNAL,
            'nama_penyedia' => 'NASA',
            'nama_lisensi' => 'Public Domain',
            'format_data' => 'CSV',
            'kode_crs' => 'EPSG:4326',
            'status_kualitas' => 'VERIFIED',
        ])->assertSessionHasNoErrors();

        $baru = SumberData::query()->where('nama_sumber', 'Dataset Baru')->firstOrFail();

        $this->get(route('katalog.sumber-data.show', $baru))
            ->assertOk()
            ->assertSee('Dataset Baru')
            ->assertSee('Dibuat')
            ->assertSee('Nama sumber')
            ->assertSee('Jenis sumber')
            ->assertSee('Terverifikasi')
            ->assertDontSee('nama_sumber');
    }

    public function test_riwayat_aktivasi_menampilkan_status_aktif_nonaktif_bukan_angka(): void
    {
        $sumber = SumberData::factory()->nonaktif()->create();

        $this->patch(route('katalog.sumber-data.aktifkan', $sumber))
            ->assertSessionHasNoErrors();

        $this->get(route('katalog.sumber-data.show', $sumber))
            ->assertOk()
            ->assertSee('Status aktif')
            ->assertSee('Nonaktif')
            ->assertSee('Aktif');
    }

    public function test_halaman_katalog_memakai_layout_global_admin(): void
    {
        $this->admin->update(['nama_lengkap' => 'Admin RimbaWatch']);

        $this->get(route('katalog.sumber-data.index'))
            ->assertOk()
            ->assertSee('PBI-03 · Admin')
            ->assertSee('Sistem demo')
            ->assertSee('Admin RimbaWatch')
            ->assertSee('Mode demo akademik')
            ->assertSee('aria-disabled="true"', false);
    }

    public function test_form_tambah_dan_ubah_dirender_dalam_layout(): void
    {
        $this->get(route('katalog.sumber-data.create'))
            ->assertOk()
            ->assertSee('Tambah Sumber Data')
            ->assertSee('PBI-03 · Admin')
            ->assertSee('Batal');

        $sumber = SumberData::factory()->create(['nama_sumber' => 'Dataset Ubah']);

        $this->get(route('katalog.sumber-data.edit', $sumber))
            ->assertOk()
            ->assertSee('Ubah Sumber Data')
            ->assertSee('Dataset Ubah')
            ->assertSee('Simpan');
    }

    public function test_label_metadata_belum_lengkap_memakai_nama_field_yang_mudah_dibaca(): void
    {
        $sumber = SumberData::factory()->make([
            'jenis_sumber' => SumberData::JENIS_EKSTERNAL,
            'url_sumber' => null,
            'deskripsi_cakupan' => null,
            'interval_penyegaran_jam' => null,
        ]);

        $this->assertSame(
            ['URL sumber', 'Deskripsi cakupan', 'Interval penyegaran'],
            $sumber->labelMetadataBelumLengkap(),
        );
    }

    public function test_tombol_aktivasi_nonaktif_dan_menampilkan_field_yang_kurang(): void
    {
        $sumber = SumberData::factory()->create([
            'jenis_sumber' => SumberData::JENIS_EKSTERNAL,
            'url_sumber' => null,
            'deskripsi_cakupan' => null,
            'interval_penyegaran_jam' => null,
            'is_aktif' => false,
        ]);

        $this->get(route('katalog.sumber-data.show', $sumber))
            ->assertOk()
            ->assertSee('Belum bisa diaktifkan')
            ->assertSee('Lengkapi metadata wajib berikut:')
            ->assertSee('Lengkapi metadata')
            ->assertSee('cursor-not-allowed', false)
            ->assertSee('aria-disabled="true"', false)
            ->assertDontSee(route('katalog.sumber-data.aktifkan', $sumber), false);
    }

    public function test_tombol_aktivasi_tersedia_bila_metadata_lengkap(): void
    {
        $sumber = SumberData::factory()->create(['is_aktif' => false]);

        $this->get(route('katalog.sumber-data.show', $sumber))
            ->assertOk()
            ->assertDontSee('Belum bisa diaktifkan')
            ->assertSee(route('katalog.sumber-data.aktifkan', $sumber), false);
    }

    public function test_katalog_menampilkan_badge_status_kualitas_berwarna(): void
    {
        SumberData::factory()->create([
            'nama_sumber' => 'Dataset Terverifikasi',
            'status_kualitas' => StatusKualitas::VERIFIED,
        ]);
        SumberData::factory()->create([
            'nama_sumber' => 'Dataset Perlu Ditinjau',
            'status_kualitas' => StatusKualitas::NEEDS_REVIEW,
        ]);

        $this->get(route('katalog.sumber-data.index'))
            ->assertOk()
            ->assertSee('Terverifikasi')
            ->assertSee('Perlu ditinjau')
            ->assertSee('bg-amber-soft', false);
    }

    public function test_empty_state_menampilkan_cta_tambah_dataset(): void
    {
        $this->get(route('katalog.sumber-data.index'))
            ->assertOk()
            ->assertSee('Belum ada sumber data.')
            ->assertSee('Tambah dataset')
            ->assertSee(route('katalog.sumber-data.create'), false)
            ->assertDontSee('Reset filter')
            ->assertDontSee('Tidak ada dataset yang cocok dengan filter saat ini.');
    }

    public function test_empty_state_karena_filter_menampilkan_tombol_reset(): void
    {
        SumberData::factory()->create(['nama_sumber' => 'Dataset Lain']);

        $this->get(route('katalog.sumber-data.index', ['cari' => 'tidak-ada']))
            ->assertOk()
            ->assertSee('Belum ada sumber data.')
            ->assertSee('Tidak ada dataset yang cocok dengan filter saat ini.')
            ->assertSee('Reset filter');
    }

    public function test_form_menampilkan_error_di_field_terkait(): void
    {
        $this->from(route('katalog.sumber-data.create'))
            ->followingRedirects()
            ->post(route('katalog.sumber-data.store'), [
                'nama_sumber' => '',
                'jenis_sumber' => SumberData::JENIS_EKSTERNAL,
                'nama_penyedia' => 'NASA',
                'nama_lisensi' => 'Public Domain',
                'format_data' => 'CSV',
                'kode_crs' => '4326',
                'status_kualitas' => 'VERIFIED',
            ])
            ->assertOk()
            ->assertSee('Nama sumber wajib diisi.')
            ->assertSee('Kode CRS harus berformat EPSG diikuti 4-6 digit, contoh EPSG:4326.')
            ->assertSee('aria-invalid="true"', false)
            ->assertSee('nama_sumber-error', false)
            ->assertSee('kode_crs-error', false);
    }

    public function test_detail_meminta_konfirmasi_sebelum_menonaktifkan(): void
    {
        $sumber = SumberData::factory()->aktif()->create();

        $this->get(route('katalog.sumber-data.show', $sumber))
            ->assertOk()
            ->assertSee('Nonaktifkan dataset')
            ->assertSee('confirm(', false);
    }

    public function test_katalog_menyediakan_daftar_card_untuk_mobile_dan_tablet(): void
    {
        SumberData::factory()->create(['nama_sumber' => 'Dataset Mobile']);

        $this->get(route('katalog.sumber-data.index'))
            ->assertOk()
            ->assertSee('lg:hidden', false)
            ->assertSee('hidden lg:block', false)
            ->assertDontSee('md:hidden', false)
            ->assertSee('Daftar sumber data')
            ->assertSee('Dataset Mobile');
    }

    public function test_pagination_mempertahankan_filter_aktif(): void
    {
        foreach (range(1, 16) as $nomor) {
            SumberData::factory()->create([
                'nama_sumber' => sprintf('Dataset Uji %02d', $nomor),
                'jenis_sumber' => SumberData::JENIS_INTERNAL,
            ]);
        }

        $respons = $this->get(route('katalog.sumber-data.index', [
            'cari' => 'Dataset Uji',
            'jenis_sumber' => SumberData::JENIS_INTERNAL,
        ]));

        $respons->assertOk();

        $paginator = $respons->viewData('sumberData');

        $this->assertSame(16, $paginator->total());
        $this->assertSame(15, $paginator->perPage());
        $this->assertStringContainsString('cari=Dataset', $paginator->url(2));
        $this->assertStringContainsString('jenis_sumber=INTERNAL', $paginator->url(2));
    }

    public function test_scope_tersedia_untuk_pilihan_dan_ringkasan_atribusi(): void
    {
        $aktif = SumberData::factory()->aktif()->create([
            'nama_sumber' => 'Sumber Aktif A',
            'nama_penyedia' => 'KLHK',
            'nama_lisensi' => 'CC BY 4.0',
            'kode_crs' => 'EPSG:4326',
        ]);
        $nonaktif = SumberData::factory()->nonaktif()->create(['nama_sumber' => 'Sumber Nonaktif B']);

        $pilihanAktif = SumberData::query()->tersediaUntukPilihan()->pluck('sumber_id')->all();
        $this->assertSame([$aktif->sumber_id], $pilihanAktif);

        $pilihanDenganEksisting = SumberData::query()
            ->tersediaUntukPilihan($nonaktif->sumber_id)
            ->pluck('sumber_id')
            ->all();
        $this->assertEqualsCanonicalizing([$aktif->sumber_id, $nonaktif->sumber_id], $pilihanDenganEksisting);

        $this->assertSame(
            'Sumber Aktif A — KLHK (CC BY 4.0 · EPSG:4326)',
            $aktif->ringkasanAtribusi(),
        );
    }

    public function test_katalog_dan_detail_menampilkan_penggunaan_pada_kawasan_dan_batas(): void
    {
        $sumber = SumberData::factory()->aktif()->create(['nama_sumber' => 'Dataset Batas Provinsi']);
        $wilayah = Wilayah::factory()->create(['nama_wilayah' => 'Riau', 'is_aktif' => true]);

        $kawasan = Kawasan::create([
            'wilayah_id' => $wilayah->wilayah_id,
            'nama_kawasan' => 'Taman Nasional Tesso Nilo',
            'jenis_kawasan' => 'TAMAN_NASIONAL',
            'status_kawasan' => 'AKTIF',
            'sumber_id' => $sumber->sumber_id,
            'is_aktif' => true,
            'dibuat_oleh' => $this->admin->pengguna_id,
        ]);

        DB::statement(
            "INSERT INTO batas_kawasan (kawasan_id, nomor_versi, data_geometri, kode_crs, sumber_id, status_validasi, pesan_validasi, dibuat_oleh, dibuat_pada)
             VALUES (?, ?, ST_Multi(ST_SetSRID(ST_GeomFromText('POLYGON((101.44 -0.50, 101.45 -0.50, 101.45 -0.51, 101.44 -0.51, 101.44 -0.50))'), 4326)), ?, ?, ?, ?, ?, NOW())",
            [
                $kawasan->kawasan_id,
                1,
                'EPSG:4326',
                $sumber->sumber_id,
                'VALID',
                'Validasi uji integrasi',
                $this->admin->pengguna_id,
            ],
        );

        $this->get(route('katalog.sumber-data.index'))
            ->assertOk()
            ->assertSee('Dipakai 1 kawasan · 1 batas');

        $this->get(route('katalog.sumber-data.show', $sumber))
            ->assertOk()
            ->assertSee('Penggunaan dataset')
            ->assertSee('2 rujukan')
            ->assertSee('Taman Nasional Tesso Nilo')
            ->assertSee('Riau')
            ->assertSee('Versi #1')
            ->assertSee(route('admin.kawasan.show', $kawasan), false)
            ->assertSee(route('admin.kawasan.batas.index', $kawasan->kawasan_id), false)
            ->assertSee('Dataset ini sedang dirujuk oleh 2 rekaman modul lain.');
    }

    public function test_detail_menampilkan_status_kosong_bila_belum_dirujuk_modul_lain(): void
    {
        $sumber = SumberData::factory()->aktif()->create();

        $this->get(route('katalog.sumber-data.show', $sumber))
            ->assertOk()
            ->assertSee('0 rujukan')
            ->assertSee('Belum ada kawasan, batas kawasan, titik panas, atau log sinkronisasi yang merujuk dataset ini.')
            ->assertDontSee('Dataset ini sedang dirujuk oleh');
    }

    public function test_form_menyembunyikan_url_sumber_untuk_internal_dan_menampilkan_untuk_eksternal(): void
    {
        $internal = SumberData::factory()->create([
            'jenis_sumber' => SumberData::JENIS_INTERNAL,
            'url_sumber' => null,
        ]);
        $eksternal = SumberData::factory()->create([
            'jenis_sumber' => SumberData::JENIS_EKSTERNAL,
            'url_sumber' => 'https://firms.modaps.eosdis.nasa.gov/',
        ]);

        $this->get(route('katalog.sumber-data.create'))
            ->assertOk()
            ->assertSee('id="wadah-url-sumber" class="sm:col-span-2 hidden"', false);

        $this->get(route('katalog.sumber-data.edit', $internal))
            ->assertOk()
            ->assertSee('id="wadah-url-sumber" class="sm:col-span-2 hidden"', false);

        $this->get(route('katalog.sumber-data.edit', $eksternal))
            ->assertOk()
            ->assertSee('id="wadah-url-sumber" class="sm:col-span-2"', false);
    }

    public function test_pesan_exception_dan_riwayat_memakai_label_ramah_pengguna(): void
    {
        $exception = new MetadataBelumLengkapException(['url_sumber', 'interval_penyegaran_jam']);

        $this->assertSame(
            'Metadata belum lengkap: URL sumber, Interval penyegaran.',
            $exception->getMessage(),
        );

        $this->assertSame('Internal', RiwayatSumberData::formatNilai('jenis_sumber', 'INTERNAL'));
        $this->assertSame('Eksternal', RiwayatSumberData::formatNilai('jenis_sumber', 'EKSTERNAL'));
    }

    public function test_seeder_sumber_data_idempoten_dan_mengisi_contoh_dataset(): void
    {
        $this->seed(SumberDataSeeder::class);
        $this->seed(SumberDataSeeder::class);

        $this->assertSame(4, SumberData::query()->count());
        $this->assertSame(3, SumberData::query()->aktif()->count());
        $this->assertSame(1, SumberData::query()->nonaktif()->count());
    }

    public function test_edit_dataset_aktif_menampilkan_banner_error_dengan_label_ramah(): void
    {
        $sumber = SumberData::factory()->aktif()->create([
            'jenis_sumber' => SumberData::JENIS_EKSTERNAL,
            'interval_penyegaran_jam' => 24,
        ]);

        $this->from(route('katalog.sumber-data.edit', $sumber))
            ->followingRedirects()
            ->put(route('katalog.sumber-data.update', $sumber), [
                'interval_penyegaran_jam' => null,
            ])
            ->assertOk()
            ->assertSee('Metadata belum lengkap: Interval penyegaran.')
            ->assertDontSee('interval_penyegaran_jam-error')
            ->assertDontSee('Metadata belum lengkap: interval_penyegaran_jam');
    }
}
