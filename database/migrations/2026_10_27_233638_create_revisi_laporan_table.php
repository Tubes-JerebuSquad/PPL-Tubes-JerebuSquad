<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('revisi_laporan', function (Blueprint $table) {
            $table->id();

            $table->foreignId('laporan_id')
                ->constrained('laporan')
                ->cascadeOnDelete();

            $table->unsignedInteger('nomor_revisi')->default(1);
            $table->string('kategori');
            $table->text('deskripsi');
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->dateTime('waktu_kejadian')->nullable();
            $table->text('catatan_revisi')->nullable();

            $table->timestamps();

            $table->unique(['laporan_id', 'nomor_revisi']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('revisi_laporan');
    }
};