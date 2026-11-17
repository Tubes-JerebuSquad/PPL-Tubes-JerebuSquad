<?php

namespace App\Http\Controllers\Masyarakat;

use App\Enums\KategoriLaporan;
use App\Enums\StatusLaporan;
use App\Exceptions\LaporanTerkunciException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Masyarakat\PerbaikiLaporanRequest;
use App\Http\Requests\Masyarakat\StoreLaporanRequest;
use App\Models\BerkasMedia;
use App\Models\Kawasan;
use App\Models\Laporan;
use App\Models\Pengguna;
use App\Models\Wilayah;
use App\Services\LaporanPerbaikanService;
use App\Support\LokasiSpasial;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

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
                    $sub->whereLike('kode_laporan', '%'.$cari.'%')
                        ->orWhereLike('kategori', '%'.$cari.'%')
                        ->orWhereLike('deskripsi', '%'.$cari.'%');
                }),
            )
            ->when(
                $status !== null && $status !== '',
                fn (Builder $query) => $query->whereIn('status_laporan', $this->varianNilaiStatus($status)),
            )
            ->orderByDesc('diperbarui_pada')
            ->orderByDesc('laporan_id')
            ->paginate(15)
            ->withQueryString();

        return view('masyarakat.laporan.index', [
            'daftarLaporan' => $daftarLaporan,
            'pilihanStatus' => StatusLaporan::pilihan(),
        ]);
    }

    public function create(Request $request): View
    {
        abort_unless($request->user()?->isMasyarakat(), 403, 'Akses khusus Masyarakat.');

        return view('masyarakat.laporan.create', [
            'kategori' => StoreLaporanRequest::KATEGORI,
            'wilayah' => DB::table('wilayah')
                ->where('is_aktif', true)
                ->orderBy('nama_wilayah')
                ->get(['wilayah_id', 'nama_wilayah']),
        ]);
    }

    public function store(StoreLaporanRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $pengguna = $request->user();
        $kode = $this->buatKode();
        $tersimpan = [];

        try {
            DB::transaction(function () use ($data, $request, $pengguna, $kode, &$tersimpan) {
                $sekarang = now();
                $lokasi = LokasiSpasial::ekspresiTitik((float) $data['latitude'], (float) $data['longitude']);

                $laporanId = DB::table('laporan')->insertGetId([
                    'kode_laporan' => $kode,
                    'pelapor_id' => $pengguna->pengguna_id,
                    'wilayah_id' => $data['wilayah_id'],
                    'kategori' => $data['kategori'],
                    'deskripsi' => $data['deskripsi'],
                    'insiden_pada' => $data['insiden_pada'],
                    'lokasi' => $lokasi,
                    'status_laporan' => Laporan::STATUS_MENUNGGU_VERIFIKASI,
                    'diserahkan_pada' => $sekarang,
                    'dibuat_pada' => $sekarang,
                    'diperbarui_pada' => $sekarang,
                ], 'laporan_id');

                $revisiId = DB::table('revisi_laporan')->insertGetId([
                    'laporan_id' => $laporanId,
                    'nomor_revisi' => 1,
                    'kategori' => $data['kategori'],
                    'deskripsi' => $data['deskripsi'],
                    'insiden_pada' => $data['insiden_pada'],
                    'lokasi' => $lokasi,
                    'alasan_revisi' => null,
                    'diedit_oleh' => $pengguna->pengguna_id,
                    'dibuat_pada' => $sekarang,
                ], 'revisi_id');

                $berkas = $request->file('foto', []);
                if ($request->hasFile('video')) {
                    $berkas[] = $request->file('video');
                }

                foreach (array_values($berkas) as $urutan => $file) {
                    $berkasId = $this->simpanBerkas($file, $kode, $pengguna->pengguna_id, $sekarang, $tersimpan);

                    DB::table('foto_revisi_laporan')->insert([
                        'revisi_id' => $revisiId,
                        'berkas_media_id' => $berkasId,
                        'urutan_tampilan' => $urutan + 1,
                    ]);
                }

                DB::table('peristiwa_laporan')->insert([
                    'laporan_id' => $laporanId,
                    'jenis_peristiwa' => 'DIAJUKAN',
                    'catatan_peristiwa' => 'Laporan diajukan oleh pelapor.',
                    'terjadi_pada' => $sekarang,
                    'aktor_id' => $pengguna->pengguna_id,
                    'status_lama' => null,
                    'status_baru' => Laporan::STATUS_MENUNGGU_VERIFIKASI,
                ]);
            });
        } catch (Throwable $e) {
            Storage::disk('local')->delete($tersimpan);
            throw $e;
        }

        return redirect()
            ->route('masyarakat.dashboard')
            ->with('status', "Laporan berhasil diajukan dengan kode {$kode}.");
    }

    public function show(Laporan $laporan): View
    {
        Gate::authorize('view', $laporan);

        $laporan->load(['wilayah', 'kawasan', 'revisi.berkasMedia', 'peristiwa.aktor']);

        return view('masyarakat.laporan.show', [
            'laporan' => $laporan,
            'koordinat' => $laporan->koordinat(),
        ]);
    }

    public function edit(Laporan $laporan): View
    {
        Gate::authorize('update', $laporan);

        $laporan->load(['wilayah', 'kawasan', 'revisiTerbaru.berkasMedia']);

        return view('masyarakat.laporan.edit', [
            'laporan' => $laporan,
            'koordinat' => $laporan->koordinat(),
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
            ->with('status', 'Perbaikan laporan #'.$laporan->kode_laporan.' berhasil dikirim ulang untuk ditinjau kembali.');
    }

    public function media(Laporan $laporan, BerkasMedia $berkasMedia): BinaryFileResponse
    {
        Gate::authorize('view', $laporan);

        abort_unless($laporan->memuatMedia($berkasMedia), 404);

        $disk = Storage::disk('local');
        abort_unless($disk->exists($berkasMedia->kunci_penyimpanan), 404);

        return response()->file($disk->path($berkasMedia->kunci_penyimpanan), [
            'Content-Type' => $berkasMedia->tipe_mime,
        ]);
    }

    private function pelapor(Request $request): Pengguna
    {
        $pengguna = $request->user();
        abort_unless($pengguna instanceof Pengguna, 403);

        return $pengguna;
    }

    /**
     * @return array<int, string>
     */
    private function varianNilaiStatus(string $status): array
    {
        $varian = [$status, strtolower($status)];

        if ($status === StatusLaporan::DIKEMBALIKAN->value) {
            $varian[] = 'PERLU_PERBAIKAN';
            $varian[] = 'perlu_perbaikan';
        }

        return array_values(array_unique($varian));
    }

    private function buatKode(): string
    {
        do {
            $kode = 'LAP-'.now()->format('Ymd').'-'.strtoupper(Str::random(6));
        } while (DB::table('laporan')->where('kode_laporan', $kode)->exists());

        return $kode;
    }

    private function simpanBerkas(UploadedFile $file, string $kode, int $penggunaId, $sekarang, array &$tersimpan): int
    {
        $mime = $file->getMimeType();
        $ukuran = $file->getSize();
        $checksum = hash_file('sha256', $file->getRealPath());
        $asli = $file->getClientOriginalName();
        $ekstensi = $file->extension() ?: $file->getClientOriginalExtension();

        $path = Storage::disk('local')->putFileAs("laporan/{$kode}", $file, Str::uuid().'.'.$ekstensi);
        $tersimpan[] = $path;

        return DB::table('berkas_media')->insertGetId([
            'nama_file_asli' => $asli,
            'tipe_mime' => $mime,
            'ukuran_file_byte' => $ukuran,
            'kunci_penyimpanan' => $path,
            'checksum' => $checksum,
            'tingkat_akses' => BerkasMedia::AKSES_PRIVAT,
            'diunggah_oleh' => $penggunaId,
            'dibuat_pada' => $sekarang,
        ], 'berkas_media_id');
    }
}
