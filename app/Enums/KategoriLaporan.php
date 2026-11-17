<?php

namespace App\Enums;

enum KategoriLaporan: string
{
    case DEFORESTASI = 'deforestasi';
    case KEBAKARAN = 'kebakaran';
    case ALIH_FUNGSI_LAHAN = 'alih_fungsi_lahan';
    case KERUSAKAN_KAWASAN = 'kerusakan_kawasan';

    public static function dariNilai(?string $nilai): ?self
    {
        if ($nilai === null) {
            return null;
        }

        return self::tryFrom(strtolower(trim($nilai)));
    }

    public function label(): string
    {
        return match ($this) {
            self::DEFORESTASI => 'Indikasi Deforestasi',
            self::KEBAKARAN => 'Indikasi Kebakaran Hutan/Lahan',
            self::ALIH_FUNGSI_LAHAN => 'Alih Fungsi Lahan',
            self::KERUSAKAN_KAWASAN => 'Kerusakan Kawasan',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function pilihan(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $kategori) => [$kategori->value => $kategori->label()])
            ->all();
    }
}
