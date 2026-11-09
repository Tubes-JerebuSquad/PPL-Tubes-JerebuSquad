<?php

namespace Tests\Feature\Admin;

use App\Models\Pengguna;
use App\Models\Peran;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TambahPenggunaTest extends TestCase
{
    use RefreshDatabase;

    private function dataValid(array $ganti = []): array
    {
        $peran = Peran::factory()->create();

        return array_merge([
            'nama_lengkap' => 'Budi Santoso',
            'email' => 'budi@contoh.test',
            'telepon' => '081234567890',
            'peran_id' => $peran->peran_id,
            'password' => 'rahasia-kuat-1',
        ], $ganti);
    }

    public function test_admin_dapat_menambah_pengguna(): void
    {
        $admin = Pengguna::factory()->admin()->create();

        $this->actingAs($admin)
            ->post('/admin/pengguna', $this->dataValid())
            ->assertRedirect(route('admin.pengguna.index'));

        $baru = Pengguna::where('email', 'budi@contoh.test')->first();
        $this->assertNotNull($baru);
        $this->assertTrue($baru->isAktif());
        $this->assertTrue(Hash::check('rahasia-kuat-1', $baru->password_hash));
    }

    public function test_bukan_admin_tidak_boleh_menambah(): void
    {
        $masyarakat = Pengguna::factory()->masyarakat()->create();

        $this->actingAs($masyarakat)
            ->post('/admin/pengguna', $this->dataValid())
            ->assertForbidden();

        $this->assertDatabaseMissing('pengguna', ['email' => 'budi@contoh.test']);
    }

    public function test_email_yang_sudah_dipakai_ditolak(): void
    {
        $admin = Pengguna::factory()->admin()->create();
        Pengguna::factory()->create(['email' => 'budi@contoh.test']);

        $this->actingAs($admin)
            ->post('/admin/pengguna', $this->dataValid())
            ->assertSessionHasErrors('email');
    }

    public function test_peran_yang_tidak_ada_ditolak(): void
    {
        $admin = Pengguna::factory()->admin()->create();

        $this->actingAs($admin)
            ->post('/admin/pengguna', $this->dataValid(['peran_id' => 9999]))
            ->assertSessionHasErrors('peran_id');
    }

    public function test_password_terlalu_pendek_ditolak(): void
    {
        $admin = Pengguna::factory()->admin()->create();

        $this->actingAs($admin)
            ->post('/admin/pengguna', $this->dataValid(['password' => 'pendek']))
            ->assertSessionHasErrors('password');
    }
}