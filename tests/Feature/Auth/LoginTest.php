<?php

namespace Tests\Feature\Auth;

use App\Models\Pengguna;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_halaman_login_dapat_ditampilkan(): void
    {
        $response = $this->get('/login');

        $response->assertOk();
        $response->assertSee('Masuk Akun Rimba');
        $response->assertSee('RimbaWatch');
    }

    public function test_pengguna_dapat_masuk_menggunakan_email(): void
    {
        $pengguna = Pengguna::factory()->masyarakat()->create([
            'email' => 'warga@rimbawatch.org',
            'password_hash' => 'password123',
        ]);

        $response = $this->post('/login', [
            'email' => 'warga@rimbawatch.org',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('masyarakat.dashboard'));
        $this->assertAuthenticatedAs($pengguna);
    }

    public function test_pengguna_dapat_masuk_menggunakan_nomor_telepon(): void
    {
        $pengguna = Pengguna::factory()->masyarakat()->create([
            'telepon' => '081234567890',
            'password_hash' => 'password123',
        ]);

        $response = $this->post('/login', [
            'email' => '081234567890',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('masyarakat.dashboard'));
        $this->assertAuthenticatedAs($pengguna);
    }

    public function test_pengguna_tidak_dapat_masuk_dengan_kata_sandi_salah(): void
    {
        Pengguna::factory()->masyarakat()->create([
            'email' => 'warga@rimbawatch.org',
            'password_hash' => 'password123',
        ]);

        $response = $this->from('/login')->post('/login', [
            'email' => 'warga@rimbawatch.org',
            'password' => 'passwordsalah',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_pengguna_nonaktif_tidak_dapat_masuk(): void
    {
        Pengguna::factory()->nonaktif()->create([
            'email' => 'nonaktif@rimbawatch.org',
            'password_hash' => 'password123',
        ]);

        $response = $this->from('/login')->post('/login', [
            'email' => 'nonaktif@rimbawatch.org',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_pengguna_dapat_melakukan_logout(): void
    {
        $pengguna = Pengguna::factory()->create();

        $response = $this->actingAs($pengguna)->post('/logout');

        $response->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_halaman_login_dapat_ditampilkan_di_root(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Masuk Akun Rimba');
    }

    public function test_admin_diarahkan_ke_dashboard_admin(): void
    {
        $admin = Pengguna::factory()->admin()->create([
            'email' => 'admin@rimbawatch.org',
            'password_hash' => 'admin123',
        ]);

        $response = $this->post('/login', [
            'email' => 'admin@rimbawatch.org',
            'password' => 'admin123',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin);
    }

    public function test_pengawas_dapat_masuk_dan_melihat_portal_pengawas(): void
    {
        $pengawas = Pengguna::factory()->pengawas()->create([
            'email' => 'pengawas@rimbawatch.org',
            'password_hash' => 'password123',
            'nama_lengkap' => 'Budi Pengawas',
        ]);

        $response = $this->post('/login', [
            'email' => 'pengawas@rimbawatch.org',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('pengawas.dashboard'));
        $this->assertAuthenticatedAs($pengawas);

        // Setelah login, saat membuka root langsung diarahkan ke dashboard pengawas
        $portalResponse = $this->actingAs($pengawas)->get('/');
        $portalResponse->assertRedirect(route('pengawas.dashboard'));

        $dashboardResponse = $this->actingAs($pengawas)->get('/pengawas/dashboard');
        $dashboardResponse->assertOk();
        $dashboardResponse->assertSee('Portal Pengawas RimbaWatch');
        $dashboardResponse->assertSee('Budi Pengawas');
    }

    public function test_petugas_lapangan_dapat_masuk_dan_melihat_portal_petugas(): void
    {
        $petugas = Pengguna::factory()->petugasLapangan()->create([
            'email' => 'petugas@rimbawatch.org',
            'password_hash' => 'password123',
            'nama_lengkap' => 'Joko Petugas',
        ]);

        $response = $this->post('/login', [
            'email' => 'petugas@rimbawatch.org',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('petugas.dashboard'));
        $this->assertAuthenticatedAs($petugas);

        $dashboardResponse = $this->actingAs($petugas)->get('/petugas/dashboard');
        $dashboardResponse->assertOk();
        $dashboardResponse->assertSee('Portal Petugas Lapangan RimbaWatch');
        $dashboardResponse->assertSee('Joko Petugas');
    }

    public function test_masyarakat_dapat_masuk_dan_melihat_portal_masyarakat(): void
    {
        $warga = Pengguna::factory()->masyarakat()->create([
            'email' => 'warga@rimbawatch.org',
            'password_hash' => 'password123',
            'nama_lengkap' => 'Siti Rahma',
        ]);

        $dashboardResponse = $this->actingAs($warga)->get('/masyarakat/dashboard');
        $dashboardResponse->assertOk();
        $dashboardResponse->assertSee('Portal Komunitas dan Warga RimbaWatch');
        $dashboardResponse->assertSee('Siti Rahma');
    }

    public function test_masyarakat_ditolak_mengakses_dashboard_pengawas_dan_petugas(): void
    {
        $warga = Pengguna::factory()->masyarakat()->create();

        $this->actingAs($warga)->get('/pengawas/dashboard')->assertForbidden();
        $this->actingAs($warga)->get('/petugas/dashboard')->assertForbidden();
        $this->actingAs($warga)->get('/admin/dashboard')->assertForbidden();
    }

    public function test_pengawas_ditolak_mengakses_dashboard_admin(): void
    {
        $pengawas = Pengguna::factory()->pengawas()->create();

        $this->actingAs($pengawas)->get('/admin/dashboard')->assertForbidden();
    }

    public function test_seeder_pengguna_berhasil_mendaftarkan_semua_peran(): void
    {
        $this->seed(\Database\Seeders\PeranSeeder::class);
        $this->seed(\Database\Seeders\PenggunaSeeder::class);

        $this->assertDatabaseHas('pengguna', ['email' => 'admin@rimbawatch.org']);
        $this->assertDatabaseHas('pengguna', ['email' => 'pengawas@rimbawatch.org']);
        $this->assertDatabaseHas('pengguna', ['email' => 'petugas@rimbawatch.org']);
        $this->assertDatabaseHas('pengguna', ['email' => 'masyarakat@rimbawatch.org']);
        $this->assertDatabaseHas('pengguna', ['email' => 'warga@rimbawatch.org']);
        $this->assertDatabaseHas('pengguna', ['email' => 'nonaktif@rimbawatch.org', 'status_akun' => 'INACTIVE']);
    }
}
