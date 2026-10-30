<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laporan', function (Blueprint $table) {
            $table->id('laporan_id');
            $table->string('kode_laporan', 50)->unique();
            $table->foreignId('pelapor_id')->constrained('pengguna', 'pengguna_id');
            $table->foreignId('wilayah_id')->constrained('wilayah', 'wilayah_id');
            $table->foreignId('kawasan_id')->nullable()->constrained('kawasan', 'kawasan_id');
            $table->string('kategori', 50);
            $table->text('deskripsi');
            $table->geometry('lokasi', 'geometry', 4326);
            $table->timestamp('insiden_pada')->nullable();
            $table->string('status_laporan', 40);
            $table->timestamp('diserahkan_pada')->nullable();
            $table->timestamp('dibuat_pada')->useCurrent();
            $table->timestamp('diperbarui_pada')->nullable();
            $table->comment('Identitas pelapor_id bersifat privat. kawasan_id boleh kosong jika laporan belum terhubung ke kawasan tertentu.');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laporan');
    }
};
