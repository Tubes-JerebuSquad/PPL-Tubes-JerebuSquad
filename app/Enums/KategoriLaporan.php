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
            self::DEFORESTASI => 'Deforestasi / penebangan liar',
            self::KEBAKARAN => 'Kebakaran hutan dan lahan',
            self::ALIH_FUNGSI_LAHAN => 'Alih fungsi lahan',
            self::KERUSAKAN_KAWASAN => 'Kerusakan kawasan',
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
