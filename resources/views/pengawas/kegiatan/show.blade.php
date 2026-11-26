@extends('layouts.app')

@section('title', $kegiatan->nama_kegiatan)
@section('breadcrumb', 'Pengawas / Rencana Kegiatan / Detail')

@section('content')
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h1 class="text-xl font-semibold">{{ $kegiatan->nama_kegiatan }}</h1>
            <div class="mt-2">
                <x-ui.badge variant="{{ $kegiatan->status_kegiatan->variant() }}">
                    Status rencana: {{ $kegiatan->status_kegiatan->label() }}
                </x-ui.badge>
            </div>
        </div>

        <div class="flex flex-wrap gap-2">
            <a href="{{ route('pengawas.kegiatan.index') }}" class="btn-secondary">Kembali</a>
            @if ($kegiatan->dapatDiubah())
                <a href="{{ route('pengawas.kegiatan.edit', $kegiatan) }}" class="btn-primary">Edit</a>
                <form method="POST" action="{{ route('pengawas.kegiatan.batalkan', $kegiatan) }}"
                      onsubmit="return confirm('Batalkan kegiatan ini?');">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn-danger">Batalkan</button>
                </form>
            @endif
        </div>
    </div>

    <x-ui.card class="mt-4 p-5">
        <dl class="grid gap-x-6 gap-y-4 text-sm sm:grid-cols-2">
            <div>
                <dt class="text-xs font-semibold uppercase tracking-[0.08em] text-ink-400">Kawasan</dt>
                <dd class="mt-1">{{ $kegiatan->kawasan?->nama_kawasan ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-[0.08em] text-ink-400">Jenis kegiatan</dt>
                <dd class="mt-1">{{ $kegiatan->jenis_kegiatan }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-[0.08em] text-ink-400">Jadwal</dt>
                <dd class="mt-1">
                    {{ $kegiatan->tanggal_mulai->translatedFormat('d M Y') }}
                    s.d.
                    {{ $kegiatan->tanggal_selesai->translatedFormat('d M Y') }}
                </dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-[0.08em] text-ink-400">Penanggung jawab</dt>
                <dd class="mt-1">{{ $kegiatan->penanggung_jawab ?? '—' }}</dd>
            </div>
            <div class="sm:col-span-2">
                <dt class="text-xs font-semibold uppercase tracking-[0.08em] text-ink-400">Tujuan</dt>
                <dd class="mt-1 whitespace-pre-line">{{ $kegiatan->tujuan }}</dd>
            </div>
            <div class="sm:col-span-2">
                <dt class="text-xs font-semibold uppercase tracking-[0.08em] text-ink-400">Target</dt>
                <dd class="mt-1 whitespace-pre-line">{{ $kegiatan->deskripsi_target ?? '—' }}</dd>
            </div>
            <div class="sm:col-span-2">
                <dt class="text-xs font-semibold uppercase tracking-[0.08em] text-ink-400">Kebutuhan sumber daya</dt>
                <dd class="mt-1 whitespace-pre-line">{{ $kegiatan->deskripsi_sumber_daya ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-[0.08em] text-ink-400">Kuota peserta</dt>
                <dd class="mt-1">{{ $kegiatan->kuota_peserta ?? 'Tidak dibatasi' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-[0.08em] text-ink-400">Direncanakan oleh</dt>
                <dd class="mt-1">{{ $kegiatan->perencana?->nama_lengkap ?? '—' }}</dd>
            </div>
        </dl>
    </x-ui.card>
@endsection