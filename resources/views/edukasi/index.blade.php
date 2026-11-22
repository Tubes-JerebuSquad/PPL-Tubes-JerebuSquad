@extends('layouts.app')

@section('title', 'Materi Edukasi Lingkungan — RimbaWatch')
@section('breadcrumb', 'Materi Edukasi')

@guest
    @section('pengguna_inisial', 'PB')
    @section('pengguna_nama', 'Pengunjung Publik')
    @section('pengguna_peran', 'Publik')
@endguest

@section('content')
    @php
        $adaFilter = filled(request('cari')) || filled(request('kategori'));
        $penggunaAktif = auth()->user();
    @endphp

    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-rimba-600">PBI-16 · Edukasi Publik</p>
            <h1 class="mt-2 text-3xl font-bold tracking-tight text-rimba-900">Materi Edukasi Lingkungan</h1>
            <p class="mt-2 max-w-2xl text-sm text-ink-500">
                Baca panduan pelestarian, pencegahan kerusakan, dan pemulihan kawasan hijau yang telah diterbitkan oleh Pengawas RimbaWatch.
            </p>
        </div>

        @if ($penggunaAktif instanceof \App\Models\Pengguna && $penggunaAktif->isAktif() && $penggunaAktif->isPengawas())
            <a href="{{ route('pengawas.edukasi.index') }}" class="btn-secondary shrink-0">Kelola materi</a>
        @endif
    </div>

    <x-ui.card class="mb-6 p-5">
        <form method="GET" action="{{ route('edukasi.index') }}" class="flex flex-col gap-3 md:flex-row md:items-end">
            <div class="flex-1">
                <label for="cari" class="field-label">Cari materi</label>
                <input
                    id="cari"
                    name="cari"
                    type="search"
                    value="{{ old('cari', request('cari')) }}"
                    placeholder="Cari judul, topik, atau isi materi..."
                    class="field-input"
                    @error('cari') aria-invalid="true" aria-describedby="cari-error" @enderror
                >
                <x-ui.field-error name="cari" />
            </div>

            <div class="md:w-56">
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

            <div class="flex gap-2">
                <button type="submit" class="btn-secondary">Terapkan</button>

                @if ($adaFilter)
                    <a href="{{ route('edukasi.index') }}" class="btn-secondary">Reset</a>
                @endif
            </div>
        </form>
    </x-ui.card>

    @if ($daftarMateri->isEmpty())
        <x-ui.card class="px-5 py-14">
            <div class="mx-auto flex max-w-sm flex-col items-center text-center">
                <span class="flex h-12 w-12 items-center justify-center rounded-full bg-rimba-100 text-rimba-600">
                    @include('partials.ikon', ['nama' => 'book', 'kelas' => 'h-6 w-6'])
                </span>

                <p class="mt-4 text-sm font-semibold text-ink-900">Belum ada materi edukasi yang diterbitkan.</p>

                @if ($adaFilter)
                    <p class="mt-1 text-sm text-ink-400">Tidak ada materi terbit yang cocok dengan pencarian atau kategori saat ini.</p>
                    <a href="{{ route('edukasi.index') }}" class="btn-secondary mt-4">Reset filter</a>
                @else
                    <p class="mt-1 text-sm text-ink-400">
                        Materi edukasi yang telah diterbitkan oleh Pengawas akan tampil di sini.
                    </p>
                @endif
            </div>
        </x-ui.card>
    @else
        <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($daftarMateri as $materi)
                <x-ui.card class="flex flex-col justify-between p-6 transition hover:border-rimba-600/40">
                    <div>
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <x-ui.badge variant="info">{{ $materi->kategori }}</x-ui.badge>
                            <time class="text-xs text-ink-400" datetime="{{ $materi->tanggalTampil()?->toIso8601String() }}">
                                {{ $materi->tanggalTampil()?->format('d M Y') ?? '-' }}
                            </time>
                        </div>

                        <h2 class="mt-3 text-lg font-bold leading-snug text-rimba-900">
                            <a href="{{ route('edukasi.show', $materi) }}" class="hover:text-rimba-600">
                                {{ $materi->judul }}
                            </a>
                        </h2>

                        <p class="mt-2.5 text-sm leading-relaxed text-ink-500">
                            {{ \Illuminate\Support\Str::limit($materi->konten, 160) }}
                        </p>
                    </div>

                    <div class="mt-5 border-t border-line pt-4">
                        <p class="line-clamp-1 text-xs text-ink-400">
                            Rujukan: {{ $materi->referensi_sumber ?? '-' }}
                        </p>

                        <a href="{{ route('edukasi.show', $materi) }}" class="mt-3 inline-flex items-center gap-1.5 text-sm font-semibold text-rimba-600 hover:text-rimba-700">
                            Baca materi
                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M8 5l5 5-5 5" />
                            </svg>
                        </a>
                    </div>
                </x-ui.card>
            @endforeach
        </div>

        @if ($daftarMateri->hasPages())
            <div class="mt-6">
                {{ $daftarMateri->links() }}
            </div>
        @endif
    @endif
@endsection
