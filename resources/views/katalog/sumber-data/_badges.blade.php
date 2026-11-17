<div class="flex flex-wrap gap-1.5">
    <x-ui.badge :variant="$sumber->is_aktif ? 'success' : 'danger'">
        {{ $sumber->is_aktif ? 'Aktif' : 'Nonaktif' }}
    </x-ui.badge>

    <x-ui.badge :variant="$sumber->jenis_sumber === \App\Models\SumberData::JENIS_INTERNAL ? 'info' : 'neutral'">
        {{ ucfirst(strtolower($sumber->jenis_sumber)) }}
    </x-ui.badge>

    <x-ui.badge :variant="$sumber->status_kualitas?->variant() ?? 'neutral'">
        {{ $sumber->status_kualitas?->label() ?? 'Belum diketahui' }}
    </x-ui.badge>
</div>
