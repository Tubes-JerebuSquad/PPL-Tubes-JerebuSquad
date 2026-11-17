<?php

namespace App\Models;

use App\Enums\StatusLaporan;
use Database\Factories\PeristiwaLaporanFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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
    /** @use HasFactory<PeristiwaLaporanFactory> */
    use HasFactory;

    // ── Jenis peristiwa FR-10 ────────────────────────────────────────
    public const JENIS_DITERIMA            = 'TRIASE_DITERIMA';

    public const JENIS_PERBAIKAN_DIMINTA   = 'PERBAIKAN_DIMINTA';

    public const JENIS_DITOLAK             = 'LAPORAN_DITOLAK';

    /** Tindakan triase => jenis peristiwa. */
    public const JENIS_HASIL_TINDAKAN = [
        Laporan::TINDAKAN_TERIMA           => self::JENIS_DITERIMA,
        Laporan::TINDAKAN_MINTA_PERBAIKAN  => self::JENIS_PERBAIKAN_DIMINTA,
        Laporan::TINDAKAN_TOLAK            => self::JENIS_DITOLAK,
    ];

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

    public function labelJenis(): string
    {
        return match (strtoupper((string) $this->jenis_peristiwa)) {
            'DIAJUKAN' => 'Laporan Diajukan',
            'PERMINTAAN_PERBAIKAN', 'DIKEMBALIKAN' => 'Dikembalikan untuk Perbaikan',
            'PERBAIKAN_DIKIRIM' => 'Perbaikan Dikirim Ulang',
            'DIVERIFIKASI', 'DITERIMA' => 'Laporan Diverifikasi',
            'DITOLAK' => 'Laporan Ditolak',
            'INSPEKSI_DITUGASKAN', 'DALAM_INSPEKSI' => 'Diteruskan ke Inspeksi',
            'SELESAI' => 'Penanganan Selesai',
            default => ucwords(strtolower(str_replace('_', ' ', (string) $this->jenis_peristiwa))),
        };
    }

    public function labelStatusLama(): ?string
    {
        if ($this->status_lama === null) {
            return null;
        }

        return StatusLaporan::dariNilai($this->status_lama)?->label()
            ?? ucwords(strtolower(str_replace('_', ' ', $this->status_lama)));
    }

    public function labelStatusBaru(): ?string
    {
        if ($this->status_baru === null) {
            return null;
        }

        return StatusLaporan::dariNilai($this->status_baru)?->label()
            ?? ucwords(strtolower(str_replace('_', ' ', $this->status_baru)));
    }
}
