<?php

namespace App\Enums;

enum StatusKualitas: string
{
    case VERIFIED = 'VERIFIED';
    case NEEDS_REVIEW = 'NEEDS_REVIEW';
    case UNKNOWN = 'UNKNOWN';

    public function label(): string
    {
        return match ($this) {
            self::VERIFIED => 'Terverifikasi',
            self::NEEDS_REVIEW => 'Perlu ditinjau',
            self::UNKNOWN => 'Belum diketahui',
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
