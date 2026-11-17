<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Admin\DashboardController as BaseDashboardController;
use App\Models\Pengguna;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DashboardController extends BaseDashboardController
{
    /**
     * Menghubungkan rute umum /dashboard ke dashboard spesifik per peran.
     */
    public function portal(Request $request): RedirectResponse
    {
        /** @var Pengguna|null $pengguna */
        $pengguna = $request->user();

        if (! $pengguna) {
            return redirect()->route('login');
        }

        if ($pengguna->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        if ($pengguna->isPengawas()) {
            return redirect()->route('pengawas.dashboard');
        }

        if ($pengguna->isPetugasLapangan()) {
            return redirect()->route('petugas.dashboard');
        }

        return redirect()->route('masyarakat.dashboard');
    }
}
