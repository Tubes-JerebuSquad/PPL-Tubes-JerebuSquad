<?php

namespace App\Enums;

enum StatusLaporan: string
{
    case DRAFT = 'DRAFT';
    case MENUNGGU_VERIFIKASI = 'MENUNGGU_VERIFIKASI';
    case DIKEMBALIKAN = 'DIKEMBALIKAN';
    case DIVERIFIKASI = 'DIVERIFIKASI';
    case DALAM_INSPEKSI = 'DALAM_INSPEKSI';
    case DITOLAK = 'DITOLAK';
    case SELESAI = 'SELESAI';

    public static function dariNilai(?string $nilai): ?self
    {
        if ($nilai === null) {
            return null;
        }

        $normal = strtoupper(trim($nilai));

        return match ($normal) {
            'DRAF' => self::DRAFT,
            'PERLU_PERBAIKAN' => self::DIKEMBALIKAN,
            default => self::tryFrom($normal),
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draf',
            self::MENUNGGU_VERIFIKASI => 'Menunggu Verifikasi',
            self::DIKEMBALIKAN => 'Dikembalikan (Perlu Perbaikan)',
            self::DIVERIFIKASI => 'Diverifikasi',
            self::DALAM_INSPEKSI => 'Dalam Inspeksi',
            self::DITOLAK => 'Ditolak',
            self::SELESAI => 'Selesai',
        };
    }

    public function variant(): string
    {
        return match ($this) {
            self::DRAFT => 'neutral',
            self::MENUNGGU_VERIFIKASI, self::DALAM_INSPEKSI => 'info',
            self::DIKEMBALIKAN => 'warning',
            self::DIVERIFIKASI, self::SELESAI => 'success',
            self::DITOLAK => 'danger',
        };
    }

    /**
     * Laporan hanya boleh diperbaiki pelapor saat berstatus dikembalikan atau draf.
     */
    public function bolehDieditPelapor(): bool
    {
        return in_array($this, [self::DIKEMBALIKAN, self::DRAFT], true);
    }

    public function terkunci(): bool
    {
        return ! $this->bolehDieditPelapor();
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
