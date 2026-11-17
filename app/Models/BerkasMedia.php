<?php

namespace App\Models;

use Database\Factories\BerkasMediaFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'nama_asli',
    'nama_file',
    'path_file',
    'mime_type',
    'ukuran_byte',
    'diunggah_oleh',
])]
class BerkasMedia extends Model
{
    /** @use HasFactory<BerkasMediaFactory> */
    use HasFactory;

    protected $table = 'berkas_media';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'ukuran_byte' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Pengguna, $this>
     */
    public function pengunggah(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'diunggah_oleh', 'pengguna_id');
    }
}
