<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SimpanKawasanRequest;
use App\Http\Requests\Admin\UbahKawasanRequest;
use App\Models\Kawasan;
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
        $kawasan->load(['wilayah', 'pembuat']);

        return view('admin.kawasan.show', [
            'kawasan' => $kawasan,
        ]);
    }

    public function edit(Kawasan $kawasan): View
    {
        return view('admin.kawasan.edit', [
            'kawasan' => $kawasan,
            'daftarWilayah' => $this->daftarWilayah($kawasan),
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

    private function daftarWilayah(?Kawasan $kawasan = null): Collection
    {
        return Wilayah::query()
            ->where('is_aktif', true)
            ->when($kawasan, fn ($query) => $query->orWhere('wilayah_id', $kawasan->wilayah_id))
            ->orderBy('nama_wilayah')
            ->get();
    }
}
