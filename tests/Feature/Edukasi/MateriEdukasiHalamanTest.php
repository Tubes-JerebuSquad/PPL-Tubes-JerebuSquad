<?php

namespace Tests\Feature\Edukasi;

use App\Models\MateriEdukasi;
use App\Models\Pengguna;
use Database\Seeders\MateriEdukasiSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MateriEdukasiHalamanTest extends TestCase
{
    use RefreshDatabase;

    private Pengguna $pengawas;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pengawas = Pengguna::factory()->pengawas()->create();
    }

    public function test_pengawas_dapat_melihat_daftar_filter_form_dan_detail_materi(): void
    {
        $terbit = MateriEdukasi::factory()->terbit()->create([
            'dibuat_oleh' => $this->pengawas->pengguna_id,
            'judul' => 'Restorasi Gambut Nusantara',
            'kategori' => 'Rehabilitasi Lahan',
            'referensi_sumber' => 'BRGM 2025',
        ]);
        $draf = MateriEdukasi::factory()->draf()->create([
            'dibuat_oleh' => $this->pengawas->pengguna_id,
            'judul' => 'Draf Patroli Hutan',
            'kategori' => 'Pelestarian Hutan',
        ]);

        $this->actingAs($this->pengawas)
            ->get(route('pengawas.edukasi.index'))
            ->assertOk()
            ->assertSee('PBI-16 · Pengawas')
            ->assertSee('Restorasi Gambut Nusantara')
            ->assertSee('Draf Patroli Hutan')
            ->assertSee('Diterbitkan')
            ->assertSee('Draf');

        $this->actingAs($this->pengawas)
            ->get(route('pengawas.edukasi.index', ['cari' => 'Gambut', 'status_publikasi' => 'PUBLISHED']))
            ->assertOk()
            ->assertSee('Restorasi Gambut Nusantara')
            ->assertDontSee('Draf Patroli Hutan');

        $this->actingAs($this->pengawas)
            ->get(route('pengawas.edukasi.create'))
            ->assertOk()
            ->assertSee('Tambah Materi Edukasi');

        $this->actingAs($this->pengawas)
            ->get(route('pengawas.edukasi.edit', $terbit))
            ->assertOk()
            ->assertSee('Ubah Materi Edukasi')
            ->assertSee('Restorasi Gambut Nusantara');

        $this->actingAs($this->pengawas)
            ->get(route('pengawas.edukasi.show', $draf))
            ->assertOk()
            ->assertSee('Draf Patroli Hutan')
            ->assertSee('Terbitkan materi')
            ->assertSee('Arsipkan materi');
    }

    public function test_halaman_publik_hanya_menampilkan_materi_terbit_dan_menyembunyikan_draf_serta_arsip(): void
    {
        $terbit = MateriEdukasi::factory()->terbit()->create([
            'dibuat_oleh' => $this->pengawas->pengguna_id,
            'judul' => 'Panduan Konservasi Terbit',
            'kategori' => 'Pelestarian Hutan',
            'konten' => 'Isi lengkap mengenai pelestarian kawasan hutan hijau.',
            'referensi_sumber' => 'KLHK · Panduan Konservasi',
        ]);
        MateriEdukasi::factory()->draf()->create([
            'dibuat_oleh' => $this->pengawas->pengguna_id,
            'judul' => 'Rahasia Draf Edukasi',
        ]);
        MateriEdukasi::factory()->arsip()->create([
            'dibuat_oleh' => $this->pengawas->pengguna_id,
            'judul' => 'Materi Lama Diarsipkan',
        ]);

        $masyarakat = Pengguna::factory()->masyarakat()->create();

        // Akses sebagai pengunjung publik (tamu)
        $this->get(route('edukasi.index'))
            ->assertOk()
            ->assertSee('Panduan Konservasi Terbit')
            ->assertSee('KLHK · Panduan Konservasi')
            ->assertDontSee('Rahasia Draf Edukasi')
            ->assertDontSee('Materi Lama Diarsipkan')
            ->assertDontSee('Kelola materi');

        // Akses sebagai Masyarakat: bisa baca daftar & detail, tanpa tombol manajemen
        $this->actingAs($masyarakat)
            ->get(route('edukasi.index'))
            ->assertOk()
            ->assertSee('Panduan Konservasi Terbit')
            ->assertDontSee('Rahasia Draf Edukasi')
            ->assertDontSee('Materi Lama Diarsipkan')
            ->assertDontSee(route('pengawas.edukasi.create'), false);

        $this->actingAs($masyarakat)
            ->get(route('edukasi.show', $terbit))
            ->assertOk()
            ->assertSee('Panduan Konservasi Terbit')
            ->assertSee('Isi lengkap mengenai pelestarian kawasan hutan hijau.')
            ->assertSee('Pelestarian Hutan')
            ->assertSee('KLHK · Panduan Konservasi')
            ->assertSee($terbit->tanggalTampil()->format('d M Y'))
            ->assertDontSee(route('pengawas.edukasi.edit', $terbit), false);
    }

    public function test_pencarian_dan_filter_kategori_pada_halaman_publik(): void
    {
        MateriEdukasi::factory()->terbit()->create([
            'dibuat_oleh' => $this->pengawas->pengguna_id,
            'judul' => 'Pencegahan Karhutla Gambut',
            'kategori' => 'Pencegahan Kebakaran',
        ]);
        MateriEdukasi::factory()->terbit()->create([
            'dibuat_oleh' => $this->pengawas->pengguna_id,
            'judul' => 'Penanaman Bibit Mangrove',
            'kategori' => 'Rehabilitasi Lahan',
        ]);

        $this->get(route('edukasi.index', ['kategori' => 'Rehabilitasi Lahan']))
            ->assertOk()
            ->assertSee('Penanaman Bibit Mangrove')
            ->assertDontSee('Pencegahan Karhutla Gambut');

        $this->get(route('edukasi.index', ['cari' => 'tidak-ditemukan']))
            ->assertOk()
            ->assertSee('Belum ada materi edukasi yang diterbitkan.')
            ->assertSee('Reset filter');
    }

    public function test_alur_lengkap_buat_ubah_terbitkan_baca_dan_arsipkan(): void
    {
        $masyarakat = Pengguna::factory()->masyarakat()->create();

        // 1. Pengawas membuat draf
        $this->actingAs($this->pengawas)->post(route('pengawas.edukasi.store'), [
            'judul' => 'Edukasi Pelestarian Mata Air Hutan',
            'kategori' => 'Pelestarian Hutan',
            'konten' => 'Tutupan vegetasi menjaga resapan air di kawasan hulu.',
            'referensi_sumber' => 'Balai Pengelolaan DAS 2025',
        ])->assertSessionHasNoErrors();

        $materi = MateriEdukasi::query()->where('judul', 'Edukasi Pelestarian Mata Air Hutan')->firstOrFail();

        // Draf belum tampil ke Masyarakat (sesi terpisah)
        $this->flushSession();
        $this->actingAs($masyarakat)
            ->get(route('edukasi.index'))
            ->assertOk()
            ->assertDontSee($materi->judul);
        $this->actingAs($masyarakat)->get(route('edukasi.show', $materi))->assertNotFound();

        // 2. Pengawas menerbitkan -> tampil ke Masyarakat
        $this->flushSession();
        $this->actingAs($this->pengawas)->patch(route('pengawas.edukasi.terbitkan', $materi))->assertSessionHasNoErrors();

        $this->flushSession();
        $this->actingAs($masyarakat)->get(route('edukasi.index'))->assertOk()->assertSee($materi->judul);
        $this->actingAs($masyarakat)->get(route('edukasi.show', $materi))->assertOk()->assertSee('Balai Pengelolaan DAS 2025');

        // 3. Pengawas mengarsipkan -> hilang lagi dari publik
        $this->flushSession();
        $this->actingAs($this->pengawas)->patch(route('pengawas.edukasi.arsipkan', $materi))->assertSessionHasNoErrors();

        $this->flushSession();
        $this->actingAs($masyarakat)->get(route('edukasi.index'))->assertOk()->assertDontSee($materi->judul);
        $this->actingAs($masyarakat)->get(route('edukasi.show', $materi))->assertNotFound();
    }

    public function test_seeder_materi_edukasi_idempoten(): void
    {
        $this->seed(MateriEdukasiSeeder::class);
        $this->seed(MateriEdukasiSeeder::class);

        $this->assertSame(4, MateriEdukasi::query()->count());
        $this->assertSame(2, MateriEdukasi::query()->terbit()->count());
        $this->assertSame(1, MateriEdukasi::query()->draf()->count());
        $this->assertSame(1, MateriEdukasi::query()->arsip()->count());
    }
}
