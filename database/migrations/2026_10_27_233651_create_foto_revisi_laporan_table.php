<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('foto_revisi_laporan', function (Blueprint $table) {
            $table->id();

            $table->foreignId('revisi_laporan_id')
                ->constrained('revisi_laporan')
                ->cascadeOnDelete();

            $table->foreignId('berkas_media_id')
                ->constrained('berkas_media')
                ->cascadeOnDelete();

            $table->unsignedInteger('urutan_tampilan')->default(1);

            $table->timestamps();

            $table->unique([
                'revisi_laporan_id',
                'berkas_media_id',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('foto_revisi_laporan');
    }
};