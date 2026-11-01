<?php

namespace App\Http\Controllers\Katalog;

use App\Exceptions\MetadataBelumLengkapException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Katalog\PerbaruiSumberDataRequest;
use App\Http\Requests\Katalog\SimpanSumberDataRequest;
use App\Models\Pengguna;
use App\Models\SumberData;
use App\Services\SumberDataService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SumberDataController extends Controller
{
    public function __construct(private readonly SumberDataService $service) {}

    public function index(Request $request): View
    {
        $filter = $request->validate([
            'cari' => ['nullable', 'string', 'max:100'],
            'jenis_sumber' => ['nullable', Rule::in([SumberData::JENIS_INTERNAL, SumberData::JENIS_EKSTERNAL])],
            'is_aktif' => ['nullable', Rule::in(['0', '1'])],
        ]);

        $cari = $filter['cari'] ?? null;
        $jenis = $filter['jenis_sumber'] ?? null;
        $status = $filter['is_aktif'] ?? null;

        $sumberData = SumberData::query()
            ->when(
                $cari !== null && $cari !== '',
                fn (Builder $query) => $query->where('nama_sumber', 'ilike', '%'.$cari.'%'),
            )
            ->when($jenis !== null, fn (Builder $query) => $query->where('jenis_sumber', $jenis))
            ->when($status !== null, fn (Builder $query) => $query->where('is_aktif', $status === '1'))
            ->orderBy('nama_sumber')
            ->paginate(15)
            ->withQueryString();

        return view('katalog.sumber-data.index', ['sumberData' => $sumberData]);
    }

    public function create(): View
    {
        return view('katalog.sumber-data.create');
    }

    public function store(SimpanSumberDataRequest $request): RedirectResponse
    {
        $sumberData = $this->service->simpan($request->validated(), $this->pengguna($request));

        return redirect()
            ->route('katalog.sumber-data.show', $sumberData)
            ->with('status', 'Dataset "'.$sumberData->nama_sumber.'" disimpan sebagai nonaktif.');
    }

    public function show(SumberData $sumberData): View
    {
        $sumberData->load(['pembuat', 'riwayat.pengguna']);

        return view('katalog.sumber-data.show', ['sumberData' => $sumberData]);
    }

    public function edit(SumberData $sumberData): View
    {
        return view('katalog.sumber-data.edit', ['sumberData' => $sumberData]);
    }

    public function update(PerbaruiSumberDataRequest $request, SumberData $sumberData): RedirectResponse
    {
        try {
            $this->service->perbarui($sumberData, $request->validated(), $this->pengguna($request));
        } catch (MetadataBelumLengkapException $exception) {
            return back()->withInput()->withErrors(['metadata' => $exception->getMessage()]);
        }

        return redirect()
            ->route('katalog.sumber-data.show', $sumberData)
            ->with('status', 'Metadata berhasil diperbarui.');
    }

    public function aktifkan(Request $request, SumberData $sumberData): RedirectResponse
    {
        try {
            $this->service->aktifkan($sumberData, $this->pengguna($request));
        } catch (MetadataBelumLengkapException $exception) {
            return back()->withErrors(['metadata' => $exception->getMessage()]);
        }

        return back()->with('status', 'Dataset "'.$sumberData->nama_sumber.'" diaktifkan.');
    }

    public function nonaktifkan(Request $request, SumberData $sumberData): RedirectResponse
    {
        $data = $request->validate([
            'catatan' => ['nullable', 'string', 'max:500'],
        ], [
            'catatan.max' => 'Alasan penonaktifan maksimal 500 karakter.',
        ]);

        $this->service->nonaktifkan($sumberData, $this->pengguna($request), $data['catatan'] ?? null);

        return back()->with('status', 'Dataset "'.$sumberData->nama_sumber.'" dinonaktifkan.');
    }

    /**
     * Pengguna yang sedang login, bila fitur autentikasi sudah aktif.
     */
    private function pengguna(Request $request): ?Pengguna
    {
        $pengguna = $request->user();

        return $pengguna instanceof Pengguna ? $pengguna : null;
    }
}
