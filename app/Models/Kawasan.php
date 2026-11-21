<?php

namespace App\Models;

use Database\Factories\KawasanFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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
    /** @use HasFactory<KawasanFactory> */
    use HasFactory;

    public const CREATED_AT = 'dibuat_pada';

    public const UPDATED_AT = 'diperbarui_pada';

    protected $table = 'kawasan';

    protected $primaryKey = 'kawasan_id';

    /**
     * @return array<string, string>
     */
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

    /**
     * @return BelongsTo<Wilayah, $this>
     */
    public function wilayah(): BelongsTo
    {
        return $this->belongsTo(Wilayah::class, 'wilayah_id', 'wilayah_id');
    }

    /**
     * @return BelongsTo<Pengguna, $this>
     */
    public function pembuat(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'dibuat_oleh', 'pengguna_id');
    }

    /**
     * Sumber data atribut kawasan. Terpisah dari sumber geometri pada
     * batas_kawasan dan tidak disinkronkan otomatis.
     *
     * @return BelongsTo<SumberData, $this>
     */
    public function sumber(): BelongsTo
    {
        return $this->belongsTo(SumberData::class, 'sumber_id', 'sumber_id');
    }

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('is_aktif', true);
    }
}
