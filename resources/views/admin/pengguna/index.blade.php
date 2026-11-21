@extends('layouts.app')

@section('title', 'Pengguna & Akses')
@section('breadcrumb', 'Admin / Pengguna & Akses')

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-xl font-semibold">Pengguna & Akses</h1>
            <p class="text-sm opacity-70">Kelola akun pengguna dan peran mereka.</p>
         <div class="flex gap-2">
            <a href="{{ route('admin.audit.index') }}" class="btn-secondary">Riwayat Perubahan</a>
            <a href="{{ route('admin.pengguna.create') }}" class="btn-primary">Tambah Pengguna</a>
        </div>

    <x-ui.card class="mt-4">
        <form method="GET" action="{{ route('admin.pengguna.index') }}" class="grid gap-3 sm:grid-cols-4">
            <div class="sm:col-span-2">
                <label for="cari" class="field-label">Cari</label>
                <input type="text" id="cari" name="cari" value="{{ request('cari') }}"
                       placeholder="Nama atau email" class="field-input">
            </div>
            <div>
                <label for="peran_id" class="field-label">Peran</label>
                <select id="peran_id" name="peran_id" class="field-input">
                    <option value="">Semua peran</option>
                    @foreach ($daftarPeran as $peran)
                        <option value="{{ $peran->peran_id }}" @selected((int) request('peran_id') === $peran->peran_id)>
                            {{ $peran->nama_peran }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="status" class="field-label">Status</label>
                <select id="status" name="status" class="field-input">
                    <option value="">Semua status</option>
                    <option value="ACTIVE" @selected(request('status') === 'ACTIVE')>Aktif</option>
                    <option value="INACTIVE" @selected(request('status') === 'INACTIVE')>Nonaktif</option>
                </select>
            </div>
            <div class="flex gap-2 sm:col-span-4">
                <button type="submit" class="btn-primary">Terapkan</button>
                <a href="{{ route('admin.pengguna.index') }}" class="btn-secondary">Atur ulang</a>
            </div>
        </form>
    </x-ui.card>

    <x-ui.card class="mt-4">
        @if ($daftar->isEmpty())
            <p class="py-6 text-center text-sm opacity-70">Tidak ada pengguna yang cocok.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b">
                            <th class="py-2 pr-4">Nama</th>
                            <th class="py-2 pr-4">Email</th>
                            <th class="py-2 pr-4">Peran</th>
                            <th class="py-2 pr-4">Status</th>
                            <th class="py-2 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($daftar as $item)
                            <tr class="border-b last:border-0">
                                <td class="py-3 pr-4">{{ $item->nama_lengkap }}</td>
                                <td class="py-3 pr-4">{{ $item->email }}</td>
                                <td class="py-3 pr-4">
                                    <x-ui.badge variant="info">{{ $item->peran?->nama_peran }}</x-ui.badge>
                                </td>
                                <td class="py-3 pr-4">
                                    @if ($item->isAktif())
                                        <x-ui.badge variant="success">Aktif</x-ui.badge>
                                    @else
                                        <x-ui.badge variant="neutral">Nonaktif</x-ui.badge>
                                    @endif
                                </td>
                                <td class="py-3">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('admin.pengguna.edit', $item) }}" class="btn-secondary">Ubah</a>
                                        @if ($item->isAktif())
                                            @unless (auth()->user()->is($item))
                                                <form method="POST" action="{{ route('admin.pengguna.nonaktifkan', $item) }}"
                                                      onsubmit="return confirm('Nonaktifkan akun ini?')">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn-danger">Nonaktifkan</button>
                                                </form>
                                            @endunless
                                        @else
                                            <form method="POST" action="{{ route('admin.pengguna.aktifkan', $item) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn-secondary">Aktifkan</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $daftar->links() }}
            </div>
        @endif
    </x-ui.card>
@endsection