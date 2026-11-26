<?php

namespace App\Models;

use App\Enums\StatusKegiatan;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'kawasan_id',
    'direncanakan_oleh',
    'nama_kegiatan',
    'tujuan',
    'jenis_kegiatan',
    'tanggal_mulai',
    'tanggal_selesai',
    'deskripsi_target',
    'penanggung_jawab',
    'deskripsi_sumber_daya',
    'kuota_peserta',
    'status_kegiatan',
])]
class KegiatanKonservasi extends Model
{
    use HasFactory;

    public const CREATED_AT = 'dibuat_pada';

    public const UPDATED_AT = 'diperbarui_pada';

    protected $table = 'kegiatan_konservasi';

    protected $primaryKey = 'kegiatan_id';

    protected function casts(): array
    {
        return [
            'tanggal_mulai' => 'date',
            'tanggal_selesai' => 'date',
            'kuota_peserta' => 'integer',
            'status_kegiatan' => StatusKegiatan::class,
            'dibuat_pada' => 'datetime',
            'diperbarui_pada' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'kegiatan_id';
    }

    public function kawasan(): BelongsTo
    {
        return $this->belongsTo(Kawasan::class, 'kawasan_id', 'kawasan_id');
    }

    public function perencana(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'direncanakan_oleh', 'pengguna_id');
    }

    public function dapatDiubah(): bool
    {
        return $this->status_kegiatan->dapatDiubah();
    }
}