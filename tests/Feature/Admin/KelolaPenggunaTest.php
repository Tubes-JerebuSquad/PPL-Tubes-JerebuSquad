<?php

namespace Tests\Feature\Admin;

use App\Models\Pengguna;
use App\Models\Peran;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class KelolaPenggunaTest extends TestCase
{
    use RefreshDatabase;

    private function dataUbah(Pengguna $pengguna, array $ganti = []): array
    {
        return array_merge([
            'nama_lengkap' => 'Nama Baru',
            'email' => $pengguna->email,
            'telepon' => '081200000000',
            'peran_id' => $pengguna->peran_id,
        ], $ganti);
    }

    public function test_admin_dapat_mengubah_data_dan_peran(): void
    {
        $admin = Pengguna::factory()->admin()->create();
        $target = Pengguna::factory()->create();
        $peranBaru = Peran::factory()->create();

        $this->actingAs($admin)
            ->put("/admin/pengguna/{$target->pengguna_id}", $this->dataUbah($target, [
                'peran_id' => $peranBaru->peran_id,
            ]))
            ->assertRedirect(route('admin.pengguna.index'));

        $target->refresh();
        $this->assertSame('Nama Baru', $target->nama_lengkap);
        $this->assertSame($peranBaru->peran_id, $target->peran_id);
    }

    public function test_password_kosong_tidak_mengubah_password_lama(): void
    {
        $admin = Pengguna::factory()->admin()->create();
        $target = Pengguna::factory()->create(['password_hash' => 'lama-12345']);

        $this->actingAs($admin)
            ->put("/admin/pengguna/{$target->pengguna_id}", $this->dataUbah($target, ['password' => '']))
            ->assertRedirect(route('admin.pengguna.index'));

        $this->assertTrue(Hash::check('lama-12345', $target->refresh()->password_hash));
    }

    public function test_password_baru_menggantikan_yang_lama(): void
    {
        $admin = Pengguna::factory()->admin()->create();
        $target = Pengguna::factory()->create(['password_hash' => 'lama-12345']);

        $this->actingAs($admin)
            ->put("/admin/pengguna/{$target->pengguna_id}", $this->dataUbah($target, ['password' => 'baru-67890']))
            ->assertRedirect(route('admin.pengguna.index'));

        $this->assertTrue(Hash::check('baru-67890', $target->refresh()->password_hash));
    }

    public function test_email_pengguna_lain_ditolak(): void
    {
        $admin = Pengguna::factory()->admin()->create();
        $target = Pengguna::factory()->create();
        $lain = Pengguna::factory()->create();

        $this->actingAs($admin)
            ->put("/admin/pengguna/{$target->pengguna_id}", $this->dataUbah($target, ['email' => $lain->email]))
            ->assertSessionHasErrors('email');
    }

    public function test_bukan_admin_tidak_boleh_mengubah(): void
    {
        $masyarakat = Pengguna::factory()->masyarakat()->create();
        $target = Pengguna::factory()->create();

        $this->actingAs($masyarakat)
            ->put("/admin/pengguna/{$target->pengguna_id}", $this->dataUbah($target))
            ->assertForbidden();
    }

    public function test_admin_dapat_menonaktifkan_lalu_mengaktifkan_akun(): void
    {
        $admin = Pengguna::factory()->admin()->create();
        $target = Pengguna::factory()->create();

        $this->actingAs($admin)
            ->patch("/admin/pengguna/{$target->pengguna_id}/nonaktifkan")
            ->assertRedirect();

        $target->refresh();
        $this->assertFalse($target->isAktif());
        $this->assertNotNull($target->dinonaktifkan_pada);

        $this->actingAs($admin)
            ->patch("/admin/pengguna/{$target->pengguna_id}/aktifkan")
            ->assertRedirect();

        $target->refresh();
        $this->assertTrue($target->isAktif());
        $this->assertNull($target->dinonaktifkan_pada);
    }

    public function test_admin_tidak_boleh_menonaktifkan_akun_sendiri(): void
    {
        $admin = Pengguna::factory()->admin()->create();

        $this->actingAs($admin)
            ->patch("/admin/pengguna/{$admin->pengguna_id}/nonaktifkan")
            ->assertSessionHasErrors('akun');

        $this->assertTrue($admin->refresh()->isAktif());
    }
}