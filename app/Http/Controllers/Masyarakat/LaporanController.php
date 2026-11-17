<?php

namespace App\Http\Controllers\Masyarakat;

use App\Http\Controllers\Controller;
use App\Http\Requests\Masyarakat\StoreLaporanRequest;
use App\Models\Laporan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class LaporanController extends Controller
{
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
                $lokasi = $this->ekspresiLokasi((float) $data['latitude'], (float) $data['longitude']);

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

    private function buatKode(): string
    {
        do {
            $kode = 'LAP-'.now()->format('Ymd').'-'.strtoupper(Str::random(6));
        } while (DB::table('laporan')->where('kode_laporan', $kode)->exists());

        return $kode;
    }

    /**
     * PostgreSQL/PostGIS: titik geometry SRID 4326 (urutan: longitude, latitude).
     * SQLite (test): disimpan sebagai teks WKT.
     */
    private function ekspresiLokasi(float $lat, float $lng): mixed
    {
        $x = number_format($lng, 7, '.', '');
        $y = number_format($lat, 7, '.', '');

        if (DB::getDriverName() === 'pgsql') {
            return DB::raw("ST_SetSRID(ST_MakePoint({$x}, {$y}), 4326)");
        }

        return "POINT({$x} {$y})";
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
            'tingkat_akses' => 'PRIVATE',
            'diunggah_oleh' => $penggunaId,
            'dibuat_pada' => $sekarang,
        ], 'berkas_media_id');
    }
}