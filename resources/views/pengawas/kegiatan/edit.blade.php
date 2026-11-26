@extends('layouts.app')

@section('title', 'Ubah Rencana Kegiatan')
@section('breadcrumb', 'Pengawas / Rencana Kegiatan / Ubah')

@section('content')
    <div>
        <h1 class="text-xl font-semibold">Ubah Rencana Kegiatan</h1>
        <p class="text-sm opacity-70">{{ $kegiatan->nama_kegiatan }}</p>
    </div>

    <x-ui.card class="mt-4 p-5">
        @include('pengawas.kegiatan._form')
    </x-ui.card>
@endsection