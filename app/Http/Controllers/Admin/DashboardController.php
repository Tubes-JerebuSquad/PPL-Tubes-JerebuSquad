<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pengguna;
use App\Models\Peran;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Menampilkan halaman dashboard utama admin beserta ringkasan statistik.
     */
    public function index(Request $request): View
    {
        $pengguna = $request->user();

        // Pastikan hanya akun dengan peran Admin yang dapat mengakses
        if (! $pengguna || ! $pengguna->isAdmin()) {
            abort(403, 'Akses khusus Administrator.');
        }

        // Statistik pengguna dan peran
        $totalPengguna = Pengguna::count();
        $penggunaAktif = Pengguna::where('status_akun', Pengguna::STATUS_AKTIF)->count();
        $totalPeran = Peran::count();
        $penggunaTerbaru = Pengguna::with('peran')
            ->orderByDesc('dibuat_pada')
            ->take(5)
            ->get();

        return view('admin.dashboard', [
            'admin' => $pengguna,
            'totalPengguna' => $totalPengguna,
            'penggunaAktif' => $penggunaAktif,
            'totalPeran' => $totalPeran,
            'penggunaTerbaru' => $penggunaTerbaru,
        ]);
    }
}
