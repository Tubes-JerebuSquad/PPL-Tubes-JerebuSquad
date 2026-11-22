@extends('layouts.app')

@section('title', 'Detail Kawasan')
@section('breadcrumb', 'Admin / Data Kawasan / Detail')

@section('content')
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <div class="flex flex-wrap items-center gap-2">
                <h1 class="text-xl font-semibold">{{ $kawasan->nama_kawasan }}</h1>
                <x-ui.badge variant="info">{{ $kawasan->jenis_kawasan }}</x-ui.badge>
                <x-ui.badge variant="neutral">{{ $kawasan->status_kawasan }}</x-ui.badge>
                @if ($kawasan->is_aktif)
                    <x-ui.badge variant="success">Aktif</x-ui.badge>
                @else
                    <x-ui.badge variant="danger">Nonaktif</x-ui.badge>
                @endif
            </div>
            <p class="mt-1 text-sm opacity-70">Detail data kawasan RimbaWatch.</p>
        </div>

        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.kawasan.edit', $kawasan) }}" class="btn-primary">Edit</a>
            <a href="{{ route('admin.kawasan.index') }}" class="btn-secondary">Kembali ke daftar</a>
        </div>
    </div>

    <div class="mt-4 grid gap-4 lg:grid-cols-3">
        <x-ui.card class="p-5 lg:col-span-2">
            <h2 class="text-xs font-semibold uppercase tracking-[0.12em] text-ink-400">Informasi Kawasan</h2>

            <dl class="mt-4 grid gap-x-6 gap-y-4 sm:grid-cols-2">
                <div>
                    <dt class="text-xs font-medium text-ink-400">Nama kawasan</dt>
                    <dd class="mt-1 text-sm font-semibold text-ink-900">{{ $kawasan->nama_kawasan }}</dd>
                </div>

                <div>
                    <dt class="text-xs font-medium text-ink-400">Wilayah</dt>
                    <dd class="mt-1 text-sm font-semibold text-ink-900">
                        {{ $kawasan->wilayah?->nama_wilayah ?? '—' }}
                    </dd>
                </div>

                <div>
                    <dt class="text-xs font-medium text-ink-400">Jenis kawasan</dt>
                    <dd class="mt-1 text-sm text-ink-900">{{ $kawasan->jenis_kawasan }}</dd>
                </div>

                <div>
                    <dt class="text-xs font-medium text-ink-400">Status Penetapan</dt>
                    <dd class="mt-1 text-sm text-ink-900">{{ $kawasan->status_kawasan }}</dd>
                </div>

                <div>
                    <dt class="text-xs font-medium text-ink-400">Sumber data kawasan</dt>
                    <dd class="mt-1 text-sm text-ink-900">
                        @php
                            $sumberKawasan = $kawasan->sumber;
                        @endphp

                        @if ($sumberKawasan)
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="font-semibold">{{ $sumberKawasan->nama_sumber }}</span>
                                <x-ui.badge :variant="$sumberKawasan->is_aktif ? 'success' : 'danger'">
                                    {{ $sumberKawasan->is_aktif ? 'Aktif' : 'Nonaktif' }}
                                </x-ui.badge>
                            </div>

                            <dl class="mt-2 space-y-1 text-xs text-ink-500">
                                <div class="flex gap-1">
                                    <dt class="shrink-0 font-medium text-ink-400">Penyedia:</dt>
                                    <dd class="min-w-0 break-words">{{ $sumberKawasan->nama_penyedia ?: '—' }}</dd>
                                </div>
                                <div class="flex gap-1">
                                    <dt class="shrink-0 font-medium text-ink-400">Lisensi:</dt>
                                    <dd class="min-w-0 break-words">{{ $sumberKawasan->nama_lisensi ?: '—' }}</dd>
                                </div>
                                <div class="flex gap-1">
                                    <dt class="shrink-0 font-medium text-ink-400">CRS:</dt>
                                    <dd class="min-w-0 break-words">{{ $sumberKawasan->kode_crs ?: '—' }}</dd>
                                </div>
                            </dl>

                            @if (! $sumberKawasan->is_aktif)
                                <p class="mt-2 rounded-lg border border-amber-ink/20 bg-amber-soft px-3 py-2 text-xs text-amber-ink" role="status">
                                    Sumber data ini nonaktif sehingga tidak tersedia untuk pemilihan baru. Tautan pada kawasan ini tetap dipertahankan.
                                </p>
                            @endif

                            @if (\Illuminate\Support\Facades\Route::has('katalog.sumber-data.show'))
                                <a href="{{ route('katalog.sumber-data.show', $sumberKawasan) }}"
                                   class="mt-2 inline-flex text-xs font-semibold text-rimba-600 hover:text-rimba-700 hover:underline">
                                    Lihat detail sumber data
                                </a>
                            @endif
                        @elseif ($kawasan->sumber_id)
                            <p class="font-medium text-ink-700">Sumber data tidak ditemukan</p>
                            <p class="mt-1 text-xs text-ink-500">
                                Kawasan masih merujuk sumber #{{ $kawasan->sumber_id }}, tetapi datanya tidak tersedia di katalog.
                                Pilih sumber pengganti melalui menu Edit.
                            </p>
                        @else
                            <span class="text-ink-400">Belum ditautkan</span>
                        @endif
                    </dd>
                </div>

                <div>
                    <dt class="text-xs font-medium text-ink-400">Status Data Kawasan</dt>
                    <dd class="mt-1">
                        @if ($kawasan->is_aktif)
                            <x-ui.badge variant="success">Aktif</x-ui.badge>
                        @else
                            <x-ui.badge variant="danger">Nonaktif</x-ui.badge>
                        @endif
                    </dd>
                </div>

                <div class="sm:col-span-2">
                    <dt class="text-xs font-medium text-ink-400">Catatan</dt>
                    <dd class="mt-1 whitespace-pre-line text-sm text-ink-700">{{ $kawasan->catatan ?: '—' }}</dd>
                </div>
            </dl>
        </x-ui.card>

        <x-ui.card class="p-5">
            <h2 class="text-xs font-semibold uppercase tracking-[0.12em] text-ink-400">Informasi Pencatatan</h2>

            <dl class="mt-4 space-y-4">
                <div>
                    <dt class="text-xs font-medium text-ink-400">Dibuat oleh</dt>
                    <dd class="mt-1 text-sm text-ink-900">{{ $kawasan->pembuat?->nama_lengkap ?? '—' }}</dd>
                </div>

                <div>
                    <dt class="text-xs font-medium text-ink-400">Dibuat pada</dt>
                    <dd class="mt-1 text-sm text-ink-900">
                        {{ $kawasan->dibuat_pada?->translatedFormat('d M Y, H:i') ?? '—' }}
                    </dd>
                </div>

                <div>
                    <dt class="text-xs font-medium text-ink-400">Terakhir diperbarui</dt>
                    <dd class="mt-1 text-sm text-ink-900">
                        {{ $kawasan->diperbarui_pada?->translatedFormat('d M Y, H:i') ?? 'Belum pernah diperbarui' }}
                    </dd>
                </div>
            </dl>
        </x-ui.card>

        <x-ui.card class="p-5 lg:col-span-3">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <h2 class="text-xs font-semibold uppercase tracking-[0.12em] text-ink-400">Informasi Geospasial</h2>
                <a href="{{ route('admin.kawasan.batas.index', ['kawasan' => $kawasan->kawasan_id]) }}"
                   class="btn-secondary">Kelola Batas Kawasan</a>
            </div>

            @if ($batasTerbaru)
                <dl class="mt-4 grid gap-x-6 gap-y-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs font-medium text-ink-400">Nomor versi batas terbaru</dt>
                        <dd class="mt-1 text-sm text-ink-900">Versi {{ $batasTerbaru->nomor_versi }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-ink-400">Sistem koordinat</dt>
                        <dd class="mt-1 text-sm text-ink-900">WGS 84 (EPSG:4326)</dd>
                    </div>
                </dl>

                <h3 class="mt-5 text-sm font-medium text-ink-700">Bounding Box</h3>
                <dl class="mt-2 grid grid-cols-2 gap-x-6 gap-y-4 sm:grid-cols-4">
                    <div>
                        <dt class="text-xs font-medium text-ink-400">Bujur minimum</dt>
                        <dd class="mt-1 text-sm tabular-nums text-ink-900">{{ $batasTerbaru->min_lon }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-ink-400">Bujur maksimum</dt>
                        <dd class="mt-1 text-sm tabular-nums text-ink-900">{{ $batasTerbaru->max_lon }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-ink-400">Lintang minimum</dt>
                        <dd class="mt-1 text-sm tabular-nums text-ink-900">{{ $batasTerbaru->min_lat }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-ink-400">Lintang maksimum</dt>
                        <dd class="mt-1 text-sm tabular-nums text-ink-900">{{ $batasTerbaru->max_lat }}</dd>
                    </div>
                </dl>
            @else
                <p class="mt-3 text-sm text-ink-700">Belum ada poligon batas kawasan</p>
            @endif
        </x-ui.card>
    </div>
@endsection
