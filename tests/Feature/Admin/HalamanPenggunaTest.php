<?php

namespace Tests\Feature\Admin;

use App\Models\Pengguna;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HalamanPenggunaTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_bisa_melihat_daftar_pengguna(): void
    {
        $admin = Pengguna::factory()->admin()->create();
        Pengguna::factory()->create(['nama_lengkap' => 'Budi Santoso']);

        $this->actingAs($admin)
            ->get(route('admin.pengguna.index'))
            ->assertOk()
            ->assertSee('Budi Santoso');
    }

    public function test_pencarian_menyaring_daftar(): void
    {
        $admin = Pengguna::factory()->admin()->create();
        Pengguna::factory()->create(['nama_lengkap' => 'Budi Santoso']);
        Pengguna::factory()->create(['nama_lengkap' => 'Citra Lestari']);

        $this->actingAs($admin)
            ->get(route('admin.pengguna.index', ['cari' => 'Citra']))
            ->assertOk()
            ->assertSee('Citra Lestari')
            ->assertDontSee('Budi Santoso');
    }

    public function test_admin_bisa_membuka_form_tambah(): void
    {
        $admin = Pengguna::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.pengguna.create'))
            ->assertOk()
            ->assertSee('Tambah Pengguna');
    }

    public function test_admin_bisa_membuka_form_ubah(): void
    {
        $admin = Pengguna::factory()->admin()->create();
        $target = Pengguna::factory()->create(['nama_lengkap' => 'Dewi Anggraini']);

        $this->actingAs($admin)
            ->get(route('admin.pengguna.edit', $target))
            ->assertOk()
            ->assertSee('Dewi Anggraini');
    }

    public function test_non_admin_tidak_bisa_membuka_halaman(): void
    {
        $biasa = Pengguna::factory()->masyarakat()->create();

        $this->actingAs($biasa)->get(route('admin.pengguna.index'))->assertForbidden();
        $this->actingAs($biasa)->get(route('admin.pengguna.create'))->assertForbidden();
    }
}