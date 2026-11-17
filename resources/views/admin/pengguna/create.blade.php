@extends('layouts.app')

@section('title', 'Tambah Pengguna')
@section('breadcrumb', 'Admin / Pengguna & Akses / Tambah')

@section('content')
    <div>
        <h1 class="text-xl font-semibold">Tambah Pengguna</h1>
        <p class="text-sm opacity-70">Admin menentukan kata sandi awal. Sampaikan ke pengguna secara langsung.</p>
    </div>

    <x-ui.card class="mt-4">
        <form method="POST" action="{{ route('admin.pengguna.store') }}" class="grid gap-4 sm:grid-cols-2">
            @csrf

            <div class="sm:col-span-2">
                <label for="nama_lengkap" class="field-label">Nama lengkap</label>
                <input type="text" id="nama_lengkap" name="nama_lengkap" value="{{ old('nama_lengkap') }}"
                       required maxlength="150" class="field-input">
            </div>

            <div>
                <label for="email" class="field-label">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}"
                       required maxlength="255" class="field-input">
            </div>

            <div>
                <label for="telepon" class="field-label">Telepon (opsional)</label>
                <input type="text" id="telepon" name="telepon" value="{{ old('telepon') }}"
                       maxlength="30" class="field-input">
            </div>

            <div>
                <label for="peran_id" class="field-label">Peran</label>
                <select id="peran_id" name="peran_id" required class="field-input">
                    <option value="">Pilih peran</option>
                    @foreach ($daftarPeran as $peran)
                        <option value="{{ $peran->peran_id }}" @selected((int) old('peran_id') === $peran->peran_id)>
                            {{ $peran->nama_peran }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="password" class="field-label">Kata sandi awal</label>
                <input type="password" id="password" name="password" required minlength="8"
                       autocomplete="new-password" class="field-input">
            </div>

            <div class="flex gap-2 sm:col-span-2">
                <button type="submit" class="btn-primary">Simpan</button>
                <a href="{{ route('admin.pengguna.index') }}" class="btn-secondary">Batal</a>
            </div>
        </form>
    </x-ui.card>
@endsection