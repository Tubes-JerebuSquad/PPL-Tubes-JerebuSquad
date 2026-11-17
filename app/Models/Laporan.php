<?php

namespace App\Models;

use App\Enums\KategoriLaporan;
use App\Enums\StatusLaporan;
use App\Support\LokasiSpasial;
use Database\Factories\LaporanFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'kode_laporan',
    'pelapor_id',
    'wilayah_id',
    'kawasan_id',
    'kategori',
    'deskripsi',
    'insiden_pada',
    'status_laporan',
    'diserahkan_pada',
])]
class Laporan extends Model
{
    /** @use HasFactory<LaporanFactory> */
    use HasFactory;

    public const CREATED_AT = 'dibuat_pada';

    public const UPDATED_AT = 'diperbarui_pada';

    public const STATUS_DRAFT = 'DRAFT';

    public const STATUS_MENUNGGU_VERIFIKASI = 'MENUNGGU_VERIFIKASI';

    public const STATUS_DIKEMBALIKAN = 'DIKEMBALIKAN';

    public const STATUS_DIVERIFIKASI = 'DIVERIFIKASI';

    public const STATUS_DITOLAK = 'DITOLAK';

    public const STATUS_DALAM_INSPEKSI = 'DALAM_INSPEKSI';

    public const STATUS_SELESAI = 'SELESAI';

    protected $table = 'laporan';

    protected $primaryKey = 'laporan_id';

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status_laporan' => self::STATUS_MENUNGGU_VERIFIKASI,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'insiden_pada' => 'datetime',
            'diserahkan_pada' => 'datetime',
            'dibuat_pada' => 'datetime',
            'diperbarui_pada' => 'datetime',
        ];
    }

    /**
     * Pelapor hanya boleh mengubah laporan berstatus draf atau dikembalikan (FR-09).
     */
    public function bisaDiubahPelapor(): bool
    {
        return $this->statusEnum()?->bolehDieditPelapor() ?? false;
    }

    public function bolehDiperbaiki(): bool
    {
        return $this->bisaDiubahPelapor();
    }

    public function terkunci(): bool
    {
        return ! $this->bisaDiubahPelapor();
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
     * Riwayat revisi bersifat append-only, diurutkan dari versi terbaru ke terlama.
     *
     * @return HasMany<RevisiLaporan, $this>
     */
    public function revisi(): HasMany
    {
        return $this->hasMany(RevisiLaporan::class, 'laporan_id', 'laporan_id')
            ->orderByDesc('nomor_revisi');
    }

    /**
     * @return HasOne<RevisiLaporan, $this>
     */
    public function revisiTerbaru(): HasOne
    {
        return $this->hasOne(RevisiLaporan::class, 'laporan_id', 'laporan_id')
            ->ofMany('nomor_revisi', 'max');
    }

    /**
     * Riwayat peristiwa/perubahan status laporan dari terbaru ke terlama.
     *
     * @return HasMany<PeristiwaLaporan, $this>
     */
    public function peristiwa(): HasMany
    {
        return $this->hasMany(PeristiwaLaporan::class, 'laporan_id', 'laporan_id')
            ->orderByDesc('terjadi_pada')
            ->orderByDesc('peristiwa_id');
    }

    public function statusEnum(): ?StatusLaporan
    {
        return StatusLaporan::dariNilai($this->status_laporan);
    }

    public function labelStatus(): string
    {
        return $this->statusEnum()?->label() ?? ucwords(strtolower(str_replace('_', ' ', (string) $this->status_laporan)));
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

    /**
     * Catatan pengembalian terakhir yang aman ditampilkan kepada pelapor.
     */
    public function catatanPerbaikanTerbaru(): ?string
    {
        $peristiwa = $this->relationLoaded('peristiwa')
            ? $this->peristiwa->first(function (PeristiwaLaporan $item): bool {
                return in_array(
                    strtoupper((string) $item->jenis_peristiwa),
                    ['PERMINTAAN_PERBAIKAN', 'DIKEMBALIKAN'],
                    true,
                ) || strtoupper((string) $item->status_baru) === self::STATUS_DIKEMBALIKAN;
            })
            : $this->peristiwa()
                ->where(function ($query): void {
                    $query->whereIn('jenis_peristiwa', ['PERMINTAAN_PERBAIKAN', 'DIKEMBALIKAN'])
                        ->orWhere('status_baru', self::STATUS_DIKEMBALIKAN);
                })
                ->first();

        return $peristiwa?->catatan_peristiwa;
    }

    public function memuatMedia(BerkasMedia $media): bool
    {
        return $this->revisi()
            ->whereHas('berkasMedia', fn ($query) => $query->where('berkas_media.berkas_media_id', $media->berkas_media_id))
            ->exists();
    }

    /**
     * @return array{latitude: float|null, longitude: float|null}
     */
    public function koordinat(): array
    {
        return LokasiSpasial::koordinatDariTabel(
            $this->table,
            $this->primaryKey,
            (int) $this->laporan_id,
            $this->getAttribute('lokasi'),
        );
    }
}
