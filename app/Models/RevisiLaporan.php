<?php

namespace App\Models;

use App\Enums\KategoriLaporan;
use Database\Factories\RevisiLaporanFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable([
    'laporan_id',
    'nomor_revisi',
    'kategori',
    'deskripsi',
    'latitude',
    'longitude',
    'waktu_kejadian',
    'catatan_revisi',
])]
class RevisiLaporan extends Model
{
    /** @use HasFactory<RevisiLaporanFactory> */
    use HasFactory;

    protected $table = 'revisi_laporan';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'nomor_revisi' => 'integer',
            'latitude' => 'float',
            'longitude' => 'float',
            'waktu_kejadian' => 'datetime',
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
     * @return BelongsToMany<BerkasMedia, $this>
     */
    public function foto(): BelongsToMany
    {
        return $this->belongsToMany(
            BerkasMedia::class,
            'foto_revisi_laporan',
            'revisi_laporan_id',
            'berkas_media_id',
        )
            ->withPivot('urutan_tampilan')
            ->withTimestamps()
            ->orderBy('foto_revisi_laporan.urutan_tampilan');
    }

    public function labelKategori(): string
    {
        return KategoriLaporan::dariNilai($this->kategori)?->label()
            ?? ucwords(str_replace('_', ' ', (string) $this->kategori));
    }
}
