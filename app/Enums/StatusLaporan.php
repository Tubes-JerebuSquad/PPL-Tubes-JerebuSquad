<?php

namespace App\Enums;

enum StatusLaporan: string
{
    case DRAF = 'draf';
    case MENUNGGU_VERIFIKASI = 'menunggu_verifikasi';
    case PERLU_PERBAIKAN = 'perlu_perbaikan';
    case DIVERIFIKASI = 'diverifikasi';
    case DALAM_INSPEKSI = 'dalam_inspeksi';
    case DITOLAK = 'ditolak';
    case SELESAI = 'selesai';

    public static function dariNilai(?string $nilai): ?self
    {
        if ($nilai === null) {
            return null;
        }

        $normal = strtolower(trim($nilai));

        return self::tryFrom($normal);
    }

    public function label(): string
    {
        return match ($this) {
            self::DRAF => 'Draf',
            self::MENUNGGU_VERIFIKASI => 'Menunggu Verifikasi',
            self::PERLU_PERBAIKAN => 'Perlu Perbaikan',
            self::DIVERIFIKASI => 'Diverifikasi',
            self::DALAM_INSPEKSI => 'Dalam Inspeksi',
            self::DITOLAK => 'Ditolak',
            self::SELESAI => 'Selesai',
        };
    }

    public function variant(): string
    {
        return match ($this) {
            self::DRAF => 'neutral',
            self::MENUNGGU_VERIFIKASI, self::DALAM_INSPEKSI => 'info',
            self::PERLU_PERBAIKAN => 'warning',
            self::DIVERIFIKASI, self::SELESAI => 'success',
            self::DITOLAK => 'danger',
        };
    }

    /**
     * Laporan hanya boleh diperbaiki pelapor saat berstatus perlu perbaikan atau draf.
     */
    public function bolehDieditPelapor(): bool
    {
        return in_array($this, [self::PERLU_PERBAIKAN, self::DRAF], true);
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
