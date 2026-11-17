<?php

namespace App\Http\Controllers;

use App\Models\Kawasan;
use App\Models\Pengguna;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PetaInteraktifController extends Controller
{
    /**
     * Menampilkan peta interaktif multi-layer (PBI-04 / FR-04)
     * Menyesuaikan hak akses dan visibilitas layer sesuai peran akun yang login.
     */
    public function index(Request $request): View
    {
        /** @var Pengguna|null $user */
        $user = $request->user();

        // Tentukan peran pengguna aktif
        $roleSlug = 'masyarakat';
        $roleLabel = 'Masyarakat';

        if ($user) {
            if ($user->isAdmin()) {
                $roleSlug = 'admin';
                $roleLabel = 'Administrator';
            } elseif ($user->isPengawas()) {
                $roleSlug = 'pengawas';
                $roleLabel = 'Pengawas';
            } elseif ($user->isPetugasLapangan()) {
                $roleSlug = 'petugas';
                $roleLabel = 'Petugas Lapangan';
            } else {
                $roleSlug = 'masyarakat';
                $roleLabel = 'Masyarakat';
            }
        }

        $namaLengkap = $user?->nama_lengkap ?? 'Pengguna Publik';
        $inisial = strtoupper(substr($namaLengkap, 0, 2));

        // Statistik ringkas database jika tersedia
        $totalKawasanAktif = Kawasan::where('is_aktif', true)->count();

        return view('PetaInteraktif.peta', [
            'user' => $user,
            'roleSlug' => $roleSlug,
            'roleLabel' => $roleLabel,
            'namaLengkap' => $namaLengkap,
            'inisial' => $inisial,
            'totalKawasanAktif' => $totalKawasanAktif,
        ]);
    }
}
