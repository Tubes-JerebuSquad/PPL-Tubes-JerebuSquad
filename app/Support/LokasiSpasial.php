<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

class LokasiSpasial
{
    /**
     * Bangun ekspresi/nilai kolom `lokasi` sesuai driver database (PostGIS atau WKT SQLite).
     */
    public static function ekspresiTitik(float $latitude, float $longitude): mixed
    {
        $x = number_format($longitude, 7, '.', '');
        $y = number_format($latitude, 7, '.', '');

        if (DB::getDriverName() === 'pgsql') {
            return DB::raw("ST_SetSRID(ST_MakePoint({$x}, {$y}), 4326)");
        }

        return "POINT({$x} {$y})";
    }

    /**
     * Ekstrak pasangan [latitude, longitude] dari teks WKT `POINT(lng lat)`.
     *
     * @return array{latitude: float, longitude: float}|null
     */
    public static function dariWkt(?string $wkt): ?array
    {
        if (! is_string($wkt) || ! preg_match('/POINT\s*\(\s*([-+]?\d+(?:\.\d+)?)\s+([-+]?\d+(?:\.\d+)?)\s*\)/i', $wkt, $cocok)) {
            return null;
        }

        return [
            'latitude' => (float) $cocok[2],
            'longitude' => (float) $cocok[1],
        ];
    }

    /**
     * Ambil koordinat [latitude, longitude] untuk satu baris tabel spasial.
     *
     * @return array{latitude: float|null, longitude: float|null}
     */
    public static function koordinatDariTabel(string $tabel, string $kolomPk, int $id, mixed $nilaiMentah = null): array
    {
        $hasil = self::dariWkt(is_string($nilaiMentah) ? $nilaiMentah : null);

        if ($hasil !== null) {
            return $hasil;
        }

        if (DB::getDriverName() === 'pgsql') {
            $baris = DB::table($tabel)
                ->where($kolomPk, $id)
                ->selectRaw('ST_Y(lokasi) as latitude, ST_X(lokasi) as longitude')
                ->first();

            if ($baris !== null && $baris->latitude !== null && $baris->longitude !== null) {
                return [
                    'latitude' => (float) $baris->latitude,
                    'longitude' => (float) $baris->longitude,
                ];
            }
        }

        return ['latitude' => null, 'longitude' => null];
    }
}
