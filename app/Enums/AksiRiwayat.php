<?php

namespace App\Enums;

enum AksiRiwayat: string
{
    case DIBUAT = 'DIBUAT';
    case DIPERBARUI = 'DIPERBARUI';
    case DIAKTIFKAN = 'DIAKTIFKAN';
    case DINONAKTIFKAN = 'DINONAKTIFKAN';

    public function label(): string
    {
        return match ($this) {
            self::DIBUAT => 'Dibuat',
            self::DIPERBARUI => 'Diperbarui',
            self::DIAKTIFKAN => 'Diaktifkan',
            self::DINONAKTIFKAN => 'Dinonaktifkan',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function pilihan(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $aksi) => [$aksi->value => $aksi->label()])
            ->all();
    }
}
