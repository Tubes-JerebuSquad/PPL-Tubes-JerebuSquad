<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('log_audit', function (Blueprint $table) {
            $table->id('log_audit_id');
            $table->foreignId('pelaku_id')->nullable()->constrained('pengguna', 'pengguna_id')->nullOnDelete();
            $table->foreignId('sasaran_id')->nullable()->constrained('pengguna', 'pengguna_id')->nullOnDelete();
            $table->string('aksi', 40);
            $table->json('perubahan')->nullable();
            $table->timestamp('dibuat_pada')->useCurrent();

            $table->index(['sasaran_id', 'dibuat_pada']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('log_audit');
    }
};