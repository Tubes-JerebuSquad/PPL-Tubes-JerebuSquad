<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
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
}
