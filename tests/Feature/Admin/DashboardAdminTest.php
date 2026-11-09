<?php

namespace Tests\Feature\Admin;

use App\Models\Pengguna;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_tamu_tidak_bisa_mengakses_dashboard_admin(): void
    {
        $response = $this->get('/admin/dashboard');

        $response->assertRedirect('/login');
    }

    public function test_warga_biasa_ditolak_mengakses_dashboard_admin(): void
    {
        $warga = Pengguna::factory()->masyarakat()->create();

        $response = $this->actingAs($warga)->get('/admin/dashboard');

        $response->assertForbidden();
    }

    public function test_admin_dapat_mengakses_dashboard_admin_dan_melihat_statistik(): void
    {
        $admin = Pengguna::factory()->admin()->create([
            'nama_lengkap' => 'Admin Utama RimbaWatch',
        ]);

        $response = $this->actingAs($admin)->get('/admin/dashboard');

        $response->assertOk();
        $response->assertSee('Selamat Datang, Admin Utama RimbaWatch!');
        $response->assertSee('Total Pengguna');
        $response->assertSee('Pengguna Aktif');
        $response->assertSee('Portal Administrasi RimbaWatch');
    }
}
