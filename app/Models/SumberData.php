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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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

    /**
     * Label yang ditampilkan ke pengguna untuk setiap field metadata.
     *
     * @var array<string, string>
     */
    public const LABEL_FIELD = [
        'nama_sumber' => 'Nama sumber',
        'jenis_sumber' => 'Jenis sumber',
        'nama_penyedia' => 'Nama penyedia',
        'nama_lisensi' => 'Nama lisensi',
        'format_data' => 'Format data',
        'kode_crs' => 'Kode CRS',
        'status_kualitas' => 'Status kualitas',
        'url_sumber' => 'URL sumber',
        'deskripsi_cakupan' => 'Deskripsi cakupan',
        'periode_mulai' => 'Periode mulai',
        'periode_selesai' => 'Periode selesai',
        'interval_penyegaran_jam' => 'Interval penyegaran',
        'is_aktif' => 'Status aktif',
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
            ->latest('dibuat_pada')
            ->orderByDesc('riwayat_id');
    }

    /**
     * @return BelongsTo<Pengguna, $this>
     */
    public function pembuat(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'dibuat_oleh', 'pengguna_id');
    }

    /**
     * @return HasMany<Kawasan, $this>
     */
    public function kawasan(): HasMany
    {
        return $this->hasMany(Kawasan::class, 'sumber_id', 'sumber_id')
            ->orderBy('kawasan.nama_kawasan');
    }

    /**
     * @return HasMany<BatasKawasan, $this>
     */
    public function batasKawasan(): HasMany
    {
        return $this->hasMany(BatasKawasan::class, 'sumber_id', 'sumber_id')
            ->orderByDesc('batas_kawasan.dibuat_pada');
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
     * Sumber data aktif untuk pilihan dropdown modul lain, tetap menyertakan
     * sumber yang sedang terpakai meski statusnya sudah dinonaktifkan.
     */
    public function scopeTersediaUntukPilihan(Builder $query, ?int $termasukSumberId = null): Builder
    {
        return $query
            ->where(function (Builder $sub) use ($termasukSumberId): void {
                $sub->where('is_aktif', true)
                    ->when(
                        $termasukSumberId !== null,
                        fn (Builder $q) => $q->orWhere('sumber_id', $termasukSumberId),
                    );
            })
            ->orderBy('nama_sumber');
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
            if ($field === 'url_sumber' && $this->jenis_sumber === self::JENIS_INTERNAL) {
                continue;
            }

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
     * Label field wajib yang masih kosong, siap ditampilkan di halaman detail.
     *
     * @return array<int, string>
     */
    public function labelMetadataBelumLengkap(): array
    {
        return array_map(
            fn (string $field): string => self::LABEL_FIELD[$field] ?? $field,
            $this->metadataBelumLengkap(),
        );
    }

    /**
     * Data sumber belum pernah diambil selama proses sinkronisasi berjalan.
     */
    public function belumPernahDiambil(): bool
    {
        return $this->terakhir_diambil_pada === null;
    }

    /**
     * Ringkasan atribusi sumber data untuk ditampilkan pada modul kawasan, batas, atau peta.
     */
    public function ringkasanAtribusi(): string
    {
        return sprintf(
            '%s — %s (%s · %s)',
            $this->nama_sumber,
            $this->nama_penyedia,
            $this->nama_lisensi,
            $this->kode_crs,
        );
    }

    /**
     * Hitung jumlah rekaman modul lain yang merujuk ke sumber data ini.
     *
     * @return array{kawasan: int, batas_kawasan: int, titik_panas: int, log_sinkronisasi: int}
     */
    public function ringkasanPenggunaan(): array
    {
        $hasil = [];

        foreach (['kawasan', 'batas_kawasan', 'titik_panas', 'log_sinkronisasi'] as $tabel) {
            $hasil[$tabel] = Schema::hasTable($tabel)
                ? DB::table($tabel)->where('sumber_id', $this->sumber_id)->count()
                : 0;
        }

        return $hasil;
    }
}
