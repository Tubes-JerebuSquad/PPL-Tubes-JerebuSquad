<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'RimbaWatch')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-rimba-50 font-sans text-ink-700 antialiased">
    <div class="grid min-h-screen lg:grid-cols-2">
        <aside class="relative hidden overflow-hidden bg-rimba-900 px-10 py-12 lg:flex lg:flex-col lg:justify-between">
            <a href="{{ url('/') }}" class="flex items-center gap-3">
                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-rimba-100 text-rimba-600">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M20 4C12 4 5 8 5 16c0 2 1 4 1 4s2-1 4-1c8 0 10-7 10-15Z" fill="currentColor" />
                        <path d="M5 20c2-6 6-10 11-12" stroke="#fff" stroke-width="1.5" stroke-linecap="round" />
                    </svg>
                </span>
                <span>
                    <span class="block text-lg font-bold tracking-tight text-white">RimbaWatch</span>
                    <span class="block text-[10px] font-semibold uppercase tracking-[0.18em] text-white/50">GIS Conservation</span>
                </span>
            </a>

            <div class="max-w-md">
                <p class="text-3xl font-bold leading-tight text-white">Pantau hutan,<br>jaga masa depan.</p>
                <p class="mt-4 text-sm leading-relaxed text-white/60">
                    Platform pemantauan kawasan dan sumber data lingkungan untuk mendukung keputusan konservasi yang berbasis bukti.
                </p>
            </div>

            <p class="text-xs text-white/50">Mode demo akademik · Data sintetis, perlu verifikasi.</p>
        </aside>

        <main class="flex items-center justify-center px-4 py-10 sm:px-6">
            <div class="w-full max-w-md">
                <a href="{{ url('/') }}" class="mb-8 flex items-center gap-3 lg:hidden">
                    <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-rimba-100 text-rimba-600">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M20 4C12 4 5 8 5 16c0 2 1 4 1 4s2-1 4-1c8 0 10-7 10-15Z" fill="currentColor" />
                            <path d="M5 20c2-6 6-10 11-12" stroke="#fff" stroke-width="1.5" stroke-linecap="round" />
                        </svg>
                    </span>
                    <span>
                        <span class="block text-base font-bold tracking-tight text-rimba-900">RimbaWatch</span>
                        <span class="block text-[10px] font-semibold uppercase tracking-[0.18em] text-ink-400">GIS Conservation</span>
                    </span>
                </a>

                @if (session('status'))
                    <div class="mb-6 rounded-xl border border-rimba-600/20 bg-rimba-100 px-4 py-3 text-sm font-medium text-rimba-700" role="status">
                        {{ session('status') }}
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
            </div>
        </main>
    </div>
</body>
</html>
