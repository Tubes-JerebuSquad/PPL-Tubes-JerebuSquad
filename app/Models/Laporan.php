<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'kode_laporan',
    'pelapor_id',
    'wilayah_id',
    'kawasan_id',
    'kategori',
    'deskripsi',
    'insiden_pada',
    'status_laporan',
    'diserahkan_pada',
])]
class Laporan extends Model
{
    public const CREATED_AT = 'dibuat_pada';

    public const UPDATED_AT = 'diperbarui_pada';

    // Nilai status (sepakati bersama tim; huruf besar seperti status lain di proyek ini).
    public const STATUS_DRAFT = 'DRAFT';

    public const STATUS_MENUNGGU_VERIFIKASI = 'MENUNGGU_VERIFIKASI';

    public const STATUS_DIKEMBALIKAN = 'DIKEMBALIKAN';

    public const STATUS_DIVERIFIKASI = 'DIVERIFIKASI';

    public const STATUS_DITOLAK = 'DITOLAK';

    public const STATUS_DALAM_INSPEKSI = 'DALAM_INSPEKSI';

    public const STATUS_SELESAI = 'SELESAI';

    protected $table = 'laporan';

    protected $primaryKey = 'laporan_id';

    /**
     * Kolom `lokasi` (geometry) tidak ada di $fillable dan di casts:
     * isi lewat ekspresi PostGIS, mis. ST_SetSRID(ST_MakePoint(lng, lat), 4326).
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'insiden_pada' => 'datetime',
            'diserahkan_pada' => 'datetime',
        ];
    }

    /**
     * Pelapor hanya boleh mengubah laporan berstatus draf atau dikembalikan (FR-09).
     */
    public function bisaDiubahPelapor(): bool
    {
        return in_array($this->status_laporan, [self::STATUS_DRAFT, self::STATUS_DIKEMBALIKAN], true);
    }

    /**
     * @return BelongsTo<Pengguna, $this>
     */
    public function pelapor(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'pelapor_id', 'pengguna_id');
    }

    /**
     * @return HasMany<RevisiLaporan, $this>
     */
    public function revisi(): HasMany
    {
        return $this->hasMany(RevisiLaporan::class, 'laporan_id', 'laporan_id')->orderBy('nomor_revisi');
    }

    /**
     * @return HasMany<PeristiwaLaporan, $this>
     */
    public function peristiwa(): HasMany
    {
        return $this->hasMany(PeristiwaLaporan::class, 'laporan_id', 'laporan_id')->orderBy('terjadi_pada');
    }
}