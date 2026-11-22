<?php

namespace Tests\Feature\Admin;

use App\Models\Pengguna;
use App\Models\Peran;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTerakhirTest extends TestCase
{
    use RefreshDatabase;

    private function dataUbah(Pengguna $pengguna, int $peranId): array
    {
        return [
            'nama_lengkap' => $pengguna->nama_lengkap,
            'email' => $pengguna->email,
            'telepon' => $pengguna->telepon,
            'peran_id' => $peranId,
        ];
    }

    public function test_admin_aktif_satu_satunya_tidak_bisa_mengubah_perannya_sendiri(): void
    {
        $admin = Pengguna::factory()->admin()->create();
        $peranWarga = Peran::factory()->masyarakat()->create();

        $this->actingAs($admin)
            ->put(route('admin.pengguna.update', $admin), $this->dataUbah($admin, $peranWarga->peran_id))
            ->assertSessionHasErrors('peran_id');

        $this->assertTrue($admin->fresh()->isAdmin());
    }

    public function test_admin_boleh_mengubah_peran_bila_masih_ada_admin_aktif_lain(): void
    {
        $admin = Pengguna::factory()->admin()->create();
        Pengguna::factory()->create(['peran_id' => $admin->peran_id]);
        $peranWarga = Peran::factory()->masyarakat()->create();

        $this->actingAs($admin)
            ->put(route('admin.pengguna.update', $admin), $this->dataUbah($admin, $peranWarga->peran_id))
            ->assertSessionHasNoErrors();

        $this->assertFalse($admin->fresh()->isAdmin());
    }

    public function test_admin_nonaktif_tidak_dihitung_sebagai_admin_lain(): void
    {
        $admin = Pengguna::factory()->admin()->create();
        Pengguna::factory()->nonaktif()->create(['peran_id' => $admin->peran_id]);
        $peranWarga = Peran::factory()->masyarakat()->create();

        $this->actingAs($admin)
            ->put(route('admin.pengguna.update', $admin), $this->dataUbah($admin, $peranWarga->peran_id))
            ->assertSessionHasErrors('peran_id');

        $this->assertTrue($admin->fresh()->isAdmin());
    }

    public function test_admin_tunggal_tetap_bisa_mengubah_data_tanpa_mengganti_peran(): void
    {
        $admin = Pengguna::factory()->admin()->create();

        $data = $this->dataUbah($admin, $admin->peran_id);
        $data['nama_lengkap'] = 'Nama Baru';

        $this->actingAs($admin)
            ->put(route('admin.pengguna.update', $admin), $data)
            ->assertSessionHasNoErrors();

        $this->assertSame('Nama Baru', $admin->fresh()->nama_lengkap);
    }
}