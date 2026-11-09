@props(['variant' => 'neutral'])

@php
    $kelas = match ($variant) {
        'success' => 'bg-rimba-100 text-rimba-700',
        'warning' => 'bg-amber-soft text-amber-ink',
        'danger' => 'bg-danger-soft text-danger-ink',
        'info' => 'bg-info-soft text-info-ink',
        default => 'bg-rimba-50 text-ink-500 ring-1 ring-line',
    };
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-semibold '.$kelas]) }}>
    {{ $slot }}
</span>
