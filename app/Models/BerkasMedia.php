<?php

namespace App\Models;

use Database\Factories\BerkasMediaFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'diunggah_oleh',
    'kunci_penyimpanan',
    'nama_file_asli',
    'tipe_mime',
    'ukuran_file_byte',
    'checksum',
    'tingkat_akses',
])]
class BerkasMedia extends Model
{
    /** @use HasFactory<BerkasMediaFactory> */
    use HasFactory;

    public const CREATED_AT = 'dibuat_pada';

    public const UPDATED_AT = null;

    public const AKSES_PRIVAT = 'PRIVATE';

    protected $table = 'berkas_media';

    protected $primaryKey = 'berkas_media_id';

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'tingkat_akses' => self::AKSES_PRIVAT,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'ukuran_file_byte' => 'integer',
            'dibuat_pada' => 'datetime',
        ];
    }

    public function isFoto(): bool
    {
        return str_starts_with((string) $this->tipe_mime, 'image/');
    }

    public function isVideo(): bool
    {
        return str_starts_with((string) $this->tipe_mime, 'video/');
    }

    /**
     * @return BelongsTo<Pengguna, $this>
     */
    public function pengunggah(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'diunggah_oleh', 'pengguna_id');
    }
}
