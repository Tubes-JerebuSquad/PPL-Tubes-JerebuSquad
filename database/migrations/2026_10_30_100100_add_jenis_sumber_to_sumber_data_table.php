<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sumber_data', function (Blueprint $table) {
            $table->string('jenis_sumber', 20)->nullable();
            $table->index('is_aktif', 'idx_sumber_data_is_aktif');
        });
    }

    public function down(): void
    {
        Schema::table('sumber_data', function (Blueprint $table) {
            $table->dropIndex('idx_sumber_data_is_aktif');
            $table->dropColumn('jenis_sumber');
        });
    }
};
