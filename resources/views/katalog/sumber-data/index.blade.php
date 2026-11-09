@extends('layouts.app')

@section('title', 'Sumber Data — RimbaWatch')
@section('breadcrumb', 'Sumber Data')

@section('content')
    @php
        $adaFilter = filled(request('cari')) || filled(request('jenis_sumber')) || filled(request('is_aktif'));
    @endphp

    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-rimba-600">PBI-03 · Admin</p>
            <h1 class="mt-2 text-3xl font-bold tracking-tight text-rimba-900">Sumber Data</h1>
            <p class="mt-2 max-w-2xl text-sm text-ink-500">
                Kelola metadata, keterlacakan sumber, dan status dataset yang dipakai RimbaWatch.
            </p>
        </div>

        <a href="{{ route('katalog.sumber-data.create') }}" class="btn-primary shrink-0">
            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                <path d="M10 4v12M4 10h12" />
            </svg>
            Tambah dataset
        </a>
    </div>

    <x-ui.card class="overflow-hidden">
        <form method="GET" action="{{ route('katalog.sumber-data.index') }}" class="flex flex-col gap-3 border-b border-line p-5 md:flex-row md:items-end">
            <div class="flex-1">
                <label for="cari" class="field-label">Cari nama dataset</label>
                <input
                    id="cari"
                    name="cari"
                    type="search"
                    value="{{ old('cari', request('cari')) }}"
                    placeholder="Cari nama sumber data..."
                    class="field-input"
                    @error('cari') aria-invalid="true" aria-describedby="cari-error" @enderror
                >
                <x-ui.field-error name="cari" />
            </div>

            <div class="md:w-44">
                <label for="jenis_sumber" class="field-label">Jenis</label>
                <select id="jenis_sumber" name="jenis_sumber" class="field-input"
                    @error('jenis_sumber') aria-invalid="true" aria-describedby="jenis_sumber-error" @enderror>
                    <option value="">Semua</option>
                    <option value="INTERNAL" @selected(old('jenis_sumber', request('jenis_sumber')) === 'INTERNAL')>Internal</option>
                    <option value="EKSTERNAL" @selected(old('jenis_sumber', request('jenis_sumber')) === 'EKSTERNAL')>Eksternal</option>
                </select>
                <x-ui.field-error name="jenis_sumber" />
            </div>

            <div class="md:w-44">
                <label for="is_aktif" class="field-label">Status</label>
                <select id="is_aktif" name="is_aktif" class="field-input"
                    @error('is_aktif') aria-invalid="true" aria-describedby="is_aktif-error" @enderror>
                    <option value="">Semua</option>
                    <option value="1" @selected(old('is_aktif', request('is_aktif')) === '1')>Aktif</option>
                    <option value="0" @selected(old('is_aktif', request('is_aktif')) === '0')>Nonaktif</option>
                </select>
                <x-ui.field-error name="is_aktif" />
            </div>

            <div class="flex gap-2">
                <button type="submit" class="btn-secondary">Terapkan</button>

                @if ($adaFilter)
                    <a href="{{ route('katalog.sumber-data.index') }}" class="btn-secondary">Reset</a>
                @endif
            </div>
        </form>

        @if ($sumberData->isEmpty())
            <div class="px-5 py-14">
                <div class="mx-auto flex max-w-sm flex-col items-center text-center">
                    <span class="flex h-12 w-12 items-center justify-center rounded-full bg-rimba-100 text-rimba-600">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <ellipse cx="12" cy="5.5" rx="7.5" ry="3" />
                            <path d="M4.5 5.5v6c0 1.66 3.36 3 7.5 3s7.5-1.34 7.5-3v-6" />
                            <path d="M4.5 11.5v6c0 1.66 3.36 3 7.5 3s7.5-1.34 7.5-3v-6" />
                        </svg>
                    </span>

                    <p class="mt-4 text-sm font-semibold text-ink-900">Belum ada sumber data.</p>

                    @if ($adaFilter)
                        <p class="mt-1 text-sm text-ink-400">Tidak ada dataset yang cocok dengan filter saat ini.</p>
                        <a href="{{ route('katalog.sumber-data.index') }}" class="btn-secondary mt-4">Reset filter</a>
                    @else
                        <p class="mt-1 text-sm text-ink-400">
                            Tambahkan dataset pertama untuk mulai mencatat metadata dan keterlacakan sumber.
                        </p>
                        <a href="{{ route('katalog.sumber-data.create') }}" class="btn-primary mt-4">
                            Tambah dataset
                        </a>
                    @endif
                </div>
            </div>
        @else
            {{-- Mobile & tablet: card list agar tidak perlu scroll horizontal --}}
            <ul class="divide-y divide-line lg:hidden" aria-label="Daftar sumber data">
                @foreach ($sumberData as $sumber)
                    <li class="p-5">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <a href="{{ route('katalog.sumber-data.show', $sumber) }}" class="font-semibold text-ink-900 hover:text-rimba-600">
                                    {{ $sumber->nama_sumber }}
                                </a>
                                <p class="mt-0.5 text-xs text-ink-400">{{ $sumber->nama_penyedia }}</p>
                            </div>

                            <a href="{{ route('katalog.sumber-data.edit', $sumber) }}" class="shrink-0 text-sm font-semibold text-rimba-600 hover:text-rimba-700">
                                Ubah
                            </a>
                        </div>

                        <div class="mt-3">
                            @include('katalog.sumber-data._badges', ['sumber' => $sumber])
                        </div>

                        <dl class="mt-3 grid grid-cols-2 gap-x-4 gap-y-2 text-xs">
                            <div>
                                <dt class="text-ink-400">Format</dt>
                                <dd class="mt-0.5 font-medium text-ink-700">{{ $sumber->format_data }}</dd>
                            </div>
                            <div>
                                <dt class="text-ink-400">Pembaruan terakhir</dt>
                                <dd class="mt-0.5 font-medium text-ink-700">
                                    {{ $sumber->terakhir_diambil_pada?->format('d M Y • H:i') ?? 'Belum pernah' }}
                                </dd>
                            </div>
                        </dl>
                    </li>
                @endforeach
            </ul>

            {{-- Desktop: tabel --}}
            <div class="hidden lg:block">
                <table class="w-full min-w-[640px] text-left text-sm">
                    <caption class="sr-only">Daftar sumber data</caption>
                    <thead>
                        <tr class="border-b border-line text-[11px] uppercase tracking-[0.12em] text-ink-400">
                            <th scope="col" class="px-4 py-3 font-semibold">Nama dataset</th>
                            <th scope="col" class="px-4 py-3 font-semibold">Format</th>
                            <th scope="col" class="px-4 py-3 font-semibold">Pembaruan terakhir</th>
                            <th scope="col" class="px-4 py-3 font-semibold">Status</th>
                            <th scope="col" class="px-4 py-3 text-right font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        @foreach ($sumberData as $sumber)
                            <tr class="transition hover:bg-rimba-50/70">
                                <td class="px-4 py-4">
                                    <a href="{{ route('katalog.sumber-data.show', $sumber) }}" class="font-semibold text-ink-900 hover:text-rimba-600">
                                        {{ $sumber->nama_sumber }}
                                    </a>
                                    <p class="mt-0.5 text-xs text-ink-400">{{ $sumber->nama_penyedia }}</p>
                                </td>
                                <td class="px-4 py-4 text-ink-500">{{ $sumber->format_data }}</td>
                                <td class="px-4 py-4 text-ink-500">
                                    {{ $sumber->terakhir_diambil_pada?->format('d M Y • H:i') ?? 'Belum pernah' }}
                                </td>
                                <td class="px-4 py-4">
                                    @include('katalog.sumber-data._badges', ['sumber' => $sumber])
                                </td>
                                <td class="px-4 py-4 text-right">
                                    <a href="{{ route('katalog.sumber-data.edit', $sumber) }}" class="text-sm font-semibold text-rimba-600 hover:text-rimba-700">
                                        Ubah
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        @if ($sumberData->hasPages())
            <div class="border-t border-line px-5 py-4">
                {{ $sumberData->links() }}
            </div>
        @endif
    </x-ui.card>
@endsection
