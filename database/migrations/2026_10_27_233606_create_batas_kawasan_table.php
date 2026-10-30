<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('batas_kawasan', function (Blueprint $table) {
            $table->id('batas_id');
            $table->foreignId('kawasan_id')->constrained('kawasan', 'kawasan_id');
            $table->integer('nomor_versi');
            $table->geometry('data_geometri', 'geometry', 4326);
            $table->string('kode_crs', 50);
            $table->foreignId('sumber_id')->nullable()->constrained('sumber_data', 'sumber_id');
            $table->string('status_validasi', 30);
            $table->text('pesan_validasi')->nullable();
            $table->foreignId('dibuat_oleh')->nullable()->constrained('pengguna', 'pengguna_id');
            $table->timestamp('dibuat_pada')->useCurrent();
            $table->unique(['kawasan_id', 'nomor_versi'], 'batas_kawasan_index_0');
            $table->comment('data_geometri adalah geometri spasial. Perubahan batas disimpan sebagai versi baru.');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('batas_kawasan');
    }
};
