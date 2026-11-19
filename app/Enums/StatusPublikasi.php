<?php

namespace App\Enums;

enum StatusPublikasi: string
{
    case DRAFT = 'DRAFT';
    case PUBLISHED = 'PUBLISHED';
    case ARCHIVED = 'ARCHIVED';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draf',
            self::PUBLISHED => 'Diterbitkan',
            self::ARCHIVED => 'Diarsipkan',
        };
    }

    /**
     * Varian badge yang dipakai di antarmuka modul edukasi.
     */
    public function variant(): string
    {
        return match ($this) {
            self::DRAFT => 'neutral',
            self::PUBLISHED => 'success',
            self::ARCHIVED => 'warning',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function pilihan(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $status) => [$status->value => $status->label()])
            ->all();
    }
}
