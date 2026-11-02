@php
    $menu = [
        [
            'label' => 'Dashboard',
            'route' => null,
            'aktif' => false,
            'ikon' => 'grid',
        ],
        [
            'label' => 'Data Kawasan',
            'route' => null,
            'aktif' => false,
            'ikon' => 'map',
        ],
        [
            'label' => 'Sumber Data',
            'route' => 'katalog.sumber-data.index',
            'aktif' => request()->routeIs('katalog.sumber-data.*'),
            'ikon' => 'database',
        ],
        [
            'label' => 'Pengguna & Akses',
            'route' => null,
            'aktif' => false,
            'ikon' => 'users',
        ],
        [
            'label' => 'Pengaturan',
            'route' => null,
            'aktif' => false,
            'ikon' => 'gear',
        ],
    ];
@endphp

<aside class="hidden w-64 shrink-0 flex-col bg-rimba-900 px-4 py-5 lg:flex">
    <div class="mb-8 flex items-center gap-3 rounded-xl bg-white px-3 py-3 shadow-sm">
        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-rimba-100 text-rimba-600">
            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M20 4C12 4 5 8 5 16c0 2 1 4 1 4s2-1 4-1c8 0 10-7 10-15Z" fill="currentColor" />
                <path d="M5 20c2-6 6-10 11-12" stroke="#fff" stroke-width="1.5" stroke-linecap="round" />
            </svg>
        </span>
        <span class="min-w-0">
            <span class="block truncate text-base font-bold tracking-tight text-rimba-900">RimbaWatch</span>
            <span class="block text-[10px] font-semibold uppercase tracking-[0.18em] text-ink-400">GIS Conservation</span>
        </span>
    </div>

    <p class="mb-2 px-3 text-[10px] font-semibold uppercase tracking-[0.18em] text-white/35">Workspace</p>

    <nav class="flex flex-1 flex-col gap-1" aria-label="Navigasi utama">
        @foreach ($menu as $item)
            @if ($item['route'])
                <a
                    href="{{ route($item['route']) }}"
                    class="nav-item {{ $item['aktif'] ? 'bg-white/10 text-white shadow-sm' : 'text-white/70 hover:bg-white/5 hover:text-white' }}"
                    @if ($item['aktif']) aria-current="page" @endif
                >
                    @include('partials.ikon', ['nama' => $item['ikon']])
                    <span>{{ $item['label'] }}</span>
                </a>
            @else
                <span
                    class="nav-item cursor-not-allowed text-white/35"
                    aria-disabled="true"
                    title="Segera hadir"
                >
                    @include('partials.ikon', ['nama' => $item['ikon']])
                    <span>{{ $item['label'] }}</span>
                </span>
            @endif
        @endforeach
    </nav>

    <div class="mt-6 border-t border-white/10 pt-4">
        <p class="px-3 text-[10px] font-semibold uppercase tracking-[0.18em] text-white/35">Prototype pelengkap</p>
        <p class="mt-1 px-3 text-[11px] text-white/45">Data sintetis · Mode demo</p>
    </div>
</aside>
