<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('foto_hasil_inspeksi', function (Blueprint $table) {
            $table->foreignId('hasil_id')->constrained('hasil_inspeksi', 'hasil_id');
            $table->foreignId('berkas_media_id')->constrained('berkas_media', 'berkas_media_id');
            $table->integer('urutan_tampilan')->default(true);
            $table->primary(['hasil_id', 'berkas_media_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('foto_hasil_inspeksi');
    }
};
