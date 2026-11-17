<?php

namespace App\Http\Controllers\Pengawas;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pengawas\TriaseLaporanRequest;
use App\Models\Laporan;
use App\Services\TriaseLaporan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LaporanController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status', Laporan::STATUS_MENUNGGU_VERIFIKASI);

        if ($status !== 'semua' && ! array_key_exists(strtoupper((string) $status), Laporan::LABEL_STATUS)) {
            $status = Laporan::STATUS_MENUNGGU_VERIFIKASI;
        }

        $kataKunci = $request->query('q');

        $daftar = Laporan::with(['pelapor', 'wilayah'])
            ->cari($kataKunci)
            ->berstatus($status === 'semua' ? null : $status)
            ->orderByDesc('dibuat_pada')
            ->paginate(15)
            ->withQueryString();

        return view('pengawas.laporan.index', [
            'daftar'      => $daftar,
            'status'      => $status,
            'kataKunci'   => $kataKunci,
            'daftarStatus' => Laporan::LABEL_STATUS,
        ]);
    }

    public function show(Laporan $laporan): View
    {
        $laporan->load([
            'pelapor',
            'wilayah',
            'kawasan',
            'penriase',
            'peristiwa' => fn ($q) => $q->with('aktor'),
        ]);

        return view('pengawas.laporan.show', [
            'laporan' => $laporan,
        ]);
    }

    public function triase(TriaseLaporanRequest $request, Laporan $laporan, TriaseLaporan $triase): RedirectResponse
    {
        $triase->jalankan($laporan, $request->user(), $request->validated());

        $pesan = match ($request->validated('tindakan')) {
            Laporan::TINDAKAN_TERIMA          => 'Laporan diterima untuk inspeksi.',
            Laporan::TINDAKAN_MINTA_PERBAIKAN => 'Permintaan perbaikan telah dicatat untuk pelapor.',
            Laporan::TINDAKAN_TOLAK           => 'Laporan ditolak dan alasan telah dicatat.',
        };

        return redirect()
            ->route('pengawas.laporan.show', $laporan)
            ->with('status', $pesan);
    }
}
