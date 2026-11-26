<?php

namespace App\Enums;

enum StatusKegiatan: string
{
    case DRAF = 'DRAF';
    case DITERBITKAN = 'DITERBITKAN';
    case BERLANGSUNG = 'BERLANGSUNG';
    case SELESAI = 'SELESAI';
    case DIBATALKAN = 'DIBATALKAN';

    public function label(): string
    {
        return match ($this) {
            self::DRAF => 'Draf',
            self::DITERBITKAN => 'Diterbitkan',
            self::BERLANGSUNG => 'Berlangsung',
            self::SELESAI => 'Selesai',
            self::DIBATALKAN => 'Dibatalkan',
        };
    }

    public function variant(): string
    {
        return match ($this) {
            self::DRAF => 'neutral',
            self::DITERBITKAN => 'info',
            self::BERLANGSUNG => 'warning',
            self::SELESAI => 'success',
            self::DIBATALKAN => 'danger',
        };
    }

    public function dapatDiubah(): bool
    {
        return in_array($this, [self::DRAF, self::DITERBITKAN], true);
    }

    public static function pilihan(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $status) => [$status->value => $status->label()])
            ->all();
    }
}