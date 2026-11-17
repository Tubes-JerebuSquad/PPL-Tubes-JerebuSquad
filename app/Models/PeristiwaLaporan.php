<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'laporan_id',
    'aktor_id',
    'jenis_peristiwa',
    'status_lama',
    'status_baru',
    'catatan_peristiwa',
    'terjadi_pada',
])]
class PeristiwaLaporan extends Model
{
    protected $table = 'peristiwa_laporan';

    protected $primaryKey = 'peristiwa_id';

    // Waktu kejadian disimpan di kolom terjadi_pada (default waktu sekarang di database).
    public $timestamps = false;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'terjadi_pada' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Laporan, $this>
     */
    public function laporan(): BelongsTo
    {
        return $this->belongsTo(Laporan::class, 'laporan_id', 'laporan_id');
    }

    /**
     * @return BelongsTo<Pengguna, $this>
     */
    public function aktor(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'aktor_id', 'pengguna_id');
    }
}