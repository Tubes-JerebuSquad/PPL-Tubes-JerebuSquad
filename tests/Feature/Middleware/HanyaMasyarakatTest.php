<?php

namespace Tests\Feature\Middleware;

use App\Models\Pengguna;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class HanyaMasyarakatTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware(['web', 'masyarakat'])->get('/_tes-masyarakat', fn () => 'ok');
    }

    public function test_masyarakat_aktif_boleh_masuk(): void
    {
        $masyarakat = Pengguna::factory()->masyarakat()->create();

        $this->actingAs($masyarakat)->get('/_tes-masyarakat')->assertOk();
    }

    public function test_peran_selain_masyarakat_ditolak(): void
    {
        $admin = Pengguna::factory()->admin()->create();
        $pengawas = Pengguna::factory()->pengawas()->create();
        $petugas = Pengguna::factory()->petugasLapangan()->create();

        $this->actingAs($admin)->get('/_tes-masyarakat')->assertForbidden();
        $this->actingAs($pengawas)->get('/_tes-masyarakat')->assertForbidden();
        $this->actingAs($petugas)->get('/_tes-masyarakat')->assertForbidden();
    }

    public function test_masyarakat_nonaktif_ditolak(): void
    {
        $masyarakat = Pengguna::factory()->masyarakat()->nonaktif()->create();

        $this->actingAs($masyarakat)->get('/_tes-masyarakat')->assertForbidden();
    }

    public function test_tamu_yang_belum_login_ditolak(): void
    {
        $this->get('/_tes-masyarakat')->assertForbidden();
    }
}
