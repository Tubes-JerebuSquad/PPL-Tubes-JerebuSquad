<?php

namespace App\Exceptions;

use RuntimeException;

class LaporanTerkunciException extends RuntimeException
{
    public function __construct(string $pesan = 'Laporan sudah terkunci dalam tahap pemeriksaan dan tidak dapat diubah.')
    {
        parent::__construct($pesan);
    }
}
