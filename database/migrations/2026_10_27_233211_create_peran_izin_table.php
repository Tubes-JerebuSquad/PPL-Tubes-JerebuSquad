<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('peran_izin', function (Blueprint $table) {
            $table->foreignId('peran_id')->constrained('peran', 'peran_id');
            $table->foreignId('izin_id')->constrained('izin', 'izin_id');
            $table->primary(['peran_id', 'izin_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('peran_izin');
    }
};
