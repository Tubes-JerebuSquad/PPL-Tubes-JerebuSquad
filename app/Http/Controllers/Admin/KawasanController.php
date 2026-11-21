<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SimpanKawasanRequest;
use App\Http\Requests\Admin\UbahKawasanRequest;
use App\Models\BatasKawasan;
use App\Models\Kawasan;
use App\Models\SumberData;
use App\Models\Wilayah;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class KawasanController extends Controller
{
    public function index(): View
    {
        $daftar = Kawasan::with('wilayah')
            ->orderBy('nama_kawasan')
            ->paginate(15);

        return view('admin.kawasan.index', [
            'daftar' => $daftar,
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

        $batasTerbaru = BatasKawasan::query()
            ->where('kawasan_id', $kawasan->kawasan_id)
            ->orderByDesc('nomor_versi')
            ->select('nomor_versi')
            ->selectRaw('ST_XMin(data_geometri) AS min_lon, ST_YMin(data_geometri) AS min_lat')
            ->selectRaw('ST_XMax(data_geometri) AS max_lon, ST_YMax(data_geometri) AS max_lat')
            ->first();

        return view('admin.kawasan.show', [
            'kawasan' => $kawasan,
            'batasTerbaru' => $batasTerbaru,
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
}
