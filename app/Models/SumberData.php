<?php

namespace App\Models;

use App\Enums\StatusKualitas;
use Database\Factories\SumberDataFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'nama_sumber',
    'jenis_sumber',
    'nama_penyedia',
    'url_sumber',
    'nama_lisensi',
    'format_data',
    'deskripsi_cakupan',
    'periode_mulai',
    'periode_selesai',
    'kode_crs',
    'interval_penyegaran_jam',
    'status_kualitas',
])]
class SumberData extends Model
{
    /** @use HasFactory<SumberDataFactory> */
    use HasFactory;

    public const CREATED_AT = 'dibuat_pada';

    public const UPDATED_AT = 'diperbarui_pada';

    public const JENIS_INTERNAL = 'INTERNAL';

    public const JENIS_EKSTERNAL = 'EKSTERNAL';

    /**
     * Field yang wajib terisi sebelum dataset boleh diaktifkan.
     *
     * @var array<int, string>
     */
    public const FIELD_WAJIB_AKTIVASI = [
        'nama_sumber',
        'nama_penyedia',
        'nama_lisensi',
        'format_data',
        'kode_crs',
        'status_kualitas',
        'url_sumber',
        'deskripsi_cakupan',
        'periode_mulai',
        'interval_penyegaran_jam',
    ];

    protected $table = 'sumber_data';

    protected $primaryKey = 'sumber_id';

    /**
     * Nilai bawaan agar instance baru langsung mencerminkan default kolom database.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'is_aktif' => false,
        'status_kebaruan' => 'UNKNOWN',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'periode_mulai' => 'date',
            'periode_selesai' => 'date',
            'terakhir_diambil_pada' => 'datetime',
            'interval_penyegaran_jam' => 'integer',
            'is_aktif' => 'boolean',
            'status_kualitas' => StatusKualitas::class,
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'sumber_id';
    }

    /**
     * @return HasMany<RiwayatSumberData, $this>
     */
    public function riwayat(): HasMany
    {
        return $this->hasMany(RiwayatSumberData::class, 'sumber_id', 'sumber_id')
            ->latest('dibuat_pada');
    }

    /**
     * @return BelongsTo<Pengguna, $this>
     */
    public function pembuat(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'dibuat_oleh', 'pengguna_id');
    }

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('is_aktif', true);
    }

    public function scopeNonaktif(Builder $query): Builder
    {
        return $query->where('is_aktif', false);
    }

    /**
     * Daftar field wajib yang masih kosong, kosong bila metadata sudah lengkap.
     *
     * @return array<int, string>
     */
    public function metadataBelumLengkap(): array
    {
        $belumLengkap = [];

        foreach (self::FIELD_WAJIB_AKTIVASI as $field) {
            $nilai = $this->getAttribute($field);

            if ($nilai === null || (is_string($nilai) && trim($nilai) === '')) {
                $belumLengkap[] = $field;
            }
        }

        if ($this->interval_penyegaran_jam !== null && $this->interval_penyegaran_jam < 1) {
            $belumLengkap[] = 'interval_penyegaran_jam';
        }

        return array_values(array_unique($belumLengkap));
    }

    public function metadataLengkap(): bool
    {
        return $this->metadataBelumLengkap() === [];
    }

    /**
     * Data sumber belum pernah diambil selama proses sinkronisasi berjalan.
     */
    public function belumPernahDiambil(): bool
    {
        return $this->terakhir_diambil_pada === null;
    }
}
