<?php

namespace App\Http\Controllers\Masyarakat;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Menampilkan dashboard utama untuk peran Masyarakat / Warga.
     */
    public function index(Request $request): View
    {
        $pengguna = $request->user();

        if (! $pengguna || ! $pengguna->isMasyarakat()) {
            abort(403, 'Akses khusus Masyarakat.');
        }

        $userId = $pengguna->pengguna_id;

        $laporanSaya = Schema::hasTable('laporan')
            ? DB::table('laporan')->where('pelapor_id', $userId)->count()
            : 0;

        $laporanDiproses = Schema::hasTable('laporan')
            ? DB::table('laporan')
                ->where('pelapor_id', $userId)
                ->whereIn('status', ['menunggu_verifikasi', 'MENUNGGU_VERIFIKASI', 'diverifikasi', 'DIVERIFIKASI', 'dalam_inspeksi', 'DALAM_INSPEKSI'])
                ->count()
            : 0;

        $laporanSelesai = Schema::hasTable('laporan')
            ? DB::table('laporan')
                ->where('pelapor_id', $userId)
                ->whereIn('status', ['selesai', 'SELESAI'])
                ->count()
            : 0;

        $kegiatanKonservasi = Schema::hasTable('kegiatan_konservasi')
            ? DB::table('kegiatan_konservasi')->count()
            : 0;

        $riwayatLaporan = Schema::hasTable('laporan')
            ? DB::table('laporan')
                ->where('pelapor_id', $userId)
                ->orderByDesc('created_at')
                ->take(5)
                ->get()
            : collect();

        return view('masyarakat.dashboard', [
            'warga' => $pengguna,
            'laporanSaya' => $laporanSaya,
            'laporanDiproses' => $laporanDiproses,
            'laporanSelesai' => $laporanSelesai,
            'kegiatanKonservasi' => $kegiatanKonservasi,
            'riwayatLaporan' => $riwayatLaporan,
        ]);
    }
}
