<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class BatasKawasanService
{
    /**
     * Memvalidasi string GeoJSON dan menyimpannya sebagai nomor versi terbaru pada tabel batas_kawasan.
     */
    public function simpanDariGeojson(
        int $kawasanId,
        string $rawJson,
        ?int $sumberId = null,
        ?int $penggunaId = null,
        string $catatanValidasi = 'Validasi topologi PostGIS berhasil (WGS 84 / EPSG:4326).'
    ): int {
        $decoded = json_decode($rawJson, true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded) || empty($decoded['type'])) {
            throw new InvalidArgumentException('Format berkas tidak valid. Pastikan file merupakan GeoJSON yang sah.');
        }

        // 1. Validasi CRS jika didefinisikan di dalam file GeoJSON
        if (isset($decoded['crs'])) {
            $crsName = data_get($decoded, 'crs.properties.name', '');
            $crsDiizinkan = ['EPSG:4326', 'urn:ogc:def:crs:OGC:1.3:CRS84', 'urn:ogc:def:crs:EPSG::4326', 'WGS84'];
            if (!in_array($crsName, $crsDiizinkan, true)) {
                throw new InvalidArgumentException("Sistem koordinat (CRS) '{$crsName}' tidak dikenali. Gunakan standar WGS 84 (EPSG:4326).");
            }
        }

        // 2. Ekstrak daftar geometri Polygon / MultiPolygon dari FeatureCollection, Feature, atau Geometry langsung
        $geometries = $this->ekstrakGeometriPoligon($decoded);

        // 3. Jalankan validasi spasial PostGIS & simpan dalam 1 transaksi atomik (anti data parsial)
        return DB::transaction(function () use ($kawasanId, $sumberId, $penggunaId, $catatanValidasi, $geometries) {
            $sqlParts = [];
            $bindings = [];

            foreach ($geometries as $index => $geomArray) {
                $geomJson = json_encode($geomArray);

                $cek = DB::selectOne("
                    SELECT
                        ST_IsValid(ST_SetSRID(ST_GeomFromGeoJSON(?), 4326)) AS valid,
                        ST_IsValidReason(ST_SetSRID(ST_GeomFromGeoJSON(?), 4326)) AS alasan,
                        ST_XMin(ST_SetSRID(ST_GeomFromGeoJSON(?), 4326)) AS min_lon,
                        ST_XMax(ST_SetSRID(ST_GeomFromGeoJSON(?), 4326)) AS max_lon,
                        ST_YMin(ST_SetSRID(ST_GeomFromGeoJSON(?), 4326)) AS min_lat,
                        ST_YMax(ST_SetSRID(ST_GeomFromGeoJSON(?), 4326)) AS max_lat
                ", [$geomJson, $geomJson, $geomJson, $geomJson, $geomJson, $geomJson]);

                if (!$cek || $cek->min_lon === null) {
                    throw new InvalidArgumentException('Koordinat geometri tidak dapat dibaca oleh PostGIS.');
                }

                if ($cek->min_lon < -180 || $cek->max_lon > 180 || $cek->min_lat < -90 || $cek->max_lat > 90) {
                    throw new InvalidArgumentException('Koordinat berada di luar jangkauan derajat WGS 84 (EPSG:4326). Pastikan tidak menggunakan proyeksi meter.');
                }

                if (!$cek->valid) {
                    $urutan = $index + 1;
                    throw new InvalidArgumentException("Geometri poligon ke-{$urutan} rusak atau tidak valid: {$cek->alasan}");
                }

                $sqlParts[] = 'ST_SetSRID(ST_GeomFromGeoJSON(?), 4326)';
                $bindings[] = $geomJson;
            }

            DB::table('kawasan')
                ->where('kawasan_id', $kawasanId)
                ->lockForUpdate()
                ->first();

            $versiTerakhir = DB::table('batas_kawasan')
                ->where('kawasan_id', $kawasanId)
                ->max('nomor_versi') ?? 0;

            $versiBaru = $versiTerakhir + 1;

            // Gabungkan jika FeatureCollection memiliki lebih dari 1 poligon menjadi MultiPolygon
            $geomExpression = count($sqlParts) === 1
                ? "ST_Multi({$sqlParts[0]})"
                : 'ST_Multi(ST_Collect(ARRAY[' . implode(', ', $sqlParts) . ']))';

            $insertBindings = array_merge(
                [$kawasanId, $versiBaru],
                $bindings,
                ['EPSG:4326', $sumberId, 'VALID', $catatanValidasi, $penggunaId]
            );

            DB::statement("
                INSERT INTO batas_kawasan (
                    kawasan_id,
                    nomor_versi,
                    data_geometri,
                    kode_crs,
                    sumber_id,
                    status_validasi,
                    pesan_validasi,
                    dibuat_oleh,
                    dibuat_pada
                ) VALUES (?, ?, {$geomExpression}, ?, ?, ?, ?, ?, NOW())
            ", $insertBindings);

            return $versiBaru;
        });
    }

    /**
     * Mengekstrak array geometri dan memastikan tipenya hanya Polygon atau MultiPolygon.
     */
    private function ekstrakGeometriPoligon(array $geojson): array
    {
        $type = $geojson['type'];
        $hasil = [];

        if ($type === 'FeatureCollection') {
            $features = $geojson['features'] ?? null;
            if (!is_array($features) || count($features) === 0) {
                throw new InvalidArgumentException('GeoJSON FeatureCollection kosong atau tidak memiliki fitur batas kawasan.');
            }

            foreach ($features as $feature) {
                $geom = $feature['geometry'] ?? null;
                $this->validasiTipeGeometri($geom);
                $hasil[] = $geom;
            }
        } elseif ($type === 'Feature') {
            $geom = $geojson['geometry'] ?? null;
            $this->validasiTipeGeometri($geom);
            $hasil[] = $geom;
        } else {
            $this->validasiTipeGeometri($geojson);
            $hasil[] = $geojson;
        }

        return $hasil;
    }

    private function validasiTipeGeometri(?array $geom): void
    {
        if (!is_array($geom) || empty($geom['type']) || empty($geom['coordinates'])) {
            throw new InvalidArgumentException('Struktur geometri di dalam GeoJSON tidak lengkap.');
        }

        if (!in_array($geom['type'], ['Polygon', 'MultiPolygon'], true)) {
            throw new InvalidArgumentException("Tipe geometri '{$geom['type']}' tidak didukung. Batas kawasan harus berupa Polygon atau MultiPolygon.");
        }
    }
}