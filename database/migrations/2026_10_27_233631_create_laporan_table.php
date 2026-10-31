<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laporan', function (Blueprint $table) {
            $table->id();

            // Identitas laporan
            $table->string('nomor_laporan')->unique();

            // Relasi dengan pelapor, wilayah, dan kawasan
            $table->unsignedBigInteger('pelapor_id')->nullable();
            $table->unsignedBigInteger('wilayah_id');
            $table->unsignedBigInteger('kawasan_id')->nullable();

            // Informasi laporan
            $table->string('kategori');
            $table->text('deskripsi');
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->dateTime('waktu_kejadian')->nullable();

            // Status awal laporan
            $table->string('status')->default('menunggu_verifikasi');

            $table->timestamps();

            $table->index('pelapor_id');
            $table->index('wilayah_id');
            $table->index('kawasan_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laporan');
    }
};