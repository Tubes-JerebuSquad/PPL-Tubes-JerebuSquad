<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BatasKawasan extends Model
{
    protected $table = 'batas_kawasan';
    protected $primaryKey = 'batas_id';

    const CREATED_AT = 'dibuat_pada';
    const UPDATED_AT = null;

    protected $fillable = [
        'kawasan_id',
        'sumber_id',
        'nomor_versi',
        'data_geometri',
        'kode_crs',
        'status_validasi',
        'pesan_validasi',
        'dibuat_oleh',
    ];
}