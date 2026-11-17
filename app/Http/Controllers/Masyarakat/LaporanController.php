<?php

namespace App\Http\Controllers\Masyarakat;

use App\Enums\KategoriLaporan;
use App\Enums\StatusLaporan;
use App\Exceptions\LaporanTerkunciException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Masyarakat\PerbaikiLaporanRequest;
use App\Models\BerkasMedia;
use App\Models\Kawasan;
use App\Models\Laporan;
use App\Models\Pengguna;
use App\Models\Wilayah;
use App\Services\LaporanPerbaikanService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class LaporanController extends Controller
{
    public function __construct(private readonly LaporanPerbaikanService $service) {}

    public function index(Request $request): View
    {
        $pengguna = $this->pelapor($request);

        $filter = $request->validate([
            'cari' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(array_column(StatusLaporan::cases(), 'value'))],
        ]);

        $cari = $filter['cari'] ?? null;
        $status = $filter['status'] ?? null;

        $daftarLaporan = Laporan::query()
            ->with(['wilayah', 'kawasan', 'revisiTerbaru'])
            ->where('pelapor_id', $pengguna->pengguna_id)
            ->when(
                $cari !== null && $cari !== '',
                fn (Builder $query) => $query->where(function (Builder $sub) use ($cari): void {
                    $sub->whereLike('nomor_laporan', '%'.$cari.'%')
                        ->orWhereLike('kategori', '%'.$cari.'%')
                        ->orWhereLike('deskripsi', '%'.$cari.'%');
                }),
            )
            ->when(
                $status !== null && $status !== '',
                fn (Builder $query) => $query->whereIn('status', [$status, strtoupper($status)]),
            )
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('masyarakat.laporan.index', [
            'daftarLaporan' => $daftarLaporan,
            'pilihanStatus' => StatusLaporan::pilihan(),
        ]);
    }

    public function show(Laporan $laporan): View
    {
        Gate::authorize('view', $laporan);

        $laporan->load(['wilayah', 'kawasan', 'revisi.foto', 'peristiwa.pengguna']);

        return view('masyarakat.laporan.show', [
            'laporan' => $laporan,
        ]);
    }

    public function edit(Laporan $laporan): View
    {
        Gate::authorize('update', $laporan);

        $laporan->load(['wilayah', 'kawasan', 'revisiTerbaru.foto']);

        return view('masyarakat.laporan.edit', [
            'laporan' => $laporan,
            'daftarKategori' => KategoriLaporan::pilihan(),
            'daftarWilayah' => Wilayah::query()->aktif()->orderBy('nama_wilayah')->get(),
            'daftarKawasan' => Kawasan::query()->with('wilayah')->aktif()->orderBy('nama_kawasan')->get(),
        ]);
    }

    public function update(PerbaikiLaporanRequest $request, Laporan $laporan): RedirectResponse
    {
        Gate::authorize('update', $laporan);

        try {
            $this->service->kirimPerbaikan(
                $laporan,
                $this->pelapor($request),
                $request->validated(),
                $request->file('foto', []),
                $request->input('pertahankan_foto', []),
            );
        } catch (LaporanTerkunciException $exception) {
            return redirect()
                ->route('masyarakat.laporan.show', $laporan)
                ->withErrors(['laporan' => $exception->getMessage()]);
        }

        return redirect()
            ->route('masyarakat.laporan.show', $laporan)
            ->with('status', 'Perbaikan laporan #'.$laporan->nomor_laporan.' berhasil dikirim ulang untuk ditinjau kembali.');
    }

    public function media(Laporan $laporan, BerkasMedia $berkasMedia): BinaryFileResponse
    {
        Gate::authorize('view', $laporan);

        abort_unless($laporan->memuatMedia($berkasMedia), 404);

        $disk = Storage::disk('local');
        abort_unless($disk->exists($berkasMedia->path_file), 404);

        return response()->file($disk->path($berkasMedia->path_file), [
            'Content-Type' => $berkasMedia->mime_type,
        ]);
    }

    private function pelapor(Request $request): Pengguna
    {
        $pengguna = $request->user();
        abort_unless($pengguna instanceof Pengguna, 403);

        return $pengguna;
    }
}
