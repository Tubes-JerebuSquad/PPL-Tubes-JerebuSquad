<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('peristiwa_laporan', function (Blueprint $table) {
            $table->id('peristiwa_id');
            $table->foreignId('laporan_id')->constrained('laporan', 'laporan_id');
            $table->foreignId('aktor_id')->nullable()->constrained('pengguna', 'pengguna_id');
            $table->string('jenis_peristiwa', 50);
            $table->string('status_lama', 40)->nullable();
            $table->string('status_baru', 40)->nullable();
            $table->text('catatan_peristiwa')->nullable();
            $table->timestamp('terjadi_pada')->useCurrent();
            $table->comment('Mencatat perubahan status, verifikasi, permintaan perbaikan, penolakan, dan tindakan terkait laporan.');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('peristiwa_laporan');
    }
};
