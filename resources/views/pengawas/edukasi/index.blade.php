@extends('layouts.app')

@section('title', 'Kelola Materi Edukasi — RimbaWatch')
@section('breadcrumb', 'Materi Edukasi')

@section('content')
    @php
        $adaFilter = filled(request('cari')) || filled(request('kategori')) || filled(request('status_publikasi'));
    @endphp

    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-rimba-600">PBI-16 · Pengawas</p>
            <h1 class="mt-2 text-3xl font-bold tracking-tight text-rimba-900">Materi Edukasi</h1>
            <p class="mt-2 max-w-2xl text-sm text-ink-500">
                Kelola, terbitkan, dan arsipkan materi edukasi pelestarian serta pemulihan kawasan hijau untuk masyarakat.
            </p>
        </div>

        <div class="flex flex-wrap gap-2.5 sm:shrink-0">
            <a href="{{ route('edukasi.index') }}" class="btn-secondary">Halaman publik</a>
            <a href="{{ route('pengawas.edukasi.create') }}" class="btn-primary">
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                    <path d="M10 4v12M4 10h12" />
                </svg>
                Tambah materi
            </a>
        </div>
    </div>

    <dl class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-4">
        <x-ui.card class="p-4">
            <dt class="text-xs font-medium text-ink-400">Total materi</dt>
            <dd class="mt-1 text-2xl font-bold text-rimba-900">{{ $ringkasanStatus['total'] }}</dd>
        </x-ui.card>
        <x-ui.card class="p-4">
            <dt class="text-xs font-medium text-ink-400">Draf</dt>
            <dd class="mt-1 text-2xl font-bold text-ink-700">{{ $ringkasanStatus['draf'] }}</dd>
        </x-ui.card>
        <x-ui.card class="p-4">
            <dt class="text-xs font-medium text-ink-400">Diterbitkan</dt>
            <dd class="mt-1 text-2xl font-bold text-rimba-600">{{ $ringkasanStatus['terbit'] }}</dd>
        </x-ui.card>
        <x-ui.card class="p-4">
            <dt class="text-xs font-medium text-ink-400">Diarsipkan</dt>
            <dd class="mt-1 text-2xl font-bold text-amber-ink">{{ $ringkasanStatus['arsip'] }}</dd>
        </x-ui.card>
    </dl>

    <x-ui.card class="overflow-hidden">
        <form method="GET" action="{{ route('pengawas.edukasi.index') }}" class="flex flex-col gap-3 border-b border-line p-5 md:flex-row md:items-end">
            <div class="flex-1">
                <label for="cari" class="field-label">Cari materi</label>
                <input
                    id="cari"
                    name="cari"
                    type="search"
                    value="{{ old('cari', request('cari')) }}"
                    placeholder="Cari judul, kategori, atau isi..."
                    class="field-input"
                    @error('cari') aria-invalid="true" aria-describedby="cari-error" @enderror
                >
                <x-ui.field-error name="cari" />
            </div>

            <div class="md:w-48">
                <label for="kategori" class="field-label">Kategori</label>
                <select id="kategori" name="kategori" class="field-input"
                    @error('kategori') aria-invalid="true" aria-describedby="kategori-error" @enderror>
                    <option value="">Semua kategori</option>
                    @foreach ($daftarKategori as $kategori)
                        <option value="{{ $kategori }}" @selected(old('kategori', request('kategori')) === $kategori)>
                            {{ $kategori }}
                        </option>
                    @endforeach
                </select>
                <x-ui.field-error name="kategori" />
            </div>

            <div class="md:w-44">
                <label for="status_publikasi" class="field-label">Status</label>
                <select id="status_publikasi" name="status_publikasi" class="field-input"
                    @error('status_publikasi') aria-invalid="true" aria-describedby="status_publikasi-error" @enderror>
                    <option value="">Semua status</option>
                    @foreach (\App\Enums\StatusPublikasi::pilihan() as $nilai => $label)
                        <option value="{{ $nilai }}" @selected(old('status_publikasi', request('status_publikasi')) === $nilai)>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
                <x-ui.field-error name="status_publikasi" />
            </div>

            <div class="flex gap-2">
                <button type="submit" class="btn-secondary">Terapkan</button>

                @if ($adaFilter)
                    <a href="{{ route('pengawas.edukasi.index') }}" class="btn-secondary">Reset</a>
                @endif
            </div>
        </form>

        @if ($daftarMateri->isEmpty())
            <div class="px-5 py-14">
                <div class="mx-auto flex max-w-sm flex-col items-center text-center">
                    <span class="flex h-12 w-12 items-center justify-center rounded-full bg-rimba-100 text-rimba-600">
                        @include('partials.ikon', ['nama' => 'book', 'kelas' => 'h-6 w-6'])
                    </span>

                    <p class="mt-4 text-sm font-semibold text-ink-900">Belum ada materi edukasi.</p>

                    @if ($adaFilter)
                        <p class="mt-1 text-sm text-ink-400">Tidak ada materi yang cocok dengan pencarian atau filter saat ini.</p>
                        <a href="{{ route('pengawas.edukasi.index') }}" class="btn-secondary mt-4">Reset filter</a>
                    @else
                        <p class="mt-1 text-sm text-ink-400">
                            Buat materi edukasi pertama untuk diterbitkan kepada masyarakat.
                        </p>
                        <a href="{{ route('pengawas.edukasi.create') }}" class="btn-primary mt-4">Tambah materi</a>
                    @endif
                </div>
            </div>
        @else
            {{-- Mobile & tablet: card list tanpa horizontal scroll --}}
            <ul class="divide-y divide-line lg:hidden" aria-label="Daftar materi edukasi">
                @foreach ($daftarMateri as $materi)
                    <li class="p-5">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <a href="{{ route('pengawas.edukasi.show', $materi) }}" class="font-semibold text-ink-900 hover:text-rimba-600">
                                    {{ $materi->judul }}
                                </a>
                                <p class="mt-1 text-xs text-ink-400">{{ $materi->kategori }}</p>
                            </div>

                            <div class="flex shrink-0 items-center gap-3">
                                <a href="{{ route('pengawas.edukasi.show', $materi) }}" class="text-sm font-semibold text-ink-700 hover:text-rimba-600">Detail</a>
                                <a href="{{ route('pengawas.edukasi.edit', $materi) }}" class="text-sm font-semibold text-rimba-600 hover:text-rimba-700">Ubah</a>
                            </div>
                        </div>

                        <div class="mt-3 flex flex-wrap items-center gap-2">
                            <x-ui.badge :variant="$materi->status_publikasi->variant()">
                                {{ $materi->status_publikasi->label() }}
                            </x-ui.badge>
                            <span class="text-xs text-ink-400">
                                {{ $materi->tanggalTampil()?->format('d M Y • H:i') ?? '-' }}
                            </span>
                        </div>
                    </li>
                @endforeach
            </ul>

            {{-- Desktop: tabel --}}
            <div class="hidden lg:block">
                <table class="w-full min-w-[640px] text-left text-sm">
                    <caption class="sr-only">Daftar materi edukasi</caption>
                    <thead>
                        <tr class="border-b border-line text-[11px] uppercase tracking-[0.12em] text-ink-400">
                            <th scope="col" class="px-4 py-3 font-semibold">Judul materi</th>
                            <th scope="col" class="px-4 py-3 font-semibold">Kategori</th>
                            <th scope="col" class="px-4 py-3 font-semibold">Status</th>
                            <th scope="col" class="px-4 py-3 font-semibold">Tanggal</th>
                            <th scope="col" class="px-4 py-3 text-right font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        @foreach ($daftarMateri as $materi)
                            <tr class="transition hover:bg-rimba-50/70">
                                <td class="px-4 py-4">
                                    <a href="{{ route('pengawas.edukasi.show', $materi) }}" class="font-semibold text-ink-900 hover:text-rimba-600">
                                        {{ $materi->judul }}
                                    </a>
                                    <p class="mt-0.5 line-clamp-1 text-xs text-ink-400">{{ $materi->referensi_sumber ?? '-' }}</p>
                                </td>
                                <td class="px-4 py-4 text-ink-500">{{ $materi->kategori }}</td>
                                <td class="px-4 py-4">
                                    <x-ui.badge :variant="$materi->status_publikasi->variant()">
                                        {{ $materi->status_publikasi->label() }}
                                    </x-ui.badge>
                                </td>
                                <td class="px-4 py-4 text-ink-500">
                                    {{ $materi->tanggalTampil()?->format('d M Y • H:i') ?? '-' }}
                                </td>
                                <td class="px-4 py-4 text-right">
                                    <div class="flex justify-end gap-3">
                                        <a href="{{ route('pengawas.edukasi.show', $materi) }}" class="text-sm font-semibold text-ink-700 hover:text-rimba-600">Detail</a>
                                        <a href="{{ route('pengawas.edukasi.edit', $materi) }}" class="text-sm font-semibold text-rimba-600 hover:text-rimba-700">Ubah</a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        @if ($daftarMateri->hasPages())
            <div class="border-t border-line px-5 py-4">
                {{ $daftarMateri->links() }}
            </div>
        @endif
    </x-ui.card>
@endsection
