<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'RimbaWatch')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-rimba-50 font-sans text-ink-700 antialiased">
    <div class="flex min-h-screen">
        @include('partials.sidebar')

        <div class="flex min-w-0 flex-1 flex-col">
            @include('partials.header')

            <main class="flex-1 px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
                @if (session('status'))
                    <div class="mb-6 flex items-start gap-3 rounded-xl border border-rimba-600/20 bg-rimba-100 px-4 py-3 text-sm font-medium text-rimba-700" role="status">
                        <svg class="mt-0.5 h-4 w-4 shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M16.704 5.29a1 1 0 0 1 .006 1.414l-7.25 7.3a1 1 0 0 1-1.42 0l-3.25-3.27a1 1 0 1 1 1.42-1.41l2.54 2.55 6.54-6.59a1 1 0 0 1 1.414-.006Z" clip-rule="evenodd" />
                        </svg>
                        <span>{{ session('status') }}</span>
                    </div>
                @endif

                @if ($errors->any())
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
