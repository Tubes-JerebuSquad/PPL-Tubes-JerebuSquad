<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Menampilkan dashboard utama untuk peran Petugas Lapangan.
     */
    public function index(Request $request): View
    {
        $pengguna = $request->user();

        if (! $pengguna || ! $pengguna->isPetugasLapangan()) {
            abort(403, 'Akses khusus Petugas Lapangan.');
        }

        $userId = $pengguna->pengguna_id;

        $tugasSaya = Schema::hasTable('tugas_inspeksi') 
            ? DB::table('tugas_inspeksi')->where('ditugaskan_kepada', $userId)->count()
            : 0;

        $tugasBerjalan = Schema::hasTable('tugas_inspeksi')
            ? DB::table('tugas_inspeksi')
                ->where('ditugaskan_kepada', $userId)
                ->whereIn('status_tugas', ['ASSIGNED', 'IN_PROGRESS', 'DITUGASKAN'])
                ->count()
            : 0;

        $inspeksiSelesai = Schema::hasTable('hasil_inspeksi')
            ? DB::table('hasil_inspeksi')->where('diinspeksi_oleh', $userId)->count()
            : 0;

        $totalKawasan = Schema::hasTable('kawasan')
            ? DB::table('kawasan')->count()
            : 0;

        $daftarTugas = Schema::hasTable('tugas_inspeksi')
            ? DB::table('tugas_inspeksi')
                ->where('ditugaskan_kepada', $userId)
                ->orderByDesc('ditugaskan_pada')
                ->take(5)
                ->get()
            : collect();

        return view('petugas.dashboard', [
            'petugas' => $pengguna,
            'tugasSaya' => $tugasSaya,
            'tugasBerjalan' => $tugasBerjalan,
            'inspeksiSelesai' => $inspeksiSelesai,
            'totalKawasan' => $totalKawasan,
            'daftarTugas' => $daftarTugas,
        ]);
    }
}
