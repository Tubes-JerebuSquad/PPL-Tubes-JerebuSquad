<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hasil_kegiatan', function (Blueprint $table) {
            $table->id('hasil_kegiatan_id');
            $table->foreignId('kegiatan_id')->constrained('kegiatan_konservasi', 'kegiatan_id');
            $table->foreignId('direkam_oleh')->constrained('pengguna', 'pengguna_id');
            $table->integer('nomor_versi');
            $table->date('tanggal_realisasi');
            $table->geometry('lokasi', 'geometry', 4326)->nullable();
            $table->decimal('area_terealisasi', 14, 2)->nullable();
            $table->decimal('kuantitas_terealisasi', 14, 2)->nullable();
            $table->string('satuan_kuantitas', 50)->nullable();
            $table->integer('jumlah_pohon')->nullable();
            $table->decimal('tingkat_kelangsungan_hidup', 5, 2)->nullable();
            $table->text('catatan_tindak_lanjut')->nullable();
            $table->string('status_pengukuran', 30);
            $table->timestamp('dibuat_pada')->useCurrent();
            $table->unique(['kegiatan_id', 'nomor_versi'], 'hasil_kegiatan_index_5');
            $table->comment('Nilai yang belum diukur harus dibedakan dari nilai nol (NULL = belum diukur).');
        });

        // CHECK constraint (Laravel belum punya API-nya; SQLite tidak mendukung ADD CONSTRAINT)
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement('ALTER TABLE hasil_kegiatan ADD CONSTRAINT chk_hasil_kegiatan_nonnegatif CHECK ((area_terealisasi IS NULL OR area_terealisasi >= 0) AND (kuantitas_terealisasi IS NULL OR kuantitas_terealisasi >= 0) AND (jumlah_pohon IS NULL OR jumlah_pohon >= 0))');
            DB::statement('ALTER TABLE hasil_kegiatan ADD CONSTRAINT chk_hasil_kegiatan_survival CHECK (tingkat_kelangsungan_hidup IS NULL OR (tingkat_kelangsungan_hidup >= 0 AND tingkat_kelangsungan_hidup <= 100))');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('hasil_kegiatan');
    }
};
