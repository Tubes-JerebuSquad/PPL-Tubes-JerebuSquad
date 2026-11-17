<?php

namespace App\Services;

use App\Models\Laporan;
use App\Models\PeristiwaLaporan;
use App\Models\Pengguna;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TriaseLaporan
{
    /**
     * @param  array{tindakan: string, kategori_triase?: ?string, prioritas_triase?: ?string, catatan_publik?: ?string, catatan_internal?: ?string}  $data
     */
    public function jalankan(Laporan $laporan, Pengguna $pengawas, array $data): Laporan
    {
        return DB::transaction(function () use ($laporan, $pengawas, $data): Laporan {
            $terkini = Laporan::query()
                ->whereKey($laporan->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if (! $terkini->bisaDitriase()) {
                throw ValidationException::withMessages([
                    'tindakan' => 'Laporan ini sudah tidak berstatus menunggu verifikasi sehingga tidak dapat ditriase ulang.',
                ]);
            }

            $tindakan  = $data['tindakan'];
            $statusLama = $terkini->status_laporan;
            $statusBaru = Laporan::STATUS_HASIL_TINDAKAN[$tindakan];
            $waktu      = now();

            $terkini->update([
                'status_laporan'   => $statusBaru,
                'kategori_triase'  => $data['kategori_triase'] ?? null,
                'prioritas_triase' => $data['prioritas_triase'] ?? null,
                'catatan_publik'   => $data['catatan_publik'] ?? null,
                'catatan_internal' => $data['catatan_internal'] ?? null,
                'ditriase_oleh'    => $pengawas->pengguna_id,
                'ditriase_pada'    => $waktu,
            ]);

            PeristiwaLaporan::create([
                'laporan_id'      => $terkini->getKey(),
                'aktor_id'        => $pengawas->pengguna_id,
                'jenis_peristiwa' => PeristiwaLaporan::JENIS_HASIL_TINDAKAN[$tindakan],
                'status_lama'     => $statusLama,
                'status_baru'     => $statusBaru,
                // Hanya catatan publik yang masuk riwayat; catatan internal tidak.
                'catatan_peristiwa' => $data['catatan_publik'] ?? null,
                'terjadi_pada'    => $waktu,
            ]);

            return $terkini;
        });
    }
}
