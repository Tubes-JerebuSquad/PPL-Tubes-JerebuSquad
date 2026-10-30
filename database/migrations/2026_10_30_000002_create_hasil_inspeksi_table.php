<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hasil_inspeksi', function (Blueprint $table) {
            $table->id('hasil_id');
            $table->foreignId('tugas_inspeksi_id')->constrained('tugas_inspeksi', 'tugas_inspeksi_id');
            $table->integer('nomor_versi');
            $table->foreignId('diinspeksi_oleh')->constrained('pengguna', 'pengguna_id');
            $table->timestamp('diinspeksi_pada');
            $table->geometry('lokasi', 'geometry', 4326)->nullable();
            $table->text('temuan');
            $table->text('interpretasi')->nullable();
            $table->string('kelas_keyakinan', 30);
            $table->text('rekomendasi')->nullable();
            $table->string('status_hasil', 30)->default('DRAFT');
            $table->timestamp('dibuat_pada')->useCurrent();
            $table->unique(['tugas_inspeksi_id', 'nomor_versi'], 'hasil_inspeksi_index_3');
            $table->comment('Klasifikasi kepastian: UNCERTAIN, STRONG_INDICATION, CONFIRMED, NOT_PROVEN. CONFIRMED tetap merupakan hasil verifikasi sesuai prosedur, bukan kesimpulan otomatis dari titik panas.');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hasil_inspeksi');
    }
};
