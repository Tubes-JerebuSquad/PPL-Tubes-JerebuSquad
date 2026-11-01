<?php

namespace Tests\Feature\Model;

use App\Models\Pengguna;
use App\Models\Peran;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PenggunaPeranTest extends TestCase
{
    use RefreshDatabase;

    public function test_factory_membuat_pengguna_beserta_perannya(): void
    {
        $pengguna = Pengguna::factory()->create();

        $this->assertNotNull($pengguna->peran);
        $this->assertDatabaseHas('pengguna', ['pengguna_id' => $pengguna->pengguna_id]);
    }

    public function test_is_admin_hanya_benar_untuk_peran_admin(): void
    {
        $admin = Pengguna::factory()->admin()->create();
        $masyarakat = Pengguna::factory()->masyarakat()->create();

        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($masyarakat->isAdmin());
    }

    public function test_is_aktif_mengikuti_status_akun(): void
    {
        $this->assertTrue(Pengguna::factory()->create()->isAktif());
        $this->assertFalse(Pengguna::factory()->nonaktif()->create()->isAktif());
    }

    public function test_password_hash_disembunyikan_dan_otomatis_di_hash(): void
    {
        $pengguna = Pengguna::factory()->create(['password_hash' => 'rahasia123']);

        $this->assertArrayNotHasKey('password_hash', $pengguna->toArray());
        $this->assertNotSame('rahasia123', $pengguna->password_hash);
        $this->assertTrue(password_verify('rahasia123', $pengguna->password_hash));
    }

    public function test_factory_peran_mewakili_empat_peran_proposal(): void
    {
        $this->assertSame('Admin', Peran::factory()->admin()->create()->nama_peran);
        $this->assertSame('Pengawas', Peran::factory()->pengawas()->create()->nama_peran);
        $this->assertSame('Petugas Lapangan', Peran::factory()->petugasLapangan()->create()->nama_peran);
        $this->assertSame('Masyarakat', Peran::factory()->masyarakat()->create()->nama_peran);
    }

    public function test_peran_memiliki_relasi_ke_pengguna(): void
    {
        $peran = Peran::factory()->pengawas()->create();
        Pengguna::factory()->create(['peran_id' => $peran->peran_id]);

        $this->assertSame(1, $peran->pengguna()->count());
    }
}
