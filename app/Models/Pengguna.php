<?php

namespace App\Models;

use Database\Factories\PenggunaFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;

#[Fillable([
    'peran_id',
    'nama_lengkap',
    'email',
    'password_hash',
    'telepon',
    'status_akun',
])]
#[Hidden(['password_hash'])]
class Pengguna extends Authenticatable
{
    /** @use HasFactory<PenggunaFactory> */
    use HasFactory;

    public const CREATED_AT = 'dibuat_pada';

    public const UPDATED_AT = 'diperbarui_pada';

    public const STATUS_AKTIF = 'ACTIVE';

    public const STATUS_NONAKTIF = 'INACTIVE';

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
     * Hak akses admin ditentukan oleh nama peran, bukan id yang di-hardcode.
     */
    public function isAdmin(): bool
    {
        return strcasecmp($this->peran?->nama_peran ?? '', 'Admin') === 0;
    }

    public function isPengawas(): bool
    {
        return strcasecmp($this->peran?->nama_peran ?? '', 'Pengawas') === 0;
    }

    public function isPetugasLapangan(): bool
    {
        return strcasecmp($this->peran?->nama_peran ?? '', 'Petugas Lapangan') === 0;
    }

    public function isMasyarakat(): bool
    {
        return strcasecmp($this->peran?->nama_peran ?? '', 'Masyarakat') === 0;
    }

    public function isAktif(): bool
    {
        return $this->status_akun === self::STATUS_AKTIF;
    }

    /**
     * Laravel membaca password dari kolom password_hash.
     */
    public function getAuthPassword(): string
    {
        return $this->password_hash;
    }

    /**
     * Tabel pengguna tidak punya kolom remember_token.
     */
    public function getRememberTokenName(): string
    {
        return '';
    }
}