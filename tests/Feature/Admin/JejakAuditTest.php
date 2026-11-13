<?php

namespace Tests\Feature\Admin;

use App\Models\LogAudit;
use App\Models\Pengguna;
use App\Models\Peran;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JejakAuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_menambah_pengguna_tercatat(): void
    {
        $admin = Pengguna::factory()->admin()->create();
        $peran = Peran::factory()->masyarakat()->create();

        $this->actingAs($admin)->post(route('admin.pengguna.store'), [
            'nama_lengkap' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'telepon' => '081200000009',
            'peran_id' => $peran->peran_id,
            'password' => 'rahasia123',
        ]);

        $baru = Pengguna::where('email', 'budi@example.com')->firstOrFail();

        $this->assertDatabaseHas('log_audit', [
            'pelaku_id' => $admin->pengguna_id,
            'sasaran_id' => $baru->pengguna_id,
            'aksi' => LogAudit::AKSI_AKUN_DIBUAT,
        ]);
    }

    public function test_mengubah_peran_tercatat_terpisah_dari_data_lain(): void
    {
        $admin = Pengguna::factory()->admin()->create();
        $target = Pengguna::factory()->masyarakat()->create();
        $peranBaru = Peran::factory()->pengawas()->create();

        $this->actingAs($admin)->put(route('admin.pengguna.update', $target), [
            'nama_lengkap' => 'Nama Baru',
            'email' => $target->email,
            'telepon' => $target->telepon,
            'peran_id' => $peranBaru->peran_id,
        ]);

        $this->assertDatabaseHas('log_audit', [
            'sasaran_id' => $target->pengguna_id,
            'aksi' => LogAudit::AKSI_PERAN_DIUBAH,
        ]);
        $this->assertDatabaseHas('log_audit', [
            'sasaran_id' => $target->pengguna_id,
            'aksi' => LogAudit::AKSI_DATA_DIUBAH,
        ]);
    }

    public function test_kata_sandi_tidak_pernah_disimpan_di_log(): void
    {
        $admin = Pengguna::factory()->admin()->create();
        $target = Pengguna::factory()->masyarakat()->create();

        $this->actingAs($admin)->put(route('admin.pengguna.update', $target), [
            'nama_lengkap' => $target->nama_lengkap,
            'email' => $target->email,
            'telepon' => $target->telepon,
            'peran_id' => $target->peran_id,
            'password' => 'passwordbaru123',
        ]);

        $log = LogAudit::where('sasaran_id', $target->pengguna_id)->firstOrFail();

        $this->assertSame('diganti', $log->perubahan['kata_sandi']);
        $this->assertArrayNotHasKey('password_hash', $log->perubahan);
        $this->assertStringNotContainsString('passwordbaru123', json_encode($log->perubahan));
    }

    public function test_tanpa_perubahan_tidak_membuat_log(): void
    {
        $admin = Pengguna::factory()->admin()->create();
        $target = Pengguna::factory()->masyarakat()->create();

        $this->actingAs($admin)->put(route('admin.pengguna.update', $target), [
            'nama_lengkap' => $target->nama_lengkap,
            'email' => $target->email,
            'telepon' => $target->telepon,
            'peran_id' => $target->peran_id,
        ]);

        $this->assertDatabaseCount('log_audit', 0);
    }

    public function test_nonaktifkan_dan_aktifkan_tercatat(): void
    {
        $admin = Pengguna::factory()->admin()->create();
        $target = Pengguna::factory()->masyarakat()->create();

        $this->actingAs($admin)->patch(route('admin.pengguna.nonaktifkan', $target));
        $this->actingAs($admin)->patch(route('admin.pengguna.aktifkan', $target));

        $this->assertDatabaseHas('log_audit', [
            'pelaku_id' => $admin->pengguna_id,
            'sasaran_id' => $target->pengguna_id,
            'aksi' => LogAudit::AKSI_AKUN_DINONAKTIFKAN,
        ]);
        $this->assertDatabaseHas('log_audit', [
            'pelaku_id' => $admin->pengguna_id,
            'sasaran_id' => $target->pengguna_id,
            'aksi' => LogAudit::AKSI_AKUN_DIAKTIFKAN,
        ]);
    }

    public function test_menonaktifkan_akun_sendiri_ditolak_dan_tidak_dicatat(): void
    {
        $admin = Pengguna::factory()->admin()->create();

        $this->actingAs($admin)->patch(route('admin.pengguna.nonaktifkan', $admin));

        $this->assertDatabaseCount('log_audit', 0);
    }
}