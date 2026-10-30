<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tugas_inspeksi', function (Blueprint $table) {
            $table->id('tugas_inspeksi_id');
            $table->foreignId('laporan_id')->constrained('laporan', 'laporan_id');
            $table->foreignId('ditugaskan_kepada')->constrained('pengguna', 'pengguna_id');
            $table->foreignId('ditugaskan_oleh')->constrained('pengguna', 'pengguna_id');
            $table->string('prioritas', 30);
            $table->timestamp('tenggat_waktu')->nullable();
            $table->text('instruksi')->nullable();
            $table->string('jenis_tindak_lanjut', 100)->nullable();
            $table->string('status_tugas', 30)->default('ASSIGNED');
            $table->timestamp('ditugaskan_pada')->useCurrent();
            $table->timestamp('diselesaikan_pada')->nullable();
            $table->comment('Satu laporan maksimal memiliki satu tugas inspeksi berstatus aktif. Lihat filtered unique index opsional di skrip.');
        });

        // Opsional (di skema asli juga masih berupa komentar): satu laporan maksimal satu tugas aktif.
        // Sesuaikan daftar status "aktif" dengan nilai yang dipakai, lalu hilangkan komentar (PostgreSQL):
        // DB::statement("CREATE UNIQUE INDEX uq_tugas_inspeksi_aktif ON tugas_inspeksi (laporan_id) WHERE status_tugas IN ('ASSIGNED', 'IN_PROGRESS')");
    }

    public function down(): void
    {
        Schema::dropIfExists('tugas_inspeksi');
    }
};
