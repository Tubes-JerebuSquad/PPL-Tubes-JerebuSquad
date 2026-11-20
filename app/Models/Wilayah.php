<?php

namespace App\Models;

use Database\Factories\WilayahFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'wilayah_induk_id',
    'kode_wilayah',
    'nama_wilayah',
    'tingkat_wilayah',
    'is_aktif',
])]
class Wilayah extends Model
{
    /** @use HasFactory<WilayahFactory> */
    use HasFactory;

    public const CREATED_AT = 'dibuat_pada';

    public const UPDATED_AT = 'diperbarui_pada';

    protected $table = 'wilayah';

    protected $primaryKey = 'wilayah_id';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_aktif' => 'boolean',
            'dibuat_pada' => 'datetime',
            'diperbarui_pada' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Wilayah, $this>
     */
    public function induk(): BelongsTo
    {
        return $this->belongsTo(self::class, 'wilayah_induk_id', 'wilayah_id');
    }

    /**
     * @return HasMany<Kawasan, $this>
     */
    public function kawasan(): HasMany
    {
        return $this->hasMany(Kawasan::class, 'wilayah_id', 'wilayah_id');
    }

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('is_aktif', true);
    }
}
