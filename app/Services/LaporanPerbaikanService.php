<?php

namespace App\Services;

use App\Exceptions\LaporanTerkunciException;
use App\Models\BerkasMedia;
use App\Models\Laporan;
use App\Models\Pengguna;
use App\Models\PeristiwaLaporan;
use App\Models\RevisiLaporan;
use App\Support\LokasiSpasial;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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
                    ->where('laporan_id', $laporan->laporan_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if (! $laporanTerkunci->milikPengguna($pelapor) || ! $laporanTerkunci->bisaDiubahPelapor()) {
                    throw new LaporanTerkunciException;
                }

                $statusSebelumnya = (string) $laporanTerkunci->status_laporan;
                $nomorBerikutnya = $this->siapkanNomorRevisiBerikutnya($laporanTerkunci, $pelapor);
                $lokasi = LokasiSpasial::ekspresiTitik((float) $data['latitude'], (float) $data['longitude']);
                $sekarang = now();

                $revisiBaru = new RevisiLaporan([
                    'laporan_id' => $laporanTerkunci->laporan_id,
                    'nomor_revisi' => $nomorBerikutnya,
                    'diedit_oleh' => $pelapor->pengguna_id,
                    'kategori' => $data['kategori'],
                    'deskripsi' => $data['deskripsi'],
                    'insiden_pada' => $data['insiden_pada'] ?? null,
                    'alasan_revisi' => $data['alasan_revisi'] ?? null,
                ]);
                $revisiBaru->forceFill([
                    'lokasi' => $lokasi,
                    'dibuat_pada' => $sekarang,
                ])->save();

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
                    'lokasi' => $lokasi,
                    'insiden_pada' => $data['insiden_pada'] ?? null,
                    'status_laporan' => Laporan::STATUS_MENUNGGU_VERIFIKASI,
                    'diserahkan_pada' => $sekarang,
                    'diperbarui_pada' => $sekarang,
                ])->save();

                PeristiwaLaporan::create([
                    'laporan_id' => $laporanTerkunci->laporan_id,
                    'aktor_id' => $pelapor->pengguna_id,
                    'jenis_peristiwa' => 'PERBAIKAN_DIKIRIM',
                    'status_lama' => $statusSebelumnya,
                    'status_baru' => Laporan::STATUS_MENUNGGU_VERIFIKASI,
                    'catatan_peristiwa' => 'Pelapor mengirim ulang perbaikan laporan (Revisi #'.$nomorBerikutnya.').',
                    'terjadi_pada' => $sekarang,
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
    private function siapkanNomorRevisiBerikutnya(Laporan $laporan, Pengguna $pelapor): int
    {
        $maksRevisi = $laporan->revisi()->max('nomor_revisi');

        if ($maksRevisi === null) {
            $koordinatAwal = $laporan->koordinat();

            $revisiAwal = new RevisiLaporan([
                'laporan_id' => $laporan->laporan_id,
                'nomor_revisi' => 1,
                'diedit_oleh' => $pelapor->pengguna_id,
                'kategori' => $laporan->kategori,
                'deskripsi' => $laporan->deskripsi,
                'insiden_pada' => $laporan->insiden_pada,
                'alasan_revisi' => 'Laporan awal',
            ]);
            $revisiAwal->forceFill([
                'lokasi' => LokasiSpasial::ekspresiTitik(
                    $koordinatAwal['latitude'] ?? 0.0,
                    $koordinatAwal['longitude'] ?? 0.0,
                ),
                'dibuat_pada' => $laporan->dibuat_pada ?? now(),
            ])->save();

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

        $direktori = 'laporan/'.$laporan->kode_laporan.'/revisi-'.$revisiBaru->nomor_revisi;

        foreach ($fileFoto as $berkas) {
            $ekstensi = $berkas->extension() ?: $berkas->getClientOriginalExtension() ?: 'bin';
            $path = Storage::disk('local')->putFileAs($direktori, $berkas, Str::uuid().'.'.$ekstensi);
            $pathBaruTersimpan[] = $path;

            $media = BerkasMedia::create([
                'diunggah_oleh' => $pelapor->pengguna_id,
                'kunci_penyimpanan' => $path,
                'nama_file_asli' => $berkas->getClientOriginalName(),
                'tipe_mime' => $berkas->getMimeType() ?: ($berkas->getClientMimeType() ?: 'application/octet-stream'),
                'ukuran_file_byte' => $berkas->getSize() ?: 0,
                'checksum' => $berkas->getRealPath() ? hash_file('sha256', $berkas->getRealPath()) : null,
                'tingkat_akses' => BerkasMedia::AKSES_PRIVAT,
            ]);

            $lampiran[$media->berkas_media_id] = ['urutan_tampilan' => $urutan++];
        }

        if ($lampiran !== []) {
            $revisiBaru->berkasMedia()->attach($lampiran);
        }
    }

    /**
     * Saring ID media agar hanya berkas milik revisi laporan ini yang dapat dipertahankan.
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
            ->whereIn('berkas_media.berkas_media_id', $kandidatIds)
            ->whereExists(function ($query) use ($laporan): void {
                $query->selectRaw('1')
                    ->from('foto_revisi_laporan')
                    ->join('revisi_laporan', 'revisi_laporan.revisi_id', '=', 'foto_revisi_laporan.revisi_id')
                    ->whereColumn('foto_revisi_laporan.berkas_media_id', 'berkas_media.berkas_media_id')
                    ->where('revisi_laporan.laporan_id', $laporan->laporan_id);
            })
            ->pluck('berkas_media.berkas_media_id')
            ->map(fn ($id): int => (int) $id)
            ->all();
    }
}
