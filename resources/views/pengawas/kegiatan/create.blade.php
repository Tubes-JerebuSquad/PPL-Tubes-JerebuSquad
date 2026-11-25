@extends('layouts.app')

@section('title', 'Rencana Kegiatan Baru')
@section('breadcrumb', 'Pengawas / Rencana Kegiatan / Baru')

@section('content')
    <div>
        <h1 class="text-xl font-semibold">Rencana Kegiatan Baru</h1>
        <p class="text-sm opacity-70">Simpan sebagai draf dulu, atau terbitkan bila data sudah lengkap.</p>
    </div>

    <x-ui.card class="mt-4 p-5">
        @include('pengawas.kegiatan._form')
    </x-ui.card>
@endsection