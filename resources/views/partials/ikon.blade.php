@php
    $kelas = $kelas ?? 'h-5 w-5 shrink-0';
@endphp

@switch($nama)
    @case('grid')
        <svg class="{{ $kelas }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
            <rect x="3.5" y="3.5" width="6.5" height="6.5" rx="1.2" />
            <rect x="14" y="3.5" width="6.5" height="6.5" rx="1.2" />
            <rect x="3.5" y="14" width="6.5" height="6.5" rx="1.2" />
            <rect x="14" y="14" width="6.5" height="6.5" rx="1.2" />
        </svg>
        @break

    @case('map')
        <svg class="{{ $kelas }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round" aria-hidden="true">
            <path d="M9 4 3.5 6.5v13L9 17l6 2.5 5.5-2.5v-13L15 6.5 9 4Z" />
            <path d="M9 4v13M15 6.5v13" stroke-linecap="round" />
        </svg>
        @break

    @case('database')
        <svg class="{{ $kelas }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
            <ellipse cx="12" cy="5.5" rx="7.5" ry="2.8" />
            <path d="M4.5 5.5v6c0 1.55 3.36 2.8 7.5 2.8s7.5-1.25 7.5-2.8v-6" />
            <path d="M4.5 11.5v6c0 1.55 3.36 2.8 7.5 2.8s7.5-1.25 7.5-2.8v-6" />
        </svg>
        @break

    @case('users')
        <svg class="{{ $kelas }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" aria-hidden="true">
            <circle cx="9" cy="8" r="3.2" />
            <path d="M3.5 19.5c0-2.9 2.46-5.2 5.5-5.2s5.5 2.3 5.5 5.2" />
            <path d="M16 5.2a3.2 3.2 0 0 1 0 5.9M17.5 14.8c1.9.6 3.2 2.3 3.2 4.4" />
        </svg>
        @break

    @case('gear')
        <svg class="{{ $kelas }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <circle cx="12" cy="12" r="3" />
            <path d="M19.4 15a1.7 1.7 0 0 0 .34 1.87l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.7 1.7 0 0 0-1.87-.34 1.7 1.7 0 0 0-1.03 1.56V21a2 2 0 1 1-4 0v-.08a1.7 1.7 0 0 0-1.11-1.56 1.7 1.7 0 0 0-1.87.34l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06A1.7 1.7 0 0 0 4.6 15a1.7 1.7 0 0 0-1.56-1.03H3a2 2 0 1 1 0-4h.08A1.7 1.7 0 0 0 4.6 8.86a1.7 1.7 0 0 0-.34-1.87l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.7 1.7 0 0 0 1.87.34H9a1.7 1.7 0 0 0 1.03-1.56V3a2 2 0 1 1 4 0v.08a1.7 1.7 0 0 0 1.03 1.56 1.7 1.7 0 0 0 1.87-.34l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.7 1.7 0 0 0-.34 1.87V9c.2.6.76 1 1.4 1.03H21a2 2 0 1 1 0 4h-.08A1.7 1.7 0 0 0 19.4 15Z" />
        </svg>
        @break

    @default
        <svg class="{{ $kelas }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
            <circle cx="12" cy="12" r="8" />
        </svg>
@endswitch
