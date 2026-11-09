<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'RimbaWatch')</title>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <!-- Tailwind CSS CDN Fallback (saat Vite belum di-build) -->
        <script src="https://cdn.tailwindcss.com"></script>
        <script>
            tailwind.config = {
                theme: {
                    extend: {
                        colors: {
                            rimba: {
                                950: '#10291e',
                                900: '#173e2d',
                                800: '#1f4a36',
                                700: '#225a38',
                                600: '#2f7a50',
                                500: '#3f8f63',
                                100: '#e7f2e6',
                                50: '#f4f7f4',
                            },
                            ink: {
                                900: '#173e2d',
                                700: '#34463a',
                                500: '#516457',
                                400: '#6b776f',
                            },
                            line: {
                                DEFAULT: '#e1e8e2',
                                strong: '#d8e1d9',
                            },
                            danger: {
                                soft: '#fbe9e7',
                                ink: '#914b41',
                            }
                        }
                    }
                }
            }
        </script>
        <style>
            .btn-primary {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 0.5rem;
                border-radius: 0.5rem;
                background-color: #2f7a50;
                padding: 0.625rem 1rem;
                font-size: 0.875rem;
                font-weight: 600;
                color: white;
                transition: all 0.15s ease;
            }
            .btn-primary:hover {
                background-color: #225a38;
            }
            .btn-secondary {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 0.5rem;
                border-radius: 0.5rem;
                border: 1px solid #d8e1d9;
                background-color: white;
                padding: 0.625rem 1rem;
                font-size: 0.875rem;
                font-weight: 600;
                color: #34463a;
                transition: all 0.15s ease;
            }
            .btn-danger {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 0.5rem;
                border-radius: 0.5rem;
                border: 1px solid rgba(145, 75, 65, 0.3);
                background-color: white;
                padding: 0.625rem 1rem;
                font-size: 0.875rem;
                font-weight: 600;
                color: #914b41;
                transition: all 0.15s ease;
            }
            .btn-danger:hover {
                background-color: #fbe9e7;
            }
            .nav-item {
                display: flex;
                align-items: center;
                gap: 0.75rem;
                border-radius: 0.5rem;
                padding: 0.625rem 0.75rem;
                font-size: 0.875rem;
                font-weight: 500;
                transition: all 0.15s ease;
            }
        </style>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const sidebar = document.getElementById('sidebar-mobile');
                if (sidebar) {
                    const openers = document.querySelectorAll('[data-buka-sidebar]');
                    const closers = document.querySelectorAll('[data-tutup-sidebar]');
                    const setExpanded = (value) => {
                        openers.forEach((button) => button.setAttribute('aria-expanded', String(value)));
                    };
                    openers.forEach((button) => {
                        button.addEventListener('click', () => {
                            if (!sidebar.open) sidebar.showModal();
                            setExpanded(true);
                        });
                    });
                    closers.forEach((button) => button.addEventListener('click', () => sidebar.close()));
                    sidebar.addEventListener('click', (event) => {
                        if (event.target === sidebar) sidebar.close();
                    });
                    sidebar.addEventListener('close', () => setExpanded(false));
                    sidebar.querySelectorAll('a').forEach((link) => link.addEventListener('click', () => sidebar.close()));
                }
            });
        </script>
    @endif
</head>
<body class="min-h-screen bg-rimba-50 font-sans text-ink-700 antialiased">
    <a href="#konten-utama" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:rounded-lg focus:bg-white focus:px-4 focus:py-2 focus:text-sm focus:font-semibold focus:text-rimba-700 focus:shadow">
        Lewati ke konten
    </a>

    <div class="flex min-h-screen">
        <aside class="hidden w-64 shrink-0 flex-col bg-rimba-900 px-4 py-5 lg:flex">
            @include('partials.sidebar')
        </aside>

        <dialog
            id="sidebar-mobile"
            class="m-0 h-full max-h-none w-72 max-w-[85vw] bg-transparent p-0 backdrop:bg-rimba-950/50 lg:hidden"
            aria-label="Menu navigasi"
        >
            <div class="flex h-full w-full flex-col overflow-y-auto bg-rimba-900 px-4 py-5">
                @include('partials.sidebar', ['mobile' => true])
            </div>
        </dialog>

        <div class="flex min-w-0 flex-1 flex-col">
            @include('partials.header')

            <main id="konten-utama" class="flex-1 px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
                @if (session('status'))
                    <div class="mb-6 flex items-start gap-3 rounded-xl border border-rimba-600/20 bg-rimba-100 px-4 py-3 text-sm font-medium text-rimba-700" role="status">
                        <svg class="mt-0.5 h-4 w-4 shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M16.704 5.29a1 1 0 0 1 .006 1.414l-7.25 7.3a1 1 0 0 1-1.42 0l-3.25-3.27a1 1 0 1 1 1.42-1.41l2.54 2.55 6.54-6.59a1 1 0 0 1 1.414-.006Z" clip-rule="evenodd" />
                        </svg>
                        <span>{{ session('status') }}</span>
                    </div>
                @endif

                @if (isset($errors) && $errors->any())
                    <div class="mb-6 rounded-xl border border-danger-ink/20 bg-danger-soft px-4 py-3 text-sm text-danger-ink" role="alert">
                        <p class="font-semibold">Periksa kembali isian berikut:</p>
                        <ul class="mt-2 list-inside list-disc space-y-1">
                            @foreach ($errors->all() as $pesan)
                                <li>{{ $pesan }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </main>

            <footer class="border-t border-line px-6 py-4 text-xs text-ink-400 lg:px-8">
                <span class="font-semibold uppercase tracking-[0.14em] text-ink-500">Mode demo akademik</span>
                <span class="mx-2 text-line-strong">•</span>
                Data sintetis, perlu verifikasi.
            </footer>
        </div>
    </div>
</body>
</html>
