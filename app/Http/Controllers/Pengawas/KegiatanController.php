<?php

namespace App\Http\Controllers\Pengawas;

use App\Enums\StatusKegiatan;
use App\Http\Controllers\Controller;
use App\Http\Requests\Pengawas\KegiatanRequest;
use App\Models\Kawasan;
use App\Models\KegiatanKonservasi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KegiatanController extends Controller
{
    private const JENIS_SARAN = [
        'Penanaman',
        'Rehabilitasi lahan',
        'Pembersihan kawasan',
        'Pemantauan',
        'Edukasi',
    ];

    public function index(Request $request): View
    {
        $status = StatusKegiatan::tryFrom((string) $request->query('status'));

        $daftar = KegiatanKonservasi::with('kawasan')
            ->when($status, fn ($query) => $query->where('status_kegiatan', $status->value))
            ->orderByDesc('tanggal_mulai')
            ->paginate(15)
            ->withQueryString();

        return view('pengawas.kegiatan.index', [
            'daftar' => $daftar,
            'status' => $status,
        ]);
    }

    public function create(): View
    {
        return view('pengawas.kegiatan.create', $this->dataFormulir());
    }

    public function store(KegiatanRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $status = $this->statusDariAksi($data['aksi']);
        unset($data['aksi']);

        $kegiatan = KegiatanKonservasi::create($data + [
            'direncanakan_oleh' => $request->user()->getKey(),
            'status_kegiatan' => $status->value,
        ]);

        return redirect()
            ->route('pengawas.kegiatan.show', $kegiatan)
            ->with('status', $status === StatusKegiatan::DITERBITKAN
                ? 'Kegiatan berhasil diterbitkan.'
                : 'Rencana kegiatan disimpan sebagai draf.');
    }

    public function show(KegiatanKonservasi $kegiatan): View
    {
        $kegiatan->load(['kawasan', 'perencana']);

        return view('pengawas.kegiatan.show', ['kegiatan' => $kegiatan]);
    }

    public function edit(KegiatanKonservasi $kegiatan): View
    {
        abort_unless($kegiatan->dapatDiubah(), 403, 'Kegiatan ini tidak dapat diubah lagi.');

        return view('pengawas.kegiatan.edit', $this->dataFormulir() + ['kegiatan' => $kegiatan]);
    }

    public function update(KegiatanRequest $request, KegiatanKonservasi $kegiatan): RedirectResponse
    {
        abort_unless($kegiatan->dapatDiubah(), 403, 'Kegiatan ini tidak dapat diubah lagi.');

        $data = $request->validated();
        $status = $this->statusDariAksi($data['aksi']);
        unset($data['aksi']);

        if ($kegiatan->status_kegiatan === StatusKegiatan::DITERBITKAN && $status === StatusKegiatan::DRAF) {
            return back()
                ->withInput()
                ->withErrors(['aksi' => 'Kegiatan yang sudah diterbitkan tidak dapat dikembalikan menjadi draf.']);
        }

        $kegiatan->update($data + ['status_kegiatan' => $status->value]);

        return redirect()
            ->route('pengawas.kegiatan.show', $kegiatan)
            ->with('status', 'Rencana kegiatan berhasil diperbarui.');
    }

    public function batalkan(KegiatanKonservasi $kegiatan): RedirectResponse
    {
        abort_unless($kegiatan->dapatDiubah(), 403, 'Kegiatan ini tidak dapat dibatalkan lagi.');

        $kegiatan->update(['status_kegiatan' => StatusKegiatan::DIBATALKAN->value]);

        return back()->with('status', 'Kegiatan berhasil dibatalkan.');
    }

    private function statusDariAksi(string $aksi): StatusKegiatan
    {
        return $aksi === 'terbitkan' ? StatusKegiatan::DITERBITKAN : StatusKegiatan::DRAF;
    }

    private function dataFormulir(): array
    {
        return [
            'daftarKawasan' => Kawasan::where('is_aktif', true)->orderBy('nama_kawasan')->get(),
            'jenisSaran' => self::JENIS_SARAN,
        ];
    }
}