@extends('layouts.app')

@section('title', 'Tambah Sumber Data — RimbaWatch')
@section('breadcrumb', 'Sumber Data / Tambah')

@section('content')
    <div class="mb-8">
        <a href="{{ route('katalog.sumber-data.index') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-ink-500 transition hover:text-rimba-600">
            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M12 5 7 10l5 5" />
            </svg>
            Kembali ke katalog
        </a>

        <div class="mt-4">
            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-rimba-600">PBI-03 · Admin</p>
            <h1 class="mt-2 text-3xl font-bold tracking-tight text-rimba-900">Tambah Sumber Data</h1>
            <p class="mt-2 max-w-2xl text-sm text-ink-500">
                Lengkapi metadata dataset agar sumber dan keterlacakannya tercatat sejak awal.
            </p>
        </div>
    </div>

    <x-ui.card class="max-w-4xl p-6 sm:p-8">
        @include('katalog.sumber-data._form', [
            'aksi' => route('katalog.sumber-data.store'),
            'metode' => 'POST',
            'batal' => route('katalog.sumber-data.index'),
        ])
    </x-ui.card>
@endsection
