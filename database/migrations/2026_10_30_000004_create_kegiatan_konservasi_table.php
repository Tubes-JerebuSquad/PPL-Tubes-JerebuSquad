<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kegiatan_konservasi', function (Blueprint $table) {
            $table->id('kegiatan_id');
            $table->foreignId('kawasan_id')->constrained('kawasan', 'kawasan_id');
            $table->foreignId('direncanakan_oleh')->constrained('pengguna', 'pengguna_id');
            $table->string('nama_kegiatan', 200);
            $table->text('tujuan');
            $table->string('jenis_kegiatan', 100);
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->text('deskripsi_target')->nullable();
            $table->string('penanggung_jawab', 200)->nullable();
            $table->text('deskripsi_sumber_daya')->nullable();
            $table->integer('kuota_peserta')->nullable();
            $table->string('status_kegiatan', 30);
            $table->timestamp('dibuat_pada')->useCurrent();
            $table->timestamp('diperbarui_pada')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kegiatan_konservasi');
    }
};
