@extends('layouts.app')

@section('title', 'Tambah Kawasan')
@section('breadcrumb', 'Admin / Data Kawasan / Tambah')

@section('content')
    <div>
        <h1 class="text-xl font-semibold">Tambah Kawasan</h1>
        <p class="text-sm opacity-70">Lengkapi data kawasan yang akan didaftarkan di RimbaWatch.</p>
    </div>

    <x-ui.card class="mt-4 p-5">
        @include('admin.kawasan._form')
    </x-ui.card>
@endsection
