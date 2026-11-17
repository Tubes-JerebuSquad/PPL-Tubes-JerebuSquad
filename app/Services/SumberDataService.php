<?php

namespace App\Services;

use App\Enums\AksiRiwayat;
use App\Exceptions\MetadataBelumLengkapException;
use App\Models\Pengguna;
use App\Models\RiwayatSumberData;
use App\Models\SumberData;
use Illuminate\Support\Facades\DB;

class SumberDataService
{
    /**
     * Field metadata yang dilacak pada riwayat perubahan.
     *
     * @var array<int, string>
     */
    private const FIELD_TERPANTAU = [
        'nama_sumber',
        'jenis_sumber',
        'nama_penyedia',
        'url_sumber',
        'nama_lisensi',
        'format_data',
        'deskripsi_cakupan',
        'periode_mulai',
        'periode_selesai',
        'kode_crs',
        'interval_penyegaran_jam',
        'status_kualitas',
    ];

    /**
     * Simpan dataset baru sebagai nonaktif beserta entri riwayat DIBUAT.
     *
     * @param  array<string, mixed>  $data
     */
    public function simpan(array $data, ?Pengguna $pengguna = null): SumberData
    {
        return DB::transaction(function () use ($data, $pengguna) {
            $sumber = new SumberData($data);
            $sumber->is_aktif = false;
            $sumber->dibuat_oleh = $pengguna?->pengguna_id;
            $sumber->save();

            $perubahan = [];

            foreach ($this->petaNilai($sumber, self::FIELD_TERPANTAU) as $field => $nilai) {
                if ($nilai === null || $nilai === '') {
                    continue;
                }

                $perubahan[$field] = ['lama' => null, 'baru' => $nilai];
            }

            $this->catat($sumber, AksiRiwayat::DIBUAT, $perubahan, $pengguna);

            return $sumber->refresh();
        });
    }

    /**
     * Perbarui metadata dan catat hanya field yang benar-benar berubah.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws MetadataBelumLengkapException
     */
    public function perbarui(SumberData $sumber, array $data, ?Pengguna $pengguna = null): SumberData
    {
        return DB::transaction(function () use ($sumber, $data, $pengguna) {
            $sebelum = $this->petaNilai($sumber, self::FIELD_TERPANTAU);

            $sumber->fill($data);

            if ($sumber->is_aktif) {
                $belumLengkap = $sumber->metadataBelumLengkap();

                if ($belumLengkap !== []) {
                    throw new MetadataBelumLengkapException($belumLengkap);
                }
            }

            $perubahan = $this->bandingkan($sebelum, $this->petaNilai($sumber, self::FIELD_TERPANTAU));

            if ($perubahan === []) {
                return $sumber;
            }

            $sumber->save();

            $this->catat($sumber, AksiRiwayat::DIPERBARUI, $perubahan, $pengguna);

            return $sumber->refresh();
        });
    }

    /**
     * Aktifkan dataset bila seluruh metadata wajib sudah terisi.
     *
     * @throws MetadataBelumLengkapException
     */
    public function aktifkan(SumberData $sumber, ?Pengguna $pengguna = null): SumberData
    {
        return DB::transaction(function () use ($sumber, $pengguna) {
            if ($sumber->is_aktif) {
                return $sumber;
            }

            $belumLengkap = $sumber->metadataBelumLengkap();

            if ($belumLengkap !== []) {
                throw new MetadataBelumLengkapException($belumLengkap);
            }

            $sumber->forceFill(['is_aktif' => true])->save();

            $this->catat($sumber, AksiRiwayat::DIAKTIFKAN, [
                'is_aktif' => ['lama' => false, 'baru' => true],
            ], $pengguna);

            return $sumber->refresh();
        });
    }

    /**
     * Nonaktifkan dataset tanpa menghapus data maupun riwayatnya.
     */
    public function nonaktifkan(SumberData $sumber, ?Pengguna $pengguna = null, ?string $alasan = null): SumberData
    {
        return DB::transaction(function () use ($sumber, $pengguna, $alasan) {
            if (! $sumber->is_aktif) {
                return $sumber;
            }

            $sumber->forceFill(['is_aktif' => false])->save();

            $this->catat($sumber, AksiRiwayat::DINONAKTIFKAN, [
                'is_aktif' => ['lama' => true, 'baru' => false],
            ], $pengguna, $alasan);

            return $sumber->refresh();
        });
    }

    /**
     * @param  array<int, string>  $field
     * @return array<string, mixed>
     */
    private function petaNilai(SumberData $sumber, array $field): array
    {
        $nilai = [];

        foreach ($field as $nama) {
            $nilai[$nama] = $this->normalisasi($sumber->getAttribute($nama));
        }

        return $nilai;
    }

    /**
     * @param  array<string, mixed>  $sebelum
     * @param  array<string, mixed>  $sesudah
     * @return array<string, array{lama: mixed, baru: mixed}>
     */
    private function bandingkan(array $sebelum, array $sesudah): array
    {
        $perubahan = [];

        foreach ($sesudah as $field => $nilaiBaru) {
            $nilaiLama = $sebelum[$field] ?? null;

            if ($nilaiLama === $nilaiBaru) {
                continue;
            }

            $perubahan[$field] = [
                'lama' => $nilaiLama,
                'baru' => $nilaiBaru,
            ];
        }

        return $perubahan;
    }

    private function normalisasi(mixed $nilai): mixed
    {
        return match (true) {
            $nilai === null => null,
            $nilai instanceof \BackedEnum => $nilai->value,
            $nilai instanceof \DateTimeInterface => $nilai->format('Y-m-d'),
            is_string($nilai) => trim($nilai),
            default => $nilai,
        };
    }

    /**
     * @param  array<string, array{lama: mixed, baru: mixed}>  $perubahan
     */
    private function catat(
        SumberData $sumber,
        AksiRiwayat $aksi,
        ?array $perubahan,
        ?Pengguna $pengguna,
        ?string $catatan = null,
    ): RiwayatSumberData {
        return RiwayatSumberData::create([
            'sumber_id' => $sumber->sumber_id,
            'pengguna_id' => $pengguna?->pengguna_id,
            'aksi' => $aksi,
            'perubahan' => $perubahan === [] ? null : $perubahan,
            'catatan' => $catatan,
        ]);
    }
}
