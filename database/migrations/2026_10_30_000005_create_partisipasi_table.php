<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partisipasi', function (Blueprint $table) {
            $table->id('partisipasi_id');
            $table->foreignId('kegiatan_id')->constrained('kegiatan_konservasi', 'kegiatan_id');
            $table->foreignId('pengguna_id')->constrained('pengguna', 'pengguna_id');
            $table->string('status_pendaftaran', 30);
            $table->timestamp('terdaftar_pada')->useCurrent();
            $table->timestamp('diperbarui_pada')->nullable();
            $table->unique(['kegiatan_id', 'pengguna_id'], 'partisipasi_index_4');
            $table->comment('Satu pengguna hanya memiliki satu catatan pendaftaran per kegiatan.');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partisipasi');
    }
};
