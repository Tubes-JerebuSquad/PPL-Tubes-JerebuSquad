<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'wilayah_id',
    'nama_kawasan',
    'jenis_kawasan',
    'status_kawasan',
    'sumber_id',
    'catatan',
    'is_aktif',
    'dibuat_oleh',
])]
class Kawasan extends Model
{
    public const CREATED_AT = 'dibuat_pada';

    public const UPDATED_AT = 'diperbarui_pada';

    protected $table = 'kawasan';

    protected $primaryKey = 'kawasan_id';

    protected function casts(): array
    {
        return [
            'is_aktif' => 'boolean',
            'dibuat_pada' => 'datetime',
            'diperbarui_pada' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'kawasan_id';
    }

    public function wilayah(): BelongsTo
    {
        return $this->belongsTo(Wilayah::class, 'wilayah_id', 'wilayah_id');
    }

    public function pembuat(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'dibuat_oleh', 'pengguna_id');
    }

    /**
     * Mencari kawasan berdasarkan nama kawasan atau nama wilayahnya.
     */
    public function scopeCari(Builder $query, ?string $kata): void
    {
        if (blank($kata)) {
            return;
        }

        $query->where(function (Builder $q) use ($kata): void {
            $q->whereLike('nama_kawasan', "%{$kata}%")
                ->orWhereHas('wilayah', fn (Builder $wilayah) => $wilayah->whereLike('nama_wilayah', "%{$kata}%"));
        });
    }

    /**
     * Membatasi daftar kawasan pada satu wilayah.
     */
    public function scopeBerwilayah(Builder $query, ?int $wilayahId): void
    {
        if ($wilayahId) {
            $query->where('wilayah_id', $wilayahId);
        }
    }

    /**
     * Membatasi daftar kawasan pada satu jenis kawasan.
     */
    public function scopeBerjenis(Builder $query, ?string $jenis): void
    {
        if (filled($jenis)) {
            $query->where('jenis_kawasan', $jenis);
        }
    }

    /**
     * Membatasi daftar kawasan pada satu status kawasan.
     */
    public function scopeBerstatusKawasan(Builder $query, ?string $status): void
    {
        if (filled($status)) {
            $query->where('status_kawasan', $status);
        }
    }

    /**
     * Membatasi daftar kawasan yang batas terbarunya beririsan dengan
     * bounding box (WGS 84 / EPSG:4326).
     *
     * Versi batas terbaru ditentukan dari MAX(nomor_versi) per kawasan.
     * Aturan ini bersifat sementara dan perlu dikonfirmasi dengan PBI-02.
     * Query hanya boleh dipanggil pada koneksi PostgreSQL/PostGIS.
     */
    public function scopeDalamBbox(
        Builder $query,
        float $minLon,
        float $minLat,
        float $maxLon,
        float $maxLat
    ): void {
        $query->whereExists(function ($sub) use ($minLon, $minLat, $maxLon, $maxLat): void {
            $sub->selectRaw('1')
                ->from('batas_kawasan')
                ->whereColumn('batas_kawasan.kawasan_id', 'kawasan.kawasan_id')
                ->whereRaw(
                    'batas_kawasan.nomor_versi = (
                        select max(b2.nomor_versi)
                        from batas_kawasan as b2
                        where b2.kawasan_id = kawasan.kawasan_id
                    )'
                )
                ->whereRaw(
                    'ST_Intersects(batas_kawasan.data_geometri, ST_MakeEnvelope(?, ?, ?, ?, 4326))',
                    [$minLon, $minLat, $maxLon, $maxLat]
                );
        });
    }

    /**
     * Membatasi daftar kawasan berdasarkan status data (aktif/nonaktif).
     */
    public function scopeBerstatusData(Builder $query, ?string $status): void
    {
        if ($status === 'aktif') {
            $query->where('is_aktif', true);
        } elseif ($status === 'nonaktif') {
            $query->where('is_aktif', false);
        }
    }
}
