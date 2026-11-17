<?php

namespace Tests\Feature\Model;

use App\Models\Pengguna;
use App\Models\Peran;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PenggunaPencarianTest extends TestCase
{
    use RefreshDatabase;

    public function test_cari_berdasarkan_nama_atau_email(): void
    {
        Pengguna::factory()->create(['nama_lengkap' => 'Budi Santoso', 'email' => 'budi@contoh.test']);
        Pengguna::factory()->create(['nama_lengkap' => 'Siti Aminah', 'email' => 'siti@contoh.test']);

        $this->assertCount(1, Pengguna::cari('budi')->get());
        $this->assertCount(1, Pengguna::cari('siti@contoh')->get());
        $this->assertCount(2, Pengguna::cari('')->get());
    }

    public function test_filter_berdasarkan_peran(): void
    {
        $admin = Peran::factory()->admin()->create();
        Pengguna::factory()->create(['peran_id' => $admin->peran_id]);
        Pengguna::factory()->masyarakat()->create();

        $this->assertCount(1, Pengguna::berperan($admin->peran_id)->get());
        $this->assertCount(2, Pengguna::berperan(null)->get());
    }

    public function test_filter_berdasarkan_status_akun(): void
    {
        Pengguna::factory()->create();
        Pengguna::factory()->nonaktif()->create();

        $this->assertCount(1, Pengguna::berstatus(Pengguna::STATUS_NONAKTIF)->get());
        $this->assertCount(2, Pengguna::berstatus(null)->get());
    }
}