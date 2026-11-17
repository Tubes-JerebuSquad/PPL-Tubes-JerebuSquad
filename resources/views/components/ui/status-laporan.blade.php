@props(['status'])

@php
    use App\Models\Laporan;
    $varian = match (strtoupper((string) $status)) {
        Laporan::STATUS_MENUNGGU_VERIFIKASI => 'info',
        Laporan::STATUS_DIKEMBALIKAN        => 'warning',
        Laporan::STATUS_DIVERIFIKASI        => 'success',
        Laporan::STATUS_DITOLAK             => 'danger',
        default                             => 'neutral',
    };
    $label = Laporan::LABEL_STATUS[strtoupper((string) $status)]
        ?? ucfirst(strtolower(str_replace('_', ' ', (string) $status)));
@endphp

<x-ui.badge :variant="$varian">{{ $label }}</x-ui.badge>
