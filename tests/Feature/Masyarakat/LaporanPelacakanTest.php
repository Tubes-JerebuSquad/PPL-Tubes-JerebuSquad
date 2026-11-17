<?php

namespace Tests\Feature\Masyarakat;

use App\Enums\StatusLaporan;
use App\Models\Laporan;
use App\Models\Pengguna;
use App\Models\PeristiwaLaporan;
use App\Models\RevisiLaporan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LaporanPelacakanTest extends TestCase
{
    use RefreshDatabase;

    private Pengguna $pelapor;

    private Pengguna $pelaporLain;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pelapor = Pengguna::factory()->masyarakat()->create([
            'nama_lengkap' => 'Siti Pelapor',
        ]);
        $this->pelaporLain = Pengguna::factory()->create([
            'peran_id' => $this->pelapor->peran_id,
            'nama_lengkap' => 'Pelapor Lain',
        ]);
    }

    public function test_daftar_hanya_menampilkan_laporan_milik_pelapor_sendiri(): void
    {
        Laporan::factory()->create([
            'pelapor_id' => $this->pelapor->pengguna_id,
            'nomor_laporan' => 'RW-SAYA-001',
        ]);
        Laporan::factory()->create([
            'pelapor_id' => $this->pelaporLain->pengguna_id,
            'nomor_laporan' => 'RW-ORANG-999',
        ]);

        $this->actingAs($this->pelapor)
            ->get(route('masyarakat.laporan.index'))
            ->assertOk()
            ->assertSee('RW-SAYA-001')
            ->assertDontSee('RW-ORANG-999')
            ->assertSee('PBI-09 · Masyarakat');
    }

    public function test_pencarian_dan_filter_status_menyaring_laporan_milik_sendiri(): void
    {
        Laporan::factory()->perluPerbaikan()->create([
            'pelapor_id' => $this->pelapor->pengguna_id,
            'nomor_laporan' => 'RW-2026-001',
            'deskripsi' => 'Pembukaan lahan gambut tanpa izin.',
        ]);
        Laporan::factory()->status(StatusLaporan::DIVERIFIKASI)->create([
            'pelapor_id' => $this->pelapor->pengguna_id,
            'nomor_laporan' => 'RW-2026-002',
            'deskripsi' => 'Titik asap dekat sungai.',
        ]);

        $this->actingAs($this->pelapor)
            ->get(route('masyarakat.laporan.index', ['status' => StatusLaporan::PERLU_PERBAIKAN->value]))
            ->assertOk()
            ->assertSee('RW-2026-001')
            ->assertDontSee('RW-2026-002');

        $this->actingAs($this->pelapor)
            ->get(route('masyarakat.laporan.index', ['cari' => 'gambut']))
            ->assertOk()
            ->assertSee('RW-2026-001')
            ->assertDontSee('RW-2026-002');
    }

    public function test_empty_state_ditampilkan_saat_belum_ada_laporan_atau_filter_kosong(): void
    {
        $this->actingAs($this->pelapor)
            ->get(route('masyarakat.laporan.index'))
            ->assertOk()
            ->assertSee('Belum ada laporan.');

        Laporan::factory()->create([
            'pelapor_id' => $this->pelapor->pengguna_id,
            'nomor_laporan' => 'RW-ADA-001',
        ]);

        $this->actingAs($this->pelapor)
            ->get(route('masyarakat.laporan.index', ['cari' => 'tidak-cocok']))
            ->assertOk()
            ->assertSee('Tidak ada laporan Anda yang cocok dengan filter saat ini.')
            ->assertSee('Reset filter');
    }

    public function test_detail_menampilkan_status_waktu_perubahan_dan_riwayat_revisi(): void
    {
        $laporan = Laporan::factory()->perluPerbaikan()->create([
            'pelapor_id' => $this->pelapor->pengguna_id,
            'nomor_laporan' => 'RW-DETAIL-01',
            'deskripsi' => 'Indikasi penebangan liar di lereng timur.',
        ]);

        RevisiLaporan::factory()->create([
            'laporan_id' => $laporan->id,
            'nomor_revisi' => 1,
            'deskripsi' => 'Indikasi penebangan liar di lereng timur.',
        ]);

        PeristiwaLaporan::factory()->create([
            'laporan_id' => $laporan->id,
            'jenis_peristiwa' => 'DIAJUKAN',
            'status_lama' => null,
            'status_baru' => StatusLaporan::MENUNGGU_VERIFIKASI->value,
            'waktu_peristiwa' => '2026-11-10 08:30:00',
        ]);

        PeristiwaLaporan::factory()->create([
            'laporan_id' => $laporan->id,
            'jenis_peristiwa' => 'PERMINTAAN_PERBAIKAN',
            'status_lama' => StatusLaporan::MENUNGGU_VERIFIKASI->value,
            'status_baru' => StatusLaporan::PERLU_PERBAIKAN->value,
            'catatan_publik' => 'Mohon lampirkan foto area terdampak lebih jelas.',
            'waktu_peristiwa' => '2026-11-11 14:15:00',
        ]);

        $this->actingAs($this->pelapor)
            ->get(route('masyarakat.laporan.show', $laporan))
            ->assertOk()
            ->assertSee('RW-DETAIL-01')
            ->assertSee('Perlu Perbaikan')
            ->assertSee('Mohon lampirkan foto area terdampak lebih jelas.')
            ->assertSee('10 Nov 2026 • 08:30')
            ->assertSee('11 Nov 2026 • 14:15')
            ->assertSee('Revisi #1')
            ->assertSee('Perbaiki &amp; kirim ulang', false);
    }

    public function test_laporan_terkunci_tidak_menampilkan_tombol_perbaiki(): void
    {
        $laporan = Laporan::factory()->status(StatusLaporan::MENUNGGU_VERIFIKASI)->create([
            'pelapor_id' => $this->pelapor->pengguna_id,
            'nomor_laporan' => 'RW-KUNCI-01',
        ]);

        $this->actingAs($this->pelapor)
            ->get(route('masyarakat.laporan.show', $laporan))
            ->assertOk()
            ->assertSee('sudah terkunci dalam tahap pemeriksaan')
            ->assertDontSee(route('masyarakat.laporan.edit', $laporan), false);
    }
}
