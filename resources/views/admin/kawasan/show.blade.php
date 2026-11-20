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
                    <dt class="text-xs font-medium text-ink-400">Status Kawasan</dt>
                    <dd class="mt-1 text-sm text-ink-900">{{ $kawasan->status_kawasan }}</dd>
                </div>

                <div>
                    <dt class="text-xs font-medium text-ink-400">Sumber data</dt>
                    <dd class="mt-1 text-sm text-ink-900">
                        {{ $kawasan->sumber_id ? 'Sumber #'.$kawasan->sumber_id : '—' }}
                    </dd>
                </div>

                <div>
                    <dt class="text-xs font-medium text-ink-400">Status Data</dt>
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
    </div>
@endsection
