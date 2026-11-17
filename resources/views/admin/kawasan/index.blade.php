@extends('layouts.app')

@section('title', 'Data Kawasan')
@section('breadcrumb', 'Admin / Data Kawasan')

@section('content')
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h1 class="text-xl font-semibold">Data Kawasan</h1>
            <p class="text-sm opacity-70">Daftar kawasan yang terdaftar di RimbaWatch.</p>
        </div>

        <a href="{{ route('admin.kawasan.create') }}" class="btn-primary">Tambah Kawasan</a>
    </div>

    @php
        // Kelas utilitas disertakan langsung agar filter tetap rapi
        // saat aplikasi memakai fallback Tailwind CDN (Vite belum di-build).
        $kelasLabelFilter = 'mb-1.5 block text-sm font-medium text-ink-700';
        $kelasInputFilter = 'field-input block w-full rounded-lg border border-line-strong bg-white px-3.5 py-2.5 text-sm text-ink-900 outline-none transition placeholder:text-ink-400 focus:border-rimba-600 focus:ring-2 focus:ring-rimba-600/20';
    @endphp

    <x-ui.card class="mt-4">
        <form method="GET" action="{{ route('admin.kawasan.index') }}" class="grid gap-3 p-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="sm:col-span-2 lg:col-span-4">
                <label for="cari" class="{{ $kelasLabelFilter }}">Cari</label>
                <input type="text" id="cari" name="cari" value="{{ request('cari') }}"
                       placeholder="Nama kawasan atau nama wilayah" class="{{ $kelasInputFilter }}">
            </div>

            <div>
                <label for="wilayah_id" class="{{ $kelasLabelFilter }}">Wilayah</label>
                <select id="wilayah_id" name="wilayah_id" class="{{ $kelasInputFilter }}">
                    <option value="">Semua wilayah</option>
                    @foreach ($daftarWilayah as $wilayah)
                        <option value="{{ $wilayah->wilayah_id }}"
                                @selected((int) request('wilayah_id') === $wilayah->wilayah_id)>
                            {{ $wilayah->nama_wilayah }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="jenis_kawasan" class="{{ $kelasLabelFilter }}">Jenis Kawasan</label>
                <select id="jenis_kawasan" name="jenis_kawasan" class="{{ $kelasInputFilter }}">
                    <option value="">Semua jenis</option>
                    @foreach ($daftarJenis as $jenis)
                        <option value="{{ $jenis }}" @selected(request('jenis_kawasan') === $jenis)>
                            {{ $jenis }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="status_kawasan" class="{{ $kelasLabelFilter }}">Status Kawasan</label>
                <select id="status_kawasan" name="status_kawasan" class="{{ $kelasInputFilter }}">
                    <option value="">Semua status</option>
                    @foreach ($daftarStatus as $status)
                        <option value="{{ $status }}" @selected(request('status_kawasan') === $status)>
                            {{ $status }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="status_data" class="{{ $kelasLabelFilter }}">Status Data</label>
                <select id="status_data" name="status_data" class="{{ $kelasInputFilter }}">
                    <option value="">Semua data</option>
                    <option value="aktif" @selected(request('status_data') === 'aktif')>Aktif</option>
                    <option value="nonaktif" @selected(request('status_data') === 'nonaktif')>Nonaktif</option>
                </select>
            </div>

            @if ($spasialTersedia)
                <details
                    class="rounded-xl border border-line bg-rimba-50/60 px-4 py-3 sm:col-span-2 lg:col-span-4"
                    @if ($bboxAktif || $errors->hasAny(['min_lon', 'min_lat', 'max_lon', 'max_lat', 'bbox'])) open @endif
                >
                    <summary class="cursor-pointer text-sm font-medium text-ink-700">
                        Area Koordinat (Bounding Box)
                    </summary>
                    <p class="mt-1.5 text-xs text-ink-400">
                        Opsional. Urutan koordinat: bujur (longitude) lalu lintang (latitude), WGS 84 (EPSG:4326).
                        Filter memakai batas kawasan versi terbaru, sehingga kawasan tanpa geometri tidak akan muncul.
                    </p>

                    <div class="mt-3 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                        <div>
                            <label for="min_lon" class="{{ $kelasLabelFilter }}">Bujur Minimum</label>
                            <input type="number" step="any" inputmode="decimal" min="-180" max="180"
                                   id="min_lon" name="min_lon" placeholder="-180 s.d. 180"
                                   value="{{ old('min_lon', request('min_lon')) }}"
                                   class="{{ $kelasInputFilter }}">
                            @error('min_lon')
                                <p class="mt-1.5 text-xs font-medium text-danger-ink">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="min_lat" class="{{ $kelasLabelFilter }}">Lintang Minimum</label>
                            <input type="number" step="any" inputmode="decimal" min="-90" max="90"
                                   id="min_lat" name="min_lat" placeholder="-90 s.d. 90"
                                   value="{{ old('min_lat', request('min_lat')) }}"
                                   class="{{ $kelasInputFilter }}">
                            @error('min_lat')
                                <p class="mt-1.5 text-xs font-medium text-danger-ink">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="max_lon" class="{{ $kelasLabelFilter }}">Bujur Maksimum</label>
                            <input type="number" step="any" inputmode="decimal" min="-180" max="180"
                                   id="max_lon" name="max_lon" placeholder="-180 s.d. 180"
                                   value="{{ old('max_lon', request('max_lon')) }}"
                                   class="{{ $kelasInputFilter }}">
                            @error('max_lon')
                                <p class="mt-1.5 text-xs font-medium text-danger-ink">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="max_lat" class="{{ $kelasLabelFilter }}">Lintang Maksimum</label>
                            <input type="number" step="any" inputmode="decimal" min="-90" max="90"
                                   id="max_lat" name="max_lat" placeholder="-90 s.d. 90"
                                   value="{{ old('max_lat', request('max_lat')) }}"
                                   class="{{ $kelasInputFilter }}">
                            @error('max_lat')
                                <p class="mt-1.5 text-xs font-medium text-danger-ink">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    @error('bbox')
                        <p class="mt-2 text-xs font-medium text-danger-ink">{{ $message }}</p>
                    @enderror
                </details>
            @endif

            <div class="flex flex-wrap gap-2 sm:col-span-2 lg:col-span-4">
                <button type="submit" class="btn-primary">Terapkan</button>
                <a href="{{ route('admin.kawasan.index') }}" class="btn-secondary">Reset Filter</a>
            </div>
        </form>
    </x-ui.card>

    <x-ui.card class="mt-4 overflow-hidden">
        @if ($daftar->isEmpty())
            <p class="px-4 py-10 text-center text-sm opacity-70">
                {{ $pesanKosong }}
            </p>
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
                        <col class="w-44">
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
                                Status Kawasan
                            </th>
                            <th scope="col" class="px-4 py-3 text-xs font-semibold uppercase tracking-[0.08em] text-ink-400">
                                Status Data
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
                                        <x-ui.badge variant="danger">Nonaktif</x-ui.badge>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right align-middle">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('admin.kawasan.edit', $kawasan) }}"
                                           class="btn-secondary whitespace-nowrap"
                                           aria-label="Ubah {{ $kawasan->nama_kawasan }}">
                                            Edit
                                        </a>
                                        <a href="{{ route('admin.kawasan.show', $kawasan) }}"
                                           class="btn-secondary whitespace-nowrap"
                                           aria-label="Lihat detail {{ $kawasan->nama_kawasan }}">
                                            Detail
                                        </a>
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
