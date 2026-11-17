<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            DB::unprepared(<<<'SQL'
ALTER TABLE laporan ADD COLUMN kategori_triase varchar(50);
ALTER TABLE laporan ADD COLUMN prioritas_triase varchar(20);
ALTER TABLE laporan ADD COLUMN catatan_publik text;
ALTER TABLE laporan ADD COLUMN catatan_internal text;
ALTER TABLE laporan ADD COLUMN ditriase_oleh integer;
ALTER TABLE laporan ADD COLUMN ditriase_pada datetime;
SQL);

            return;
        }

        Schema::table('laporan', function (Blueprint $table) {
            $table->string('kategori_triase', 50)->nullable()->after('status_laporan');
            $table->string('prioritas_triase', 20)->nullable()->after('kategori_triase');
            $table->text('catatan_publik')->nullable()->after('prioritas_triase');
            $table->text('catatan_internal')->nullable()->after('catatan_publik');
            $table->unsignedBigInteger('ditriase_oleh')->nullable()->after('catatan_internal');
            $table->dateTime('ditriase_pada')->nullable()->after('ditriase_oleh');

            $table->index('ditriase_oleh');
        });
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('laporan', function (Blueprint $table) {
            $table->dropIndex(['ditriase_oleh']);
            $table->dropColumn([
                'kategori_triase',
                'prioritas_triase',
                'catatan_publik',
                'catatan_internal',
                'ditriase_oleh',
                'ditriase_pada',
            ]);
        });
    }
};
