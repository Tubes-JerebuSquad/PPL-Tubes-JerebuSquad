<?php

namespace App\Policies;

use App\Models\Laporan;
use App\Models\Pengguna;

class LaporanPolicy
{
    /**
     * Pelapor hanya dapat melihat laporan dan riwayat miliknya sendiri.
     */
    public function view(Pengguna $pengguna, Laporan $laporan): bool
    {
        return $pengguna->isAktif()
            && $pengguna->isMasyarakat()
            && $laporan->milikPengguna($pengguna);
    }

    /**
     * Pelapor hanya dapat memperbaiki laporan miliknya yang belum terkunci.
     */
    public function update(Pengguna $pengguna, Laporan $laporan): bool
    {
        return $this->view($pengguna, $laporan) && $laporan->bolehDiperbaiki();
    }
}
