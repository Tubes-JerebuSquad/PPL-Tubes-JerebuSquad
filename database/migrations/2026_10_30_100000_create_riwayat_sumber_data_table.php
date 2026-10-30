<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('riwayat_sumber_data', function (Blueprint $table) {
            $table->id('riwayat_id');
            $table->foreignId('sumber_id')->constrained('sumber_data', 'sumber_id')->restrictOnDelete();
            $table->foreignId('pengguna_id')->nullable()->constrained('pengguna', 'pengguna_id')->nullOnDelete();
            $table->string('aksi', 30);
            $table->jsonb('perubahan')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamp('dibuat_pada')->useCurrent();

            $table->index(['sumber_id', 'dibuat_pada'], 'idx_riwayat_sumber_data_sumber_waktu');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('riwayat_sumber_data');
    }
};
