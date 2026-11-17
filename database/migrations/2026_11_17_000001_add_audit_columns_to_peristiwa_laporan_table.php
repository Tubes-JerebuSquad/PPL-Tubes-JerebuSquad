<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('peristiwa_laporan', function (Blueprint $table) {
            $table->foreignId('pengguna_id')
                ->nullable()
                ->after('laporan_id')
                ->constrained('pengguna', 'pengguna_id')
                ->nullOnDelete();

            $table->string('status_lama', 50)->nullable()->after('jenis_peristiwa');
            $table->string('status_baru', 50)->nullable()->after('status_lama');
            $table->text('catatan_publik')->nullable()->after('deskripsi');

            $table->index(['laporan_id', 'waktu_peristiwa'], 'idx_peristiwa_laporan_waktu');
        });
    }

    public function down(): void
    {
        Schema::table('peristiwa_laporan', function (Blueprint $table) {
            $table->dropIndex('idx_peristiwa_laporan_waktu');
            $table->dropForeign(['pengguna_id']);
            $table->dropColumn(['pengguna_id', 'status_lama', 'status_baru', 'catatan_publik']);
        });
    }
};
