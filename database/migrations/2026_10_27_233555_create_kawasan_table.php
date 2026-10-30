<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kawasan', function (Blueprint $table) {
            $table->id('kawasan_id');
            $table->foreignId('wilayah_id')->constrained('wilayah', 'wilayah_id');
            $table->string('nama_kawasan', 200);
            $table->string('jenis_kawasan', 100);
            $table->string('status_kawasan', 50);
            $table->foreignId('sumber_id')->nullable()->constrained('sumber_data', 'sumber_id');
            $table->text('catatan')->nullable();
            $table->boolean('is_aktif')->default(true);
            $table->foreignId('dibuat_oleh')->nullable()->constrained('pengguna', 'pengguna_id');
            $table->timestamp('dibuat_pada')->useCurrent();
            $table->timestamp('diperbarui_pada')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kawasan');
    }
};
