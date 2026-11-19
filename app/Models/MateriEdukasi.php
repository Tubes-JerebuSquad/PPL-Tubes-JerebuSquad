<?php

namespace App\Models;

use App\Enums\StatusPublikasi;
use Database\Factories\MateriEdukasiFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

#[Fillable([
    'judul',
    'konten',
    'kategori',
    'referensi_sumber',
])]
class MateriEdukasi extends Model
{
    /** @use HasFactory<MateriEdukasiFactory> */
    use HasFactory;

    public const CREATED_AT = 'dibuat_pada';

    public const UPDATED_AT = 'diperbarui_pada';

    protected $table = 'materi_edukasi';

    protected $primaryKey = 'materi_id';

    /**
     * Nilai bawaan mengikuti default kolom status_publikasi di database.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status_publikasi' => StatusPublikasi::DRAFT->value,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status_publikasi' => StatusPublikasi::class,
            'dipublikasikan_pada' => 'datetime',
            'diarsipkan_pada' => 'datetime',
            'dibuat_pada' => 'datetime',
            'diperbarui_pada' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'materi_id';
    }

    /**
     * @return BelongsTo<Pengguna, $this>
     */
    public function pembuat(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'dibuat_oleh', 'pengguna_id');
    }

    /**
     * @return BelongsTo<Pengguna, $this>
     */
    public function pembaru(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'diperbarui_oleh', 'pengguna_id');
    }

    public function scopeTerbit(Builder $query): Builder
    {
        return $query->where('status_publikasi', StatusPublikasi::PUBLISHED->value);
    }

    public function scopeDraf(Builder $query): Builder
    {
        return $query->where('status_publikasi', StatusPublikasi::DRAFT->value);
    }

    public function scopeArsip(Builder $query): Builder
    {
        return $query->where('status_publikasi', StatusPublikasi::ARCHIVED->value);
    }

    public function scopeCari(Builder $query, ?string $kata): Builder
    {
        if (blank($kata)) {
            return $query;
        }

        $pola = '%'.trim($kata).'%';

        return $query->where(function (Builder $sub) use ($pola): void {
            $sub->whereLike('judul', $pola)
                ->orWhereLike('kategori', $pola)
                ->orWhereLike('konten', $pola);
        });
    }

    public function sudahTerbit(): bool
    {
        return $this->status_publikasi === StatusPublikasi::PUBLISHED;
    }

    public function masihDraf(): bool
    {
        return $this->status_publikasi === StatusPublikasi::DRAFT;
    }

    public function sudahDiarsipkan(): bool
    {
        return $this->status_publikasi === StatusPublikasi::ARCHIVED;
    }

    /**
     * Tanggal utama untuk tampilan publik/daftar: waktu publikasi bila sudah terbit, selain itu waktu dibuat.
     */
    public function tanggalTampil(): ?Carbon
    {
        return $this->dipublikasikan_pada ?? $this->dibuat_pada;
    }
}
