<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
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
    public const CREATED_AT = 'dibuat_pada';

    public const UPDATED_AT = 'diperbarui_pada';

    protected $table = 'wilayah';

    protected $primaryKey = 'wilayah_id';

    protected function casts(): array
    {
        return [
            'is_aktif' => 'boolean',
            'dibuat_pada' => 'datetime',
            'diperbarui_pada' => 'datetime',
        ];
    }

    public function kawasan(): HasMany
    {
        return $this->hasMany(Kawasan::class, 'wilayah_id', 'wilayah_id');
    }
}
