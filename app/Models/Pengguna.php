<?php

namespace App\Models;

use Database\Factories\PenggunaFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
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
   
    use HasFactory;

    public const CREATED_AT = 'dibuat_pada';

    public const UPDATED_AT = 'diperbarui_pada';

    public const STATUS_AKTIF = 'ACTIVE';

    public const STATUS_NONAKTIF = 'INACTIVE';

    protected $table = 'pengguna';

    protected $primaryKey = 'pengguna_id';

   
    protected function casts(): array
    {
        return [
            'password_hash' => 'hashed',
            'dinonaktifkan_pada' => 'datetime',
        ];
    }

    
    public function peran(): BelongsTo
    {
        return $this->belongsTo(Peran::class, 'peran_id', 'peran_id');
    }

    
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

    
    public function scopeCari(Builder $query, ?string $kata): void
    {
        if (blank($kata)) {
            return;
        }

        $query->where(function (Builder $q) use ($kata): void {
            $q->whereLike('nama_lengkap', "%{$kata}%")
                ->orWhereLike('email', "%{$kata}%");
        });
    }

    
    public function scopeBerperan(Builder $query, ?int $peranId): void
    {
        if ($peranId) {
            $query->where('peran_id', $peranId);
        }
    }

   
    public function scopeBerstatus(Builder $query, ?string $status): void
    {
        if (filled($status)) {
            $query->where('status_akun', $status);
        }
    }

    
    public function getAuthPassword(): string
    {
        return $this->password_hash;
    }

   
    public function getRememberTokenName(): string
    {
        return '';
    }
}