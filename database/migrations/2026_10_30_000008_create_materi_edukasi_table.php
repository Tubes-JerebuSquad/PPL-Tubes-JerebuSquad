<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('materi_edukasi', function (Blueprint $table) {
            $table->id('materi_id');
            $table->foreignId('dibuat_oleh')->constrained('pengguna', 'pengguna_id');
            $table->foreignId('diperbarui_oleh')->nullable()->constrained('pengguna', 'pengguna_id');
            $table->string('judul', 250);
            $table->text('konten');
            $table->string('kategori', 100);
            $table->text('referensi_sumber')->nullable();
            $table->string('status_publikasi', 30)->default('DRAFT');
            $table->timestamp('dipublikasikan_pada')->nullable();
            $table->timestamp('diarsipkan_pada')->nullable();
            $table->timestamp('dibuat_pada')->useCurrent();
            $table->timestamp('diperbarui_pada')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('materi_edukasi');
    }
};
