<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FilterKawasanRequest;
use App\Http\Requests\Admin\SimpanKawasanRequest;
use App\Http\Requests\Admin\UbahKawasanRequest;
use App\Models\Kawasan;
use App\Models\SumberData;
use App\Models\Wilayah;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection as DaftarNilai;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class KawasanController extends Controller
{
    public function index(FilterKawasanRequest $request): View
    {
        $spasialTersedia = DB::connection()->getDriverName() === 'pgsql';
        $bbox = $spasialTersedia ? $request->bbox() : null;

        $query = Kawasan::with('wilayah')
            ->cari($request->query('cari'))
            ->berwilayah($request->integer('wilayah_id') ?: null)
            ->berjenis($request->query('jenis_kawasan'))
            ->berstatusKawasan($request->query('status_kawasan'))
            ->berstatusData($request->query('status_data'));

        if ($bbox !== null) {
            $query->dalamBbox(
                $bbox['min_lon'],
                $bbox['min_lat'],
                $bbox['max_lon'],
                $bbox['max_lat'],
            );
        }

        $daftar = $query
            ->orderBy('nama_kawasan')
            ->paginate(15)
            ->withQueryString();

        $filterAktif = filled($request->query('cari'))
            || filled($request->query('wilayah_id'))
            || filled($request->query('jenis_kawasan'))
            || filled($request->query('status_kawasan'))
            || filled($request->query('status_data'))
            || $bbox !== null;

        return view('admin.kawasan.index', [
            'daftar' => $daftar,
            'daftarWilayah' => Wilayah::orderBy('nama_wilayah')->get(),
            'daftarJenis' => $this->opsiKolomKawasan('jenis_kawasan'),
            'daftarStatus' => $this->opsiKolomKawasan('status_kawasan'),
            'spasialTersedia' => $spasialTersedia,
            'bboxAktif' => $bbox !== null,
            'pesanKosong' => $this->pesanKosong($daftar, $filterAktif, $bbox !== null),
        ]);
    }

    public function create(): View
    {
        return view('admin.kawasan.create', [
            'daftarWilayah' => $this->daftarWilayah(),
            'daftarSumber' => $this->daftarSumber(),
        ]);
    }

    public function store(SimpanKawasanRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['is_aktif'] = $request->boolean('is_aktif', true);
        $data['dibuat_oleh'] = $request->user()->pengguna_id;

        Kawasan::create($data);

        return redirect()
            ->route('admin.kawasan.index')
            ->with('status', 'Kawasan berhasil ditambahkan.');
    }

    public function show(Kawasan $kawasan): View
    {
        $kawasan->load(['wilayah', 'pembuat', 'sumber']);

        return view('admin.kawasan.show', [
            'kawasan' => $kawasan,
        ]);
    }

    public function edit(Kawasan $kawasan): View
    {
        return view('admin.kawasan.edit', [
            'kawasan' => $kawasan,
            'daftarWilayah' => $this->daftarWilayah($kawasan),
            'daftarSumber' => $this->daftarSumber($kawasan),
        ]);
    }

    public function update(UbahKawasanRequest $request, Kawasan $kawasan): RedirectResponse
    {
        $data = $request->validated();
        $data['is_aktif'] = $request->boolean('is_aktif', $kawasan->is_aktif);

        $kawasan->update($data);

        return redirect()
            ->route('admin.kawasan.index')
            ->with('status', 'Data kawasan berhasil diperbarui.');
    }

    private function daftarSumber(?Kawasan $kawasan = null): Collection
    {
        // status_kualitas sengaja tidak diambil agar nilai lama di luar enum
        // tidak memicu ValueError saat opsi dropdown dirender.
        return SumberData::query()
            ->tersediaUntukPilihan($kawasan?->sumber_id)
            ->get(['sumber_id', 'nama_sumber', 'kode_crs', 'is_aktif']);
    }

    private function daftarWilayah(?Kawasan $kawasan = null): Collection
    {
        return Wilayah::query()
            ->where('is_aktif', true)
            ->when($kawasan, fn ($query) => $query->orWhere('wilayah_id', $kawasan->wilayah_id))
            ->orderBy('nama_wilayah')
            ->get();
    }

    /**
     * Mengambil nilai unik yang benar-benar dipakai pada kolom kawasan,
     * sehingga pilihan filter selalu mengikuti data aktual.
     */
    private function opsiKolomKawasan(string $kolom): DaftarNilai
    {
        return Kawasan::query()
            ->whereNotNull($kolom)
            ->where($kolom, '<>', '')
            ->distinct()
            ->orderBy($kolom)
            ->pluck($kolom);
    }

    /**
     * Menentukan pesan saat daftar kosong agar penyebabnya jelas bagi pengguna.
     */
    private function pesanKosong(
        LengthAwarePaginator $daftar,
        bool $filterAktif,
        bool $bboxAktif,
    ): string {
        if (! $daftar->isEmpty()) {
            return '';
        }

        if (! $filterAktif) {
            return 'Belum ada data kawasan.';
        }

        if ($bboxAktif && ! DB::table('batas_kawasan')->exists()) {
            return 'Filter area belum bisa digunakan karena belum ada data batas kawasan.';
        }

        return 'Tidak ada kawasan yang cocok dengan pencarian atau filter.';
    }
}
