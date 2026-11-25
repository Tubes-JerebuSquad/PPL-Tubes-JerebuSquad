@extends('layouts.app')

@section('title', 'Rencana Kegiatan')
@section('breadcrumb', 'Pengawas / Rencana Kegiatan')

@section('content')
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h1 class="text-xl font-semibold">Rencana Kegiatan</h1>
            <p class="text-sm opacity-70">Rencana kegiatan konservasi dan rehabilitasi kawasan.</p>
        </div>

        <a href="{{ route('pengawas.kegiatan.create') }}" class="btn-primary">Rencana Baru</a>
    </div>

    <x-ui.card class="mt-4">
        <form method="GET" action="{{ route('pengawas.kegiatan.index') }}" class="flex flex-wrap items-end gap-3 p-4">
            <div>
                <label for="status" class="mb-1.5 block text-sm font-medium text-ink-700">Status</label>
                <select id="status" name="status"
                        class="field-input block rounded-lg border border-line-strong bg-white px-3.5 py-2.5 text-sm text-ink-900 outline-none transition focus:border-rimba-600 focus:ring-2 focus:ring-rimba-600/20">
                    <option value="">Semua status</option>
                    @foreach (\App\Enums\StatusKegiatan::pilihan() as $nilai => $label)
                        <option value="{{ $nilai }}" @selected($status?->value === $nilai)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="btn-primary">Terapkan</button>
            <a href="{{ route('pengawas.kegiatan.index') }}" class="btn-secondary">Reset</a>
        </form>
    </x-ui.card>

    <x-ui.card class="mt-4 overflow-hidden">
        @if ($daftar->isEmpty())
            <p class="px-4 py-10 text-center text-sm opacity-70">Belum ada rencana kegiatan.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full min-w-[760px] text-left text-sm">
                    <caption class="sr-only">Daftar rencana kegiatan</caption>
                    <thead class="bg-rimba-50">
                        <tr class="border-b border-line">
                            <th scope="col" class="px-4 py-3 text-xs font-semibold uppercase tracking-[0.08em] text-ink-400">Kegiatan</th>
                            <th scope="col" class="px-4 py-3 text-xs font-semibold uppercase tracking-[0.08em] text-ink-400">Kawasan</th>
                            <th scope="col" class="px-4 py-3 text-xs font-semibold uppercase tracking-[0.08em] text-ink-400">Jadwal</th>
                            <th scope="col" class="px-4 py-3 text-xs font-semibold uppercase tracking-[0.08em] text-ink-400">Status</th>
                            <th scope="col" class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-[0.08em] text-ink-400">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($daftar as $kegiatan)
                            <tr class="border-b border-line transition-colors last:border-0 hover:bg-rimba-50/50">
                                <td class="px-4 py-3 align-middle">
                                    <a href="{{ route('pengawas.kegiatan.show', $kegiatan) }}"
                                       class="font-medium text-ink-900 hover:text-rimba-600">
                                        {{ $kegiatan->nama_kegiatan }}
                                    </a>
                                    <div class="mt-1 text-xs text-ink-400">{{ $kegiatan->jenis_kegiatan }}</div>
                                </td>
                                <td class="px-4 py-3 align-middle">{{ $kegiatan->kawasan?->nama_kawasan ?? '—' }}</td>
                                <td class="px-4 py-3 align-middle">
                                    {{ $kegiatan->tanggal_mulai->translatedFormat('d M Y') }}
                                    s.d.
                                    {{ $kegiatan->tanggal_selesai->translatedFormat('d M Y') }}
                                </td>
                                <td class="px-4 py-3 align-middle">
                                    <x-ui.badge variant="{{ $kegiatan->status_kegiatan->variant() }}">
                                        {{ $kegiatan->status_kegiatan->label() }}
                                    </x-ui.badge>
                                </td>
                                <td class="px-4 py-3 text-right align-middle">
                                    <div class="flex justify-end gap-2">
                                        @if ($kegiatan->dapatDiubah())
                                            <a href="{{ route('pengawas.kegiatan.edit', $kegiatan) }}"
                                               class="btn-secondary whitespace-nowrap">Edit</a>
                                        @endif
                                        <a href="{{ route('pengawas.kegiatan.show', $kegiatan) }}"
                                           class="btn-secondary whitespace-nowrap">Detail</a>
                                    </div>
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