<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('titik_panas', function (Blueprint $table) {
            $table->id('titik_panas_id');
            $table->foreignId('sumber_id')->constrained('sumber_data', 'sumber_id');
            $table->string('id_rekaman_eksternal', 200)->nullable();
            $table->geometry('lokasi', 'geometry', 4326);
            $table->timestamp('terdeteksi_pada')->nullable();
            $table->decimal('nilai_keyakinan', 6, 3)->nullable();
            $table->jsonb('atribut_titik_panas')->nullable();
            $table->timestamp('pertama_diimpor_pada')->useCurrent();
            $table->timestamp('terakhir_diimpor_pada')->useCurrent();
            $table->timestamp('dibuat_pada')->useCurrent();
            $table->unique(['sumber_id', 'id_rekaman_eksternal'], 'titik_panas_index_1');
            $table->comment('Lokasi titik panas merupakan indikasi pemeriksaan, bukan bukti otomatis deforestasi.');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('titik_panas');
    }
};
