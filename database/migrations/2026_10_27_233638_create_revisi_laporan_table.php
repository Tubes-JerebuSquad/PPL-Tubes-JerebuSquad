<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('revisi_laporan', function (Blueprint $table) {
            $table->id('revisi_id');
            $table->foreignId('laporan_id')->constrained('laporan', 'laporan_id');
            $table->integer('nomor_revisi');
            $table->foreignId('diedit_oleh')->constrained('pengguna', 'pengguna_id');
            $table->string('kategori', 50);
            $table->text('deskripsi');
            $table->geometry('lokasi', 'geometry', 4326);
            $table->timestamp('insiden_pada')->nullable();
            $table->text('alasan_revisi')->nullable();
            $table->timestamp('dibuat_pada')->useCurrent();
            $table->unique(['laporan_id', 'nomor_revisi'], 'revisi_laporan_index_2');
            $table->comment('Riwayat revisi bersifat append-only; revisi baru ditambahkan tanpa menimpa revisi sebelumnya.');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('revisi_laporan');
    }
};
