<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengguna', function (Blueprint $table) {
            $table->id('pengguna_id');
            $table->foreignId('peran_id')->constrained('peran', 'peran_id');
            $table->string('nama_lengkap', 150);
            $table->string('email', 255)->unique();
            $table->string('password_hash', 255);
            $table->string('telepon', 30)->nullable();
            $table->string('status_akun', 20)->default('ACTIVE');
            $table->timestamp('dibuat_pada')->useCurrent();
            $table->timestamp('diperbarui_pada')->nullable();
            $table->timestamp('dinonaktifkan_pada')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengguna');
    }
};
