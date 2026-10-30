<?php

namespace App\Models;

use Database\Factories\PenggunaFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'peran_id',
    'nama_lengkap',
    'email',
    'password_hash',
    'telepon',
    'status_akun',
])]
#[Hidden(['password_hash'])]
class Pengguna extends Model
{
    /** @use HasFactory<PenggunaFactory> */
    use HasFactory;

    public const CREATED_AT = 'dibuat_pada';

    public const UPDATED_AT = 'diperbarui_pada';

    public const STATUS_AKTIF = 'ACTIVE';

    protected $table = 'pengguna';

    protected $primaryKey = 'pengguna_id';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password_hash' => 'hashed',
            'dinonaktifkan_pada' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Peran, $this>
     */
    public function peran(): BelongsTo
    {
        return $this->belongsTo(Peran::class, 'peran_id', 'peran_id');
    }

    /**
     * @return HasMany<SumberData, $this>
     */
    public function sumberDataDibuat(): HasMany
    {
        return $this->hasMany(SumberData::class, 'dibuat_oleh', 'pengguna_id');
    }

    /**
     * @return HasMany<RiwayatSumberData, $this>
     */
    public function riwayatSumberData(): HasMany
    {
        return $this->hasMany(RiwayatSumberData::class, 'pengguna_id', 'pengguna_id');
    }

    /**
     * Satu-satunya titik penentu hak akses admin katalog sumber data.
     */
    public function isAdmin(): bool
    {
        return $this->peran?->nama_peran === 'Admin';
    }

    public function isAktif(): bool
    {
        return $this->status_akun === self::STATUS_AKTIF;
    }
}
