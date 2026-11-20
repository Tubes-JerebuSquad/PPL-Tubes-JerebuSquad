<?php

namespace Tests\Feature\Auth;

use App\Models\Pengguna;
use App\Models\Peran;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DaftarTest extends TestCase
{
    use RefreshDatabase;

    private function dataValid(array $ubah = []): array
    {
        return array_merge([
            'nama_lengkap' => 'Siti Rahma',
            'email' => 'siti@example.com',
            'telepon' => '081234567890',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ], $ubah);
    }

    public function test_halaman_daftar_dapat_ditampilkan(): void
    {
        $this->get('/register')
            ->assertOk()
            ->assertSee('Buat akun warga');
    }

    public function test_warga_dapat_mendaftar_dan_otomatis_masuk(): void
    {
        $masyarakat = Peran::factory()->masyarakat()->create();

        $this->post('/register', $this->dataValid())
            ->assertRedirect(route('masyarakat.dashboard'));

        $pengguna = Pengguna::where('email', 'siti@example.com')->firstOrFail();

        $this->assertAuthenticatedAs($pengguna);
        $this->assertSame($masyarakat->peran_id, $pengguna->peran_id);
        $this->assertSame(Pengguna::STATUS_AKTIF, $pengguna->status_akun);
        $this->assertTrue(Hash::check('rahasia123', $pengguna->password_hash));
    }

    public function test_telepon_boleh_dikosongkan(): void
    {
        Peran::factory()->masyarakat()->create();

        $this->post('/register', $this->dataValid(['telepon' => '']))
            ->assertRedirect(route('masyarakat.dashboard'));

        $this->assertDatabaseHas('pengguna', ['email' => 'siti@example.com', 'telepon' => null]);
    }

    public function test_peran_tidak_bisa_dipilih_dari_form(): void
    {
        $masyarakat = Peran::factory()->masyarakat()->create();
        $admin = Peran::factory()->admin()->create();

        $this->post('/register', $this->dataValid(['peran_id' => $admin->peran_id]));

        $pengguna = Pengguna::where('email', 'siti@example.com')->firstOrFail();

        $this->assertSame($masyarakat->peran_id, $pengguna->peran_id);
    }

    public function test_email_yang_sudah_dipakai_ditolak(): void
    {
        Peran::factory()->masyarakat()->create();
        Pengguna::factory()->create(['email' => 'siti@example.com']);

        $this->post('/register', $this->dataValid())
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_konfirmasi_kata_sandi_harus_cocok(): void
    {
        Peran::factory()->masyarakat()->create();

        $this->post('/register', $this->dataValid(['password_confirmation' => 'berbeda123']))
            ->assertSessionHasErrors('password');

        $this->assertDatabaseMissing('pengguna', ['email' => 'siti@example.com']);
    }

    public function test_kata_sandi_terlalu_pendek_ditolak(): void
    {
        Peran::factory()->masyarakat()->create();

        $this->post('/register', $this->dataValid([
            'password' => 'pendek',
            'password_confirmation' => 'pendek',
        ]))->assertSessionHasErrors('password');
    }

    public function test_pengguna_yang_sudah_masuk_tidak_perlu_mendaftar(): void
    {
        $pengguna = Pengguna::factory()->masyarakat()->create();

        $this->actingAs($pengguna)
            ->get('/register')
            ->assertRedirect();
    }
}