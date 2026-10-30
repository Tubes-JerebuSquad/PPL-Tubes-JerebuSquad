<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('berkas_media', function (Blueprint $table) {
            $table->id('berkas_media_id');
            $table->foreignId('diunggah_oleh')->constrained('pengguna', 'pengguna_id');
            $table->string('kunci_penyimpanan', 500)->unique();
            $table->string('nama_file_asli', 255);
            $table->string('tipe_mime', 100);
            $table->bigInteger('ukuran_file_byte');
            $table->string('checksum', 128)->nullable();
            $table->string('tingkat_akses', 30)->default('PRIVATE');
            $table->timestamp('dibuat_pada')->useCurrent();
            $table->comment('Berkas disimpan pada private object storage atau disk lokal untuk pengembangan. Basis data menyimpan metadata dan referensi berkas.');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('berkas_media');
    }
};
