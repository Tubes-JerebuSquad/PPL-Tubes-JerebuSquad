<?php

namespace Tests\Feature\Admin;

use App\Models\LogAudit;
use App\Models\Pengguna;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RiwayatAuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dapat_melihat_riwayat(): void
    {
        $admin = Pengguna::factory()->admin()->create(['nama_lengkap' => 'Admin Utama']);
        $target = Pengguna::factory()->create(['nama_lengkap' => 'Budi Santoso']);

        LogAudit::catat($admin, $target, LogAudit::AKSI_PERAN_DIUBAH, [
            'peran_id' => ['dari' => 1, 'menjadi' => 2],
        ]);

        $this->actingAs($admin)
            ->get(route('admin.audit.index'))
            ->assertOk()
            ->assertSee('Riwayat Perubahan')
            ->assertSee('Admin Utama')
            ->assertSee('Budi Santoso');
    }

    public function test_filter_jenis_perubahan_menyaring_daftar(): void
    {
        $admin = Pengguna::factory()->admin()->create();
        $citra = Pengguna::factory()->create(['nama_lengkap' => 'Citra Lestari']);
        $dewi = Pengguna::factory()->create(['nama_lengkap' => 'Dewi Anggraini']);

        LogAudit::catat($admin, $citra, LogAudit::AKSI_AKUN_DIAKTIFKAN);
        LogAudit::catat($admin, $dewi, LogAudit::AKSI_AKUN_DINONAKTIFKAN);

        $this->actingAs($admin)
            ->get(route('admin.audit.index', ['aksi' => LogAudit::AKSI_AKUN_DIAKTIFKAN]))
            ->assertOk()
            ->assertSee('Citra Lestari')
            ->assertDontSee('Dewi Anggraini');
    }

    public function test_jenis_perubahan_tidak_dikenal_diabaikan(): void
    {
        $admin = Pengguna::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.audit.index', ['aksi' => 'ngawur']))
            ->assertOk();
    }

    public function test_kata_sandi_tampil_sebagai_diganti(): void
    {
        $admin = Pengguna::factory()->admin()->create();
        $target = Pengguna::factory()->create();

        LogAudit::catat($admin, $target, LogAudit::AKSI_DATA_DIUBAH, ['kata_sandi' => 'diganti']);

        $this->actingAs($admin)
            ->get(route('admin.audit.index'))
            ->assertOk()
            ->assertSee('Kata sandi')
            ->assertSee('diganti');
    }

    public function test_bukan_admin_tidak_boleh_melihat_riwayat(): void
    {
        $biasa = Pengguna::factory()->masyarakat()->create();

        $this->actingAs($biasa)
            ->get(route('admin.audit.index'))
            ->assertForbidden();
    }

    public function test_tamu_diarahkan_ke_halaman_login(): void
    {
        $this->get(route('admin.audit.index'))
            ->assertRedirect(route('login'));
    }
}
