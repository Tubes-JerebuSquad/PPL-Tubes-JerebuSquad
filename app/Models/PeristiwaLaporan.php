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
    'pengguna_id',
    'jenis_peristiwa',
    'status_lama',
    'status_baru',
    'deskripsi',
    'catatan_publik',
    'waktu_peristiwa',
])]
class PeristiwaLaporan extends Model
{
    /** @use HasFactory<PeristiwaLaporanFactory> */
    use HasFactory;

    protected $table = 'peristiwa_laporan';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'waktu_peristiwa' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Laporan, $this>
     */
    public function laporan(): BelongsTo
    {
        return $this->belongsTo(Laporan::class, 'laporan_id', 'id');
    }

    /**
     * @return BelongsTo<Pengguna, $this>
     */
    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'pengguna_id', 'pengguna_id');
    }

    public function labelJenis(): string
    {
        return match (strtoupper((string) $this->jenis_peristiwa)) {
            'DIAJUKAN' => 'Laporan Diajukan',
            'PERMINTAAN_PERBAIKAN', 'DIKEMBALIKAN' => 'Diminta Perbaikan',
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
            ?? ucwords(str_replace('_', ' ', $this->status_lama));
    }

    public function labelStatusBaru(): ?string
    {
        if ($this->status_baru === null) {
            return null;
        }

        return StatusLaporan::dariNilai($this->status_baru)?->label()
            ?? ucwords(str_replace('_', ' ', $this->status_baru));
    }
}
