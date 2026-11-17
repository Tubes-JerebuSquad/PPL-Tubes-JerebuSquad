@extends('layouts.app')

@section('title', 'Verifikasi Laporan')
@section('breadcrumb', 'Pengawas / Verifikasi Laporan')

@section('content')
    <div>
        <h1 class="text-xl font-semibold">Verifikasi Laporan</h1>
        <p class="text-sm opacity-70">
            Tinjau laporan masyarakat. Laporan adalah informasi awal dan belum dianggap terbukti sebelum diperiksa.
        </p>
    </div>

    <x-ui.card class="mt-4 p-4">
        <form method="GET" action="{{ route('pengawas.laporan.index') }}" class="grid gap-3 sm:grid-cols-[1fr_14rem_auto]">
            <div>
                <label for="q" class="field-label">Cari</label>
                <input type="search" id="q" name="q" value="{{ $kataKunci }}" class="field-input"
                       placeholder="Kode laporan atau deskripsi">
            </div>
            <div>
                <label for="status" class="field-label">Status</label>
                <select id="status" name="status" class="field-input">
                    <option value="semua" @selected($status === 'semua')>Semua status</option>
                    @foreach ($daftarStatus as $nilai => $label)
                        <option value="{{ $nilai }}" @selected(strtoupper((string) $status) === $nilai)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="btn-primary w-full sm:w-auto">Terapkan</button>
            </div>
        </form>
    </x-ui.card>

    <x-ui.card class="mt-4 overflow-hidden">
        @if ($daftar->isEmpty())
            <p class="px-4 py-10 text-center text-sm opacity-70">Tidak ada laporan yang sesuai dengan filter.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full min-w-[760px] text-left text-sm">
                    <caption class="sr-only">Daftar laporan</caption>
                    <thead class="bg-rimba-50">
                        <tr class="border-b border-line">
                            @foreach (['Kode', 'Kategori', 'Wilayah', 'Dikirim', 'Status'] as $kolom)
                                <th scope="col" class="px-4 py-3 text-xs font-semibold uppercase tracking-[0.08em] text-ink-400">{{ $kolom }}</th>
                            @endforeach
                            <th scope="col" class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-[0.08em] text-ink-400">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        @foreach ($daftar as $lap)
                            <tr>
                                <td class="px-4 py-3 font-mono text-xs text-ink-500">{{ $lap->kode_laporan }}</td>
                                <td class="px-4 py-3">{{ $lap->labelKategori() }}</td>
                                <td class="px-4 py-3">{{ $lap->wilayah?->nama_wilayah ?? '-' }}</td>
                                <td class="px-4 py-3">{{ $lap->dibuat_pada?->translatedFormat('d M Y H:i') }}</td>
                                <td class="px-4 py-3"><x-ui.status-laporan :status="$lap->status_laporan" /></td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('pengawas.laporan.show', $lap) }}" class="btn-secondary">Tinjau</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="border-t border-line px-4 py-3">{{ $daftar->links() }}</div>
        @endif
    </x-ui.card>
@endsection
