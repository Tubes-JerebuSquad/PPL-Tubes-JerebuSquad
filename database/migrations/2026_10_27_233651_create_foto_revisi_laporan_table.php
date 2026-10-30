<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('foto_revisi_laporan', function (Blueprint $table) {
            $table->foreignId('revisi_id')->constrained('revisi_laporan', 'revisi_id');
            $table->foreignId('berkas_media_id')->constrained('berkas_media', 'berkas_media_id');
            $table->integer('urutan_tampilan')->default(true);
            $table->primary(['revisi_id', 'berkas_media_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('foto_revisi_laporan');
    }
};
