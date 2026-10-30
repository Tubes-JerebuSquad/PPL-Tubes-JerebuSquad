<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wilayah', function (Blueprint $table) {
            $table->id('wilayah_id');
            $table->foreignId('wilayah_induk_id')->nullable()->constrained('wilayah', 'wilayah_id');
            $table->string('kode_wilayah', 50)->nullable();
            $table->string('nama_wilayah', 150);
            $table->string('tingkat_wilayah', 50)->nullable();
            $table->boolean('is_aktif')->default(true);
            $table->timestamp('dibuat_pada')->nullable()->useCurrent();
            $table->timestamp('diperbarui_pada')->nullable();
            $table->unique(['kode_wilayah'], 'uq_wilayah_kode');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wilayah');
    }
};
