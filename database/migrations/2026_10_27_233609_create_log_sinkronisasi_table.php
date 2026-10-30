<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('log_sinkronisasi', function (Blueprint $table) {
            $table->id('log_sinkronisasi_id');
            $table->foreignId('sumber_id')->constrained('sumber_data', 'sumber_id');
            $table->timestamp('dimulai_pada')->useCurrent();
            $table->timestamp('diselesaikan_pada')->nullable();
            $table->string('status_sinkronisasi', 30);
            $table->integer('catatan_diterima')->nullable();
            $table->integer('catatan_dimasukkan')->nullable();
            $table->integer('catatan_diperbarui')->nullable();
            $table->integer('catatan_ditolak')->nullable();
            $table->text('pesan_kesalahan')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('log_sinkronisasi');
    }
};
