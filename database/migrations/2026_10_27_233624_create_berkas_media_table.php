<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('berkas_media', function (Blueprint $table) {
            $table->id();

            $table->string('nama_asli');
            $table->string('nama_file');
            $table->string('path_file');
            $table->string('mime_type');
            $table->unsignedBigInteger('ukuran_byte');
            $table->unsignedBigInteger('diunggah_oleh')->nullable();

            $table->timestamps();

            $table->index('diunggah_oleh');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('berkas_media');
    }
};