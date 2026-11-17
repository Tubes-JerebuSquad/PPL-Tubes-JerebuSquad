<?php

namespace App\Services;

use App\Enums\StatusLaporan;
use App\Exceptions\LaporanTerkunciException;
use App\Models\BerkasMedia;
use App\Models\Laporan;
use App\Models\Pengguna;
use App\Models\PeristiwaLaporan;
use App\Models\RevisiLaporan;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class LaporanPerbaikanService
{
    /**
     * Simpan versi revisi baru (append-only) dan kirim ulang laporan untuk verifikasi.
     *
     * @param  array<string, mixed>  $data
     * @param  array<int, UploadedFile>  $fileFoto
     * @param  array<int, int|string>  $pertahankanMediaIds
     *
     * @throws LaporanTerkunciException
     */
    public function kirimPerbaikan(
        Laporan $laporan,
        Pengguna $pelapor,
        array $data,
        array $fileFoto = [],
        array $pertahankanMediaIds = [],
    ): Laporan {
        $pathBaruTersimpan = [];

        try {
            return DB::transaction(function () use (
                $laporan,
                $pelapor,
                $data,
                $fileFoto,
                $pertahankanMediaIds,
                &$pathBaruTersimpan,
            ): Laporan {
                $laporanTerkunci = Laporan::query()
                    ->whereKey($laporan->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if (! $laporanTerkunci->milikPengguna($pelapor) || ! $laporanTerkunci->bolehDiperbaiki()) {
                    throw new LaporanTerkunciException;
                }

                $statusSebelumnya = (string) $laporanTerkunci->status;
                $nomorBerikutnya = $this->siapkanNomorRevisiBerikutnya($laporanTerkunci);

                $revisiBaru = RevisiLaporan::create([
                    'laporan_id' => $laporanTerkunci->id,
                    'nomor_revisi' => $nomorBerikutnya,
                    'kategori' => $data['kategori'],
                    'deskripsi' => $data['deskripsi'],
                    'latitude' => $data['latitude'],
                    'longitude' => $data['longitude'],
                    'waktu_kejadian' => $data['waktu_kejadian'] ?? null,
                    'catatan_revisi' => $data['catatan_revisi'] ?? null,
                ]);

                $this->lampirkanFotoRevisi(
                    $laporanTerkunci,
                    $revisiBaru,
                    $pelapor,
                    $fileFoto,
                    $pertahankanMediaIds,
                    $pathBaruTersimpan,
                );

                $laporanTerkunci->forceFill([
                    'kategori' => $data['kategori'],
                    'deskripsi' => $data['deskripsi'],
                    'wilayah_id' => $data['wilayah_id'],
                    'kawasan_id' => $data['kawasan_id'] ?? null,
                    'latitude' => $data['latitude'],
                    'longitude' => $data['longitude'],
                    'waktu_kejadian' => $data['waktu_kejadian'] ?? null,
                    'status' => StatusLaporan::MENUNGGU_VERIFIKASI->value,
                ])->save();

                PeristiwaLaporan::create([
                    'laporan_id' => $laporanTerkunci->id,
                    'pengguna_id' => $pelapor->pengguna_id,
                    'jenis_peristiwa' => 'PERBAIKAN_DIKIRIM',
                    'status_lama' => $statusSebelumnya,
                    'status_baru' => StatusLaporan::MENUNGGU_VERIFIKASI->value,
                    'deskripsi' => 'Pelapor mengirim ulang perbaikan laporan (Revisi #'.$nomorBerikutnya.').',
                    'waktu_peristiwa' => now(),
                ]);

                return $laporanTerkunci->refresh();
            });
        } catch (Throwable $exception) {
            if ($pathBaruTersimpan !== []) {
                Storage::disk('local')->delete($pathBaruTersimpan);
            }

            throw $exception;
        }
    }

    /**
     * Pastikan versi awal tercatat sebelum menambah revisi baru bila belum ada entri revisi.
     */
    private function siapkanNomorRevisiBerikutnya(Laporan $laporan): int
    {
        $maksRevisi = $laporan->revisi()->max('nomor_revisi');

        if ($maksRevisi === null) {
            RevisiLaporan::create([
                'laporan_id' => $laporan->id,
                'nomor_revisi' => 1,
                'kategori' => $laporan->kategori,
                'deskripsi' => $laporan->deskripsi,
                'latitude' => $laporan->latitude,
                'longitude' => $laporan->longitude,
                'waktu_kejadian' => $laporan->waktu_kejadian,
                'catatan_revisi' => 'Laporan awal',
            ]);

            return 2;
        }

        return ((int) $maksRevisi) + 1;
    }

    /**
     * @param  array<int, UploadedFile>  $fileFoto
     * @param  array<int, int|string>  $pertahankanMediaIds
     * @param  array<int, string>  $pathBaruTersimpan
     */
    private function lampirkanFotoRevisi(
        Laporan $laporan,
        RevisiLaporan $revisiBaru,
        Pengguna $pelapor,
        array $fileFoto,
        array $pertahankanMediaIds,
        array &$pathBaruTersimpan,
    ): void {
        $urutan = 1;
        $lampiran = [];

        $idsValid = $this->mediaMilikLaporan($laporan, $pertahankanMediaIds);

        foreach ($idsValid as $mediaId) {
            $lampiran[$mediaId] = ['urutan_tampilan' => $urutan++];
        }

        $direktori = 'laporan/'.$laporan->id.'/revisi-'.$revisiBaru->nomor_revisi;

        foreach ($fileFoto as $berkas) {
            $path = $berkas->store($direktori, 'local');
            $pathBaruTersimpan[] = $path;

            $media = BerkasMedia::create([
                'nama_asli' => $berkas->getClientOriginalName(),
                'nama_file' => basename($path),
                'path_file' => $path,
                'mime_type' => $berkas->getClientMimeType() ?: 'application/octet-stream',
                'ukuran_byte' => $berkas->getSize() ?: 0,
                'diunggah_oleh' => $pelapor->pengguna_id,
            ]);

            $lampiran[$media->id] = ['urutan_tampilan' => $urutan++];
        }

        if ($lampiran !== []) {
            $revisiBaru->foto()->attach($lampiran);
        }
    }

    /**
     * Saring ID media agar hanya foto yang memang berasal dari revisi laporan ini yang dapat dipertahankan.
     *
     * @param  array<int, int|string>  $pertahankanMediaIds
     * @return array<int, int>
     */
    private function mediaMilikLaporan(Laporan $laporan, array $pertahankanMediaIds): array
    {
        $kandidatIds = array_values(array_unique(array_filter(
            array_map('intval', $pertahankanMediaIds),
            fn (int $id): bool => $id > 0,
        )));

        if ($kandidatIds === []) {
            return [];
        }

        return BerkasMedia::query()
            ->whereIn('berkas_media.id', $kandidatIds)
            ->whereExists(function ($query) use ($laporan): void {
                $query->selectRaw('1')
                    ->from('foto_revisi_laporan')
                    ->join('revisi_laporan', 'revisi_laporan.id', '=', 'foto_revisi_laporan.revisi_laporan_id')
                    ->whereColumn('foto_revisi_laporan.berkas_media_id', 'berkas_media.id')
                    ->where('revisi_laporan.laporan_id', $laporan->id);
            })
            ->pluck('berkas_media.id')
            ->map(fn ($id): int => (int) $id)
            ->all();
    }
}
