<?php

namespace App\Models;

use App\Enums\KategoriLaporan;
use App\Enums\StatusLaporan;
use Database\Factories\LaporanFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'nomor_laporan',
    'pelapor_id',
    'wilayah_id',
    'kawasan_id',
    'kategori',
    'deskripsi',
    'latitude',
    'longitude',
    'waktu_kejadian',
    'status',
])]
class Laporan extends Model
{
    /** @use HasFactory<LaporanFactory> */
    use HasFactory;

    protected $table = 'laporan';

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => StatusLaporan::MENUNGGU_VERIFIKASI->value,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
            'waktu_kejadian' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Pengguna, $this>
     */
    public function pelapor(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'pelapor_id', 'pengguna_id');
    }

    /**
     * @return BelongsTo<Wilayah, $this>
     */
    public function wilayah(): BelongsTo
    {
        return $this->belongsTo(Wilayah::class, 'wilayah_id', 'wilayah_id');
    }

    /**
     * @return BelongsTo<Kawasan, $this>
     */
    public function kawasan(): BelongsTo
    {
        return $this->belongsTo(Kawasan::class, 'kawasan_id', 'kawasan_id');
    }

    /**
     * Seluruh versi revisi laporan (append-only), diurutkan dari versi terbaru.
     *
     * @return HasMany<RevisiLaporan, $this>
     */
    public function revisi(): HasMany
    {
        return $this->hasMany(RevisiLaporan::class, 'laporan_id', 'id')
            ->orderByDesc('nomor_revisi');
    }

    /**
     * @return HasOne<RevisiLaporan, $this>
     */
    public function revisiTerbaru(): HasOne
    {
        return $this->hasOne(RevisiLaporan::class, 'laporan_id', 'id')
            ->ofMany('nomor_revisi', 'max');
    }

    /**
     * Riwayat peristiwa/perubahan status laporan dari terbaru ke terlama.
     *
     * @return HasMany<PeristiwaLaporan, $this>
     */
    public function peristiwa(): HasMany
    {
        return $this->hasMany(PeristiwaLaporan::class, 'laporan_id', 'id')
            ->orderByDesc('waktu_peristiwa')
            ->orderByDesc('id');
    }

    public function statusEnum(): ?StatusLaporan
    {
        return StatusLaporan::dariNilai($this->status);
    }

    public function labelStatus(): string
    {
        return $this->statusEnum()?->label() ?? ucwords(str_replace('_', ' ', (string) $this->status));
    }

    public function varianStatus(): string
    {
        return $this->statusEnum()?->variant() ?? 'neutral';
    }

    public function labelKategori(): string
    {
        return KategoriLaporan::dariNilai($this->kategori)?->label()
            ?? ucwords(str_replace('_', ' ', (string) $this->kategori));
    }

    public function milikPengguna(Pengguna $pengguna): bool
    {
        return $this->pelapor_id !== null && (int) $this->pelapor_id === (int) $pengguna->pengguna_id;
    }

    public function bolehDiperbaiki(): bool
    {
        return $this->statusEnum()?->bolehDieditPelapor() ?? false;
    }

    public function terkunci(): bool
    {
        return ! $this->bolehDiperbaiki();
    }

    /**
     * Catatan pengembalian yang boleh dilihat pelapor saat diminta perbaikan.
     */
    public function catatanPerbaikanTerbaru(): ?string
    {
        $peristiwa = $this->relationLoaded('peristiwa')
            ? $this->peristiwa->first(fn (PeristiwaLaporan $item): bool => filled($item->catatan_publik))
            : $this->peristiwa()->whereNotNull('catatan_publik')->first();

        return $peristiwa?->catatan_publik;
    }

    public function memuatMedia(BerkasMedia $media): bool
    {
        return $this->revisi()
            ->whereHas('foto', fn ($query) => $query->where('berkas_media.id', $media->id))
            ->exists();
    }
}
