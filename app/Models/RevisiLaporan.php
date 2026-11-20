<?php

namespace App\Models;

use App\Enums\KategoriLaporan;
use App\Support\LokasiSpasial;
use Database\Factories\RevisiLaporanFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable([
    'laporan_id',
    'nomor_revisi',
    'diedit_oleh',
    'kategori',
    'deskripsi',
    'insiden_pada',
    'alasan_revisi',
])]
class RevisiLaporan extends Model
{
    /** @use HasFactory<RevisiLaporanFactory> */
    use HasFactory;

    public const CREATED_AT = 'dibuat_pada';

    // Riwayat revisi bersifat append-only: tidak ada kolom diperbarui_pada.
    public const UPDATED_AT = null;

    protected $table = 'revisi_laporan';

    protected $primaryKey = 'revisi_id';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'nomor_revisi' => 'integer',
            'insiden_pada' => 'datetime',
            'dibuat_pada' => 'datetime',
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
    public function pengedit(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'diedit_oleh', 'pengguna_id');
    }

    /**
     * Foto/video pada revisi ini, urut sesuai urutan_tampilan.
     *
     * @return BelongsToMany<BerkasMedia, $this>
     */
    public function berkasMedia(): BelongsToMany
    {
        return $this->belongsToMany(BerkasMedia::class, 'foto_revisi_laporan', 'revisi_id', 'berkas_media_id')
            ->withPivot('urutan_tampilan')
            ->orderByPivot('urutan_tampilan');
    }

    public function labelKategori(): string
    {
        return KategoriLaporan::dariNilai($this->kategori)?->label()
            ?? ucwords(str_replace('_', ' ', (string) $this->kategori));
    }

    /**
     * @return array{latitude: float|null, longitude: float|null}
     */
    public function koordinat(): array
    {
        return LokasiSpasial::koordinatDariTabel(
            $this->table,
            $this->primaryKey,
            (int) $this->revisi_id,
            $this->getAttribute('lokasi'),
        );
    }
}
