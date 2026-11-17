@extends('layouts.app')

@section('title', 'Data Kawasan')
@section('breadcrumb', 'Admin / Data Kawasan')

@section('content')
    <div>
        <h1 class="text-xl font-semibold">Data Kawasan</h1>
        <p class="text-sm opacity-70">Daftar kawasan yang terdaftar di RimbaWatch.</p>
    </div>

    <x-ui.card class="mt-4 overflow-hidden">
        @if ($daftar->isEmpty())
            <p class="px-4 py-10 text-center text-sm opacity-70">Belum ada data kawasan.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full min-w-[820px] table-fixed text-left text-sm">
                    <caption class="sr-only">Daftar kawasan</caption>
                    <colgroup>
                        <col>
                        <col class="w-[20%]">
                        <col class="w-[17%]">
                        <col class="w-[13%]">
                        <col class="w-[9%]">
                        <col class="w-28">
                    </colgroup>
                    <thead class="bg-rimba-50">
                        <tr class="border-b border-line">
                            <th scope="col" class="px-4 py-3 text-xs font-semibold uppercase tracking-[0.08em] text-ink-400">
                                Nama Kawasan
                            </th>
                            <th scope="col" class="px-4 py-3 text-xs font-semibold uppercase tracking-[0.08em] text-ink-400">
                                Wilayah
                            </th>
                            <th scope="col" class="px-4 py-3 text-xs font-semibold uppercase tracking-[0.08em] text-ink-400">
                                Jenis
                            </th>
                            <th scope="col" class="px-4 py-3 text-xs font-semibold uppercase tracking-[0.08em] text-ink-400">
                                Status
                            </th>
                            <th scope="col" class="px-4 py-3 text-xs font-semibold uppercase tracking-[0.08em] text-ink-400">
                                Aktif
                            </th>
                            <th scope="col" class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-[0.08em] text-ink-400">
                                Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($daftar as $kawasan)
                            <tr class="border-b border-line transition-colors last:border-0 hover:bg-rimba-50/50">
                                <td class="px-4 py-3 align-middle">
                                    <a href="{{ route('admin.kawasan.show', $kawasan) }}"
                                       class="font-medium text-ink-900 hover:text-rimba-600">
                                        {{ $kawasan->nama_kawasan }}
                                    </a>
                                </td>
                                <td class="px-4 py-3 align-middle">
                                    {{ $kawasan->wilayah?->nama_wilayah ?? '—' }}
                                </td>
                                <td class="px-4 py-3 align-middle">
                                    <x-ui.badge variant="info">{{ $kawasan->jenis_kawasan }}</x-ui.badge>
                                </td>
                                <td class="px-4 py-3 align-middle">
                                    <x-ui.badge variant="neutral">{{ $kawasan->status_kawasan }}</x-ui.badge>
                                </td>
                                <td class="px-4 py-3 align-middle">
                                    @if ($kawasan->is_aktif)
                                        <x-ui.badge variant="success">Aktif</x-ui.badge>
                                    @else
                                        <x-ui.badge variant="neutral">Nonaktif</x-ui.badge>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right align-middle">
                                    <a href="{{ route('admin.kawasan.show', $kawasan) }}"
                                       class="btn-secondary whitespace-nowrap"
                                       aria-label="Lihat detail {{ $kawasan->nama_kawasan }}">
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="border-t border-line px-4 py-4">
                {{ $daftar->links() }}
            </div>
        @endif
    </x-ui.card>
@endsection
