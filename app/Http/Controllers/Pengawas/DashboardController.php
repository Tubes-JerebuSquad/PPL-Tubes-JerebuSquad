<?php

namespace App\Http\Controllers\Pengawas;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Menampilkan dashboard utama untuk peran Pengawas.
     */
    public function index(Request $request): View
    {
        $pengguna = $request->user();

        if (! $pengguna || ! $pengguna->isPengawas()) {
            abort(403, 'Akses khusus Pengawas.');
        }

        $totalLaporan = Schema::hasTable('laporan') ? DB::table('laporan')->count() : 0;
        
        $laporanPerluVerifikasi = Schema::hasTable('laporan') 
            ? DB::table('laporan')
                ->whereIn('status', ['menunggu_verifikasi', 'MENUNGGU_VERIFIKASI'])
                ->count() 
            : 0;

        $tugasInspeksi = Schema::hasTable('tugas_inspeksi') ? DB::table('tugas_inspeksi')->count() : 0;
        $titikPanas = Schema::hasTable('titik_panas') ? DB::table('titik_panas')->count() : 0;

        $laporanTerbaru = Schema::hasTable('laporan')
            ? DB::table('laporan')
                ->orderByDesc('created_at')
                ->take(5)
                ->get()
            : collect();

        return view('pengawas.dashboard', [
            'pengawas' => $pengguna,
            'totalLaporan' => $totalLaporan,
            'laporanPerluVerifikasi' => $laporanPerluVerifikasi,
            'tugasInspeksi' => $tugasInspeksi,
            'titikPanas' => $titikPanas,
            'laporanTerbaru' => $laporanTerbaru,
        ]);
    }
}
