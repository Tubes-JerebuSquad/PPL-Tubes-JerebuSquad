<?php

namespace Tests\Feature\Middleware;

use App\Models\Pengguna;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class HanyaPengawasTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware(['web', 'pengawas'])->get('/_tes-pengawas', fn () => 'ok');
    }

    public function test_pengawas_aktif_boleh_masuk(): void
    {
        $pengawas = Pengguna::factory()->pengawas()->create();

        $this->actingAs($pengawas)->get('/_tes-pengawas')->assertOk();
    }

    public function test_peran_selain_pengawas_ditolak(): void
    {
        $admin = Pengguna::factory()->admin()->create();
        $petugas = Pengguna::factory()->petugasLapangan()->create();
        $masyarakat = Pengguna::factory()->masyarakat()->create();

        $this->actingAs($admin)->get('/_tes-pengawas')->assertForbidden();
        $this->actingAs($petugas)->get('/_tes-pengawas')->assertForbidden();
        $this->actingAs($masyarakat)->get('/_tes-pengawas')->assertForbidden();
    }

    public function test_pengawas_nonaktif_ditolak(): void
    {
        $pengawas = Pengguna::factory()->pengawas()->nonaktif()->create();

        $this->actingAs($pengawas)->get('/_tes-pengawas')->assertForbidden();
    }

    public function test_tamu_yang_belum_login_ditolak(): void
    {
        $this->get('/_tes-pengawas')->assertForbidden();
    }
}
