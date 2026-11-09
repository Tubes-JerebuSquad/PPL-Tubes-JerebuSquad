<?php

namespace Tests\Feature\Middleware;

use App\Models\Pengguna;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class HanyaAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware(['web', 'admin'])->get('/_tes-admin', fn () => 'ok');
    }

    public function test_admin_aktif_boleh_masuk(): void
    {
        $admin = Pengguna::factory()->admin()->create();

        $this->actingAs($admin)->get('/_tes-admin')->assertOk();
    }

    public function test_peran_selain_admin_ditolak(): void
    {
        $masyarakat = Pengguna::factory()->masyarakat()->create();

        $this->actingAs($masyarakat)->get('/_tes-admin')->assertForbidden();
    }

    public function test_admin_nonaktif_ditolak(): void
    {
        $admin = Pengguna::factory()->admin()->nonaktif()->create();

        $this->actingAs($admin)->get('/_tes-admin')->assertForbidden();
    }

    public function test_tamu_yang_belum_login_ditolak(): void
    {
        $this->get('/_tes-admin')->assertForbidden();
    }
}