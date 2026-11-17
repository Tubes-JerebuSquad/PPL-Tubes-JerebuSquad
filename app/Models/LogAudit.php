<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LogAudit extends Model
{
    public const CREATED_AT = 'dibuat_pada';

    public const UPDATED_AT = null;

    public const AKSI_AKUN_DIBUAT = 'AKUN_DIBUAT';

    public const AKSI_DATA_DIUBAH = 'DATA_DIUBAH';

    public const AKSI_PERAN_DIUBAH = 'PERAN_DIUBAH';

    public const AKSI_AKUN_DINONAKTIFKAN = 'AKUN_DINONAKTIFKAN';

    public const AKSI_AKUN_DIAKTIFKAN = 'AKUN_DIAKTIFKAN';

    protected $table = 'log_audit';

    protected $primaryKey = 'log_audit_id';

    protected $fillable = ['pelaku_id', 'sasaran_id', 'aksi', 'perubahan'];

    protected function casts(): array
    {
        return [
            'perubahan' => 'array',
            'dibuat_pada' => 'datetime',
        ];
    }

    public function pelaku(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'pelaku_id', 'pengguna_id');
    }

    public function sasaran(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'sasaran_id', 'pengguna_id');
    }

    public static function catat(?Pengguna $pelaku, Pengguna $sasaran, string $aksi, ?array $perubahan = null): self
    {
        return self::create([
            'pelaku_id' => $pelaku?->pengguna_id,
            'sasaran_id' => $sasaran->pengguna_id,
            'aksi' => $aksi,
            'perubahan' => $perubahan,
        ]);
    }
}
