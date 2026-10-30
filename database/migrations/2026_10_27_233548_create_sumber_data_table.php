<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sumber_data', function (Blueprint $table) {
            $table->id('sumber_id');
            $table->string('nama_sumber', 200);
            $table->string('nama_penyedia', 200);
            $table->text('url_sumber')->nullable();
            $table->string('nama_lisensi', 200);
            $table->string('format_data', 50);
            $table->text('deskripsi_cakupan')->nullable();
            $table->date('periode_mulai')->nullable();
            $table->date('periode_selesai')->nullable();
            $table->string('kode_crs', 50);
            $table->integer('interval_penyegaran_jam')->nullable();
            $table->string('status_kualitas', 30);
            $table->timestamp('terakhir_diambil_pada')->nullable();
            $table->string('status_kebaruan', 30)->default('UNKNOWN');
            $table->boolean('is_aktif')->default(false);
            $table->foreignId('dibuat_oleh')->nullable()->constrained('pengguna', 'pengguna_id');
            $table->timestamp('dibuat_pada')->useCurrent();
            $table->timestamp('diperbarui_pada')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sumber_data');
    }
};
