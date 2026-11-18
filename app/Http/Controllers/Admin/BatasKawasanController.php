<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\BatasKawasanService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use InvalidArgumentException;

class BatasKawasanController extends Controller
{
    public function __construct(
        private readonly BatasKawasanService $batasKawasanService
    ) {}

    public function index(int $kawasanId): View
    {
        $kawasan = DB::table('kawasan')
            ->leftJoin('wilayah', 'kawasan.wilayah_id', '=', 'wilayah.wilayah_id')
            ->where('kawasan.kawasan_id', $kawasanId)
            ->select(
                'kawasan.*',
                'wilayah.nama_wilayah',
                'wilayah.kode_wilayah'
            )
            ->first();

        abort_if(!$kawasan, 404, 'Data kawasan tidak ditemukan.');

        $daftarSumber = DB::table('sumber_data')
            ->where('is_aktif', true)
            ->orderBy('nama_sumber')
            ->get(['sumber_id', 'nama_sumber', 'kode_crs']);

        $batasAktif = DB::selectOne("
            SELECT
                batas_id,
                nomor_versi,
                kode_crs,
                status_validasi,
                pesan_validasi,
                sumber_id,
                dibuat_pada,
                ST_AsGeoJSON(data_geometri) AS geojson,
                ST_NPoints(data_geometri) AS jumlah_titik,
                ROUND((ST_Area(data_geometri::geography) / 10000.0)::numeric, 2) AS luas_hektar
            FROM batas_kawasan
            WHERE kawasan_id = ?
            ORDER BY nomor_versi DESC
            LIMIT 1
        ", [$kawasanId]);

        $riwayatVersi = DB::select("
            SELECT
                bk.batas_id,
                bk.nomor_versi,
                bk.kode_crs,
                bk.status_validasi,
                bk.pesan_validasi,
                bk.dibuat_pada,
                p.nama_lengkap AS pembuat,
                ROUND((ST_Area(bk.data_geometri::geography) / 10000.0)::numeric, 2) AS luas_hektar
            FROM batas_kawasan bk
            LEFT JOIN pengguna p ON bk.dibuat_oleh = p.pengguna_id
            WHERE bk.kawasan_id = ?
            ORDER BY bk.nomor_versi DESC
        ", [$kawasanId]);

        return view('admin.kawasan.batas', [
            'kawasan' => $kawasan,
            'daftarSumber' => $daftarSumber,
            'batasAktif' => $batasAktif,
            'riwayatVersi' => $riwayatVersi,
        ]);
    }

    public function geojsonAktif(int $kawasanId)
    {
        $batas = DB::selectOne("
            SELECT
                bk.batas_id,
                bk.kawasan_id,
                k.nama_kawasan,
                k.jenis_kawasan,
                bk.nomor_versi,
                ST_AsGeoJSON(bk.data_geometri) AS geometry
            FROM batas_kawasan bk
            JOIN kawasan k ON k.kawasan_id = bk.kawasan_id
            WHERE bk.kawasan_id = ?
              AND bk.status_validasi = 'VALID'
            ORDER BY bk.nomor_versi DESC
            LIMIT 1
        ", [$kawasanId]);

        abort_if(!$batas, 404, 'Batas aktif kawasan belum tersedia.');

        return response()->json([
            'type' => 'Feature',
            'properties' => [
                'batas_id' => $batas->batas_id,
                'kawasan_id' => $batas->kawasan_id,
                'nama_kawasan' => $batas->nama_kawasan,
                'jenis_kawasan' => $batas->jenis_kawasan,
                'nomor_versi' => $batas->nomor_versi,
            ],
            'geometry' => json_decode($batas->geometry, true),
        ]);
    }

    public function simpanDigitasi(Request $request, int $kawasanId): RedirectResponse
    {
        $validated = $request->validate([
            'geojson_digitasi' => ['required', 'string'],
            'sumber_id' => ['nullable', 'integer', 'exists:sumber_data,sumber_id'],
            'mode_simpan' => ['nullable', 'in:draf,final'],
        ], [
            'geojson_digitasi.required' => 'Poligon batas kawasan belum digambar di peta.',
        ]);

        try {
            $catatan = ($validated['mode_simpan'] ?? 'final') === 'draf'
                ? 'Disimpan sebagai draf digitasi polygon (WGS 84 / EPSG:4326).'
                : 'Validasi geometri polygon digitasi berhasil (WGS 84 / EPSG:4326).';

            $versiBaru = $this->batasKawasanService->simpanDariGeojson(
                kawasanId: $kawasanId,
                rawJson: $validated['geojson_digitasi'],
                sumberId: $validated['sumber_id'] ?? null,
                penggunaId: $request->user()?->pengguna_id,
                catatanValidasi: $catatan
            );

            return redirect()
                ->route('admin.kawasan.batas.index', $kawasanId)
                ->with('success', "Batas kawasan berhasil disimpan sebagai versi ke-{$versiBaru}.");
        } catch (InvalidArgumentException $e) {
            return redirect()
                ->route('admin.kawasan.batas.index', $kawasanId)
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function imporGeojson(Request $request, int $kawasanId): RedirectResponse
    {
        $validated = $request->validate([
            'file_geojson' => ['required', 'file', 'max:5120'],
            'sumber_id' => ['nullable', 'integer', 'exists:sumber_data,sumber_id'],
        ], [
            'file_geojson.required' => 'Pilih berkas GeoJSON terlebih dahulu sebelum mengimpor.',
            'file_geojson.max' => 'Ukuran berkas maksimal 5 MB.',
        ]);

        $file = $request->file('file_geojson');
        $ekstensi = strtolower($file->getClientOriginalExtension());

        if (!in_array($ekstensi, ['geojson', 'json'], true)) {
            return redirect()
                ->route('admin.kawasan.batas.index', $kawasanId)
                ->with('error', 'Format berkas ditolak. Unggah berkas berekstensi .geojson atau .json.');
        }

        try {
            $rawJson = file_get_contents($file->getRealPath());

            $versiBaru = $this->batasKawasanService->simpanDariGeojson(
                kawasanId: $kawasanId,
                rawJson: $rawJson ?: '',
                sumberId: $validated['sumber_id'] ?? null,
                penggunaId: $request->user()?->pengguna_id,
                catatanValidasi: "Impor berkas '{$file->getClientOriginalName()}' valid (WGS 84 / EPSG:4326)."
            );

            return redirect()
                ->route('admin.kawasan.batas.index', $kawasanId)
                ->with('success', "Berkas GeoJSON berhasil diimpor sebagai batas versi ke-{$versiBaru}.");
        } catch (InvalidArgumentException $e) {
            return redirect()
                ->route('admin.kawasan.batas.index', $kawasanId)
                ->with('error', $e->getMessage());
        }
    }
}
