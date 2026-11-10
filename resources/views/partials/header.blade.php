<header class="sticky top-0 z-20 border-b border-line bg-white/90 backdrop-blur">
    <div class="flex h-16 items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
        <div class="flex min-w-0 items-center gap-2 text-sm">
            <button
                type="button"
                data-buka-sidebar
                class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-line-strong text-ink-500 transition hover:bg-rimba-50 lg:hidden"
                aria-label="Buka menu"
                aria-controls="sidebar-mobile"
                aria-expanded="false"
            >
                @include('partials.ikon', ['nama' => 'menu'])
            </button>

            <span class="hidden font-semibold text-rimba-900 sm:inline">RimbaWatch</span>
            <span class="hidden text-ink-400 sm:inline">/</span>
            <span class="truncate text-ink-500">@yield('breadcrumb', 'Beranda')</span>
        </div>

        <div class="flex shrink-0 items-center gap-3">
            <span class="inline-flex items-center gap-2 rounded-full bg-rimba-100 px-3 py-1.5 text-xs font-semibold text-rimba-700">
                <span class="h-1.5 w-1.5 rounded-full bg-rimba-600" aria-hidden="true"></span>
                Sistem demo
            </span>

            @php
                $authUser = auth()->user();
                $inisialAuth = $authUser ? strtoupper(substr($authUser->nama_lengkap, 0, 2)) : null;
            @endphp
            <div class="flex items-center gap-2.5">
                <span class="flex h-9 w-9 items-center justify-center rounded-full bg-rimba-900 text-xs font-bold text-white">
                    @yield('pengguna_inisial', $inisialAuth ?? 'RM')
                </span>
                <span class="hidden leading-tight sm:block">
                    <span class="block text-xs font-semibold text-ink-900">@yield('pengguna_nama', $authUser?->nama_lengkap ?? 'Admin RimbaWatch')</span>
                    <span class="block text-[11px] text-ink-400">@yield('pengguna_peran', $authUser?->peran?->nama_peran ?? 'Admin')</span>
                </span>
            </div>
        </div>
    </div>
</header>
