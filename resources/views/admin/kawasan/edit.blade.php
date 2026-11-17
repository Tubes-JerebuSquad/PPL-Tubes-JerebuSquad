@extends('layouts.app')

@section('title', 'Ubah Kawasan')
@section('breadcrumb', 'Admin / Data Kawasan / Ubah')

@section('content')
    <div>
        <h1 class="text-xl font-semibold">Ubah Kawasan</h1>
        <p class="text-sm opacity-70">Perbarui data kawasan {{ $kawasan->nama_kawasan }}.</p>
    </div>

    <x-ui.card class="mt-4 p-5">
        @include('admin.kawasan._form')
    </x-ui.card>
@endsection
