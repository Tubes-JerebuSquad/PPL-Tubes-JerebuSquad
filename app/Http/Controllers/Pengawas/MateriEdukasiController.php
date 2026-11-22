<?php

namespace App\Http\Controllers\Pengawas;

use App\Enums\StatusPublikasi;
use App\Http\Controllers\Controller;
use App\Http\Requests\Pengawas\PerbaruiMateriEdukasiRequest;
use App\Http\Requests\Pengawas\SimpanMateriEdukasiRequest;
use App\Models\MateriEdukasi;
use App\Models\Pengguna;
use App\Services\MateriEdukasiService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MateriEdukasiController extends Controller
{
    public function __construct(private readonly MateriEdukasiService $service) {}

    public function index(Request $request): View
    {
        $filter = $request->validate([
            'cari' => ['nullable', 'string', 'max:100'],
            'kategori' => ['nullable', 'string', 'max:100'],
            'status_publikasi' => ['nullable', Rule::enum(StatusPublikasi::class)],
        ]);

        $cari = $filter['cari'] ?? null;
        $kategori = $filter['kategori'] ?? null;
        $status = $filter['status_publikasi'] ?? null;

        $daftarMateri = MateriEdukasi::query()
            ->with(['pembuat', 'pembaru'])
            ->cari($cari)
            ->when(
                $kategori !== null && $kategori !== '',
                fn (Builder $query) => $query->where('kategori', $kategori),
            )
            ->when(
                $status !== null && $status !== '',
                fn (Builder $query) => $query->where('status_publikasi', $status),
            )
            ->latest('dibuat_pada')
            ->orderByDesc('materi_id')
            ->paginate(15)
            ->withQueryString();

        $ringkasanStatus = [
            'total' => MateriEdukasi::query()->count(),
            'draf' => MateriEdukasi::query()->draf()->count(),
            'terbit' => MateriEdukasi::query()->terbit()->count(),
            'arsip' => MateriEdukasi::query()->arsip()->count(),
        ];

        return view('pengawas.edukasi.index', [
            'daftarMateri' => $daftarMateri,
            'daftarKategori' => $this->daftarKategori(),
            'ringkasanStatus' => $ringkasanStatus,
        ]);
    }

    public function create(): View
    {
        return view('pengawas.edukasi.create', [
            'daftarKategori' => $this->daftarKategori(),
        ]);
    }

    public function store(SimpanMateriEdukasiRequest $request): RedirectResponse
    {
        $materi = $this->service->simpan($request->validated(), $this->pengawas($request));

        return redirect()
            ->route('pengawas.edukasi.show', $materi)
            ->with('status', 'Materi "'.$materi->judul.'" disimpan sebagai draf.');
    }

    public function show(MateriEdukasi $materiEdukasi): View
    {
        $materiEdukasi->load(['pembuat', 'pembaru']);

        return view('pengawas.edukasi.show', [
            'materi' => $materiEdukasi,
        ]);
    }

    public function edit(MateriEdukasi $materiEdukasi): View
    {
        return view('pengawas.edukasi.edit', [
            'materi' => $materiEdukasi,
            'daftarKategori' => $this->daftarKategori(),
        ]);
    }

    public function update(PerbaruiMateriEdukasiRequest $request, MateriEdukasi $materiEdukasi): RedirectResponse
    {
        $this->service->perbarui($materiEdukasi, $request->validated(), $this->pengawas($request));

        return redirect()
            ->route('pengawas.edukasi.show', $materiEdukasi)
            ->with('status', 'Materi edukasi berhasil diperbarui.');
    }

    public function terbitkan(Request $request, MateriEdukasi $materiEdukasi): RedirectResponse
    {
        $this->service->terbitkan($materiEdukasi, $this->pengawas($request));

        return back()->with('status', 'Materi "'.$materiEdukasi->judul.'" telah diterbitkan.');
    }

    public function arsipkan(Request $request, MateriEdukasi $materiEdukasi): RedirectResponse
    {
        $this->service->arsipkan($materiEdukasi, $this->pengawas($request));

        return back()->with('status', 'Materi "'.$materiEdukasi->judul.'" telah diarsipkan.');
    }

    /**
     * @return Collection<int, string>
     */
    private function daftarKategori(): Collection
    {
        return MateriEdukasi::query()
            ->whereNotNull('kategori')
            ->where('kategori', '<>', '')
            ->distinct()
            ->orderBy('kategori')
            ->pluck('kategori');
    }

    private function pengawas(Request $request): Pengguna
    {
        /** @var Pengguna $pengguna */
        $pengguna = $request->user();

        return $pengguna;
    }
}
