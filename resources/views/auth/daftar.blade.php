@extends('layouts.guest')

@section('title', 'Daftar Akun | RimbaWatch')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-rimba-900">Buat akun warga</h1>
        <p class="mt-1 text-sm text-ink-500">Daftar untuk mengirim laporan dan memantau statusnya.</p>
    </div>

    <form method="POST" action="{{ url('/register') }}" class="space-y-4">
        @csrf

        <div>
            <label for="nama_lengkap" class="mb-1 block text-sm font-semibold text-ink-700">Nama lengkap</label>
            <input type="text" id="nama_lengkap" name="nama_lengkap" value="{{ old('nama_lengkap') }}"
                   required maxlength="150" autocomplete="name"
                   class="w-full rounded-lg border border-line-strong bg-white px-3 py-2.5 text-sm focus:border-rimba-600 focus:outline-none focus:ring-2 focus:ring-rimba-600/20">
        </div>

        <div>
            <label for="email" class="mb-1 block text-sm font-semibold text-ink-700">Email</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}"
                   required maxlength="255" autocomplete="email"
                   class="w-full rounded-lg border border-line-strong bg-white px-3 py-2.5 text-sm focus:border-rimba-600 focus:outline-none focus:ring-2 focus:ring-rimba-600/20">
        </div>

        <div>
            <label for="telepon" class="mb-1 block text-sm font-semibold text-ink-700">Nomor telepon (opsional)</label>
            <input type="text" id="telepon" name="telepon" value="{{ old('telepon') }}"
                   maxlength="30" autocomplete="tel" inputmode="tel"
                   class="w-full rounded-lg border border-line-strong bg-white px-3 py-2.5 text-sm focus:border-rimba-600 focus:outline-none focus:ring-2 focus:ring-rimba-600/20">
        </div>

        <div>
            <label for="password" class="mb-1 block text-sm font-semibold text-ink-700">Kata sandi</label>
            <input type="password" id="password" name="password"
                   required minlength="8" autocomplete="new-password"
                   class="w-full rounded-lg border border-line-strong bg-white px-3 py-2.5 text-sm focus:border-rimba-600 focus:outline-none focus:ring-2 focus:ring-rimba-600/20">
            <p class="mt-1 text-xs text-ink-400">Minimal 8 karakter.</p>
        </div>

        <div>
            <label for="password_confirmation" class="mb-1 block text-sm font-semibold text-ink-700">Ulangi kata sandi</label>
            <input type="password" id="password_confirmation" name="password_confirmation"
                   required minlength="8" autocomplete="new-password"
                   class="w-full rounded-lg border border-line-strong bg-white px-3 py-2.5 text-sm focus:border-rimba-600 focus:outline-none focus:ring-2 focus:ring-rimba-600/20">
        </div>

        <button type="submit"
                class="w-full rounded-lg bg-rimba-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-rimba-800">
            Daftar
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-ink-500">
        Sudah punya akun?
        <a href="{{ route('login') }}" class="font-semibold text-rimba-700 hover:underline">Masuk</a>
    </p>
@endsection