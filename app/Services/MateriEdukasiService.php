<?php

namespace App\Services;

use App\Enums\StatusPublikasi;
use App\Models\MateriEdukasi;
use App\Models\Pengguna;
use Illuminate\Support\Facades\DB;

class MateriEdukasiService
{
    /**
     * Simpan materi edukasi baru dengan status awal DRAF.
     *
     * @param  array<string, mixed>  $data
     */
    public function simpan(array $data, Pengguna $pengawas): MateriEdukasi
    {
        return DB::transaction(function () use ($data, $pengawas): MateriEdukasi {
            $materi = new MateriEdukasi($data);
            $materi->forceFill([
                'dibuat_oleh' => $pengawas->pengguna_id,
                'diperbarui_oleh' => $pengawas->pengguna_id,
                'status_publikasi' => StatusPublikasi::DRAFT,
                'dipublikasikan_pada' => null,
                'diarsipkan_pada' => null,
            ])->save();

            return $materi->refresh();
        });
    }

    /**
     * Perbarui isi materi edukasi tanpa mengubah status publikasinya.
     *
     * @param  array<string, mixed>  $data
     */
    public function perbarui(MateriEdukasi $materi, array $data, Pengguna $pengawas): MateriEdukasi
    {
        return DB::transaction(function () use ($materi, $data, $pengawas): MateriEdukasi {
            $materi->fill($data);
            $materi->forceFill([
                'diperbarui_oleh' => $pengawas->pengguna_id,
            ])->save();

            return $materi->refresh();
        });
    }

    /**
     * Terbitkan materi edukasi agar tampil pada halaman publik.
     */
    public function terbitkan(MateriEdukasi $materi, Pengguna $pengawas): MateriEdukasi
    {
        return DB::transaction(function () use ($materi, $pengawas): MateriEdukasi {
            if ($materi->sudahTerbit()) {
                return $materi;
            }

            $materi->forceFill([
                'status_publikasi' => StatusPublikasi::PUBLISHED,
                'dipublikasikan_pada' => now(),
                'diarsipkan_pada' => null,
                'diperbarui_oleh' => $pengawas->pengguna_id,
            ])->save();

            return $materi->refresh();
        });
    }

    /**
     * Arsipkan materi edukasi sehingga tidak lagi tampil pada daftar publik.
     */
    public function arsipkan(MateriEdukasi $materi, Pengguna $pengawas): MateriEdukasi
    {
        return DB::transaction(function () use ($materi, $pengawas): MateriEdukasi {
            if ($materi->sudahDiarsipkan()) {
                return $materi;
            }

            $materi->forceFill([
                'status_publikasi' => StatusPublikasi::ARCHIVED,
                'diarsipkan_pada' => now(),
                'diperbarui_oleh' => $pengawas->pengguna_id,
            ])->save();

            return $materi->refresh();
        });
    }
}
