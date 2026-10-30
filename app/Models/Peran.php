<?php

namespace App\Models;

use Database\Factories\PeranFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['nama_peran', 'deskripsi'])]
class Peran extends Model
{
    /** @use HasFactory<PeranFactory> */
    use HasFactory;

    protected $table = 'peran';

    protected $primaryKey = 'peran_id';

    public $timestamps = false;

    /**
     * @return HasMany<Pengguna, $this>
     */
    public function pengguna(): HasMany
    {
        return $this->hasMany(Pengguna::class, 'peran_id', 'peran_id');
    }
}
