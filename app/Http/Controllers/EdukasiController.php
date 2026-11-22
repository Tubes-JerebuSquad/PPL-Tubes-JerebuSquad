<?php

namespace App\Http\Controllers;

use App\Models\MateriEdukasi;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EdukasiController extends Controller
{
    /**
     * Menampilkan daftar materi edukasi berstatus terbit untuk publik dan Masyarakat.
     */
    public function index(Request $request): View
    {
        $filter = $request->validate([
            'cari' => ['nullable', 'string', 'max:100'],
            'kategori' => ['nullable', 'string', 'max:100'],
        ]);

        $cari = $filter['cari'] ?? null;
        $kategori = $filter['kategori'] ?? null;

        $daftarMateri = MateriEdukasi::query()
            ->terbit()
            ->with(['pembuat', 'pembaru'])
            ->cari($cari)
            ->when(
                $kategori !== null && $kategori !== '',
                fn (Builder $query) => $query->where('kategori', $kategori),
            )
            ->latest('dipublikasikan_pada')
            ->orderByDesc('materi_id')
            ->paginate(12)
            ->withQueryString();

        $daftarKategori = MateriEdukasi::query()
            ->terbit()
            ->whereNotNull('kategori')
            ->where('kategori', '<>', '')
            ->distinct()
            ->orderBy('kategori')
            ->pluck('kategori');

        return view('edukasi.index', [
            'daftarMateri' => $daftarMateri,
            'daftarKategori' => $daftarKategori,
        ]);
    }

    /**
     * Menampilkan detail satu materi edukasi yang sudah diterbitkan.
     * Materi berstatus draf atau arsip tidak dapat diakses di halaman publik.
     */
    public function show(MateriEdukasi $materiEdukasi): View
    {
        abort_unless($materiEdukasi->sudahTerbit(), 404);

        $materiEdukasi->load(['pembuat', 'pembaru']);

        return view('edukasi.show', [
            'materi' => $materiEdukasi,
        ]);
    }
}
