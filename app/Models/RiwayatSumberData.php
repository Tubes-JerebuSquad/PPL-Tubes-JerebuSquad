<?php

namespace App\Models;

use App\Enums\AksiRiwayat;
use Database\Factories\RiwayatSumberDataFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'sumber_id',
    'pengguna_id',
    'aksi',
    'perubahan',
    'catatan',
])]
class RiwayatSumberData extends Model
{
    /** @use HasFactory<RiwayatSumberDataFactory> */
    use HasFactory;

    public const CREATED_AT = 'dibuat_pada';

    public const UPDATED_AT = null;

    protected $table = 'riwayat_sumber_data';

    protected $primaryKey = 'riwayat_id';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'aksi' => AksiRiwayat::class,
            'perubahan' => 'array',
            'dibuat_pada' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<SumberData, $this>
     */
    public function sumberData(): BelongsTo
    {
        return $this->belongsTo(SumberData::class, 'sumber_id', 'sumber_id');
    }

    /**
     * @return BelongsTo<Pengguna, $this>
     */
    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'pengguna_id', 'pengguna_id');
    }
}
